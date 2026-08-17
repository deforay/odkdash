#!/bin/bash

# Upgrade odkdash deployments in place.
#
# Run it straight from the repo:
#
#   curl -fsSL "https://raw.githubusercontent.com/deforay/odkdash/master/bin/upgrade.sh?v=$(date +%s)" | sudo bash -s -- -A
#
# Instances come in two shapes: some are git checkouts (MIGRATION.md deploys
# Trinidad that way), others were unpacked from a tarball or copied off another
# server and have no .git at all. The script detects which it is and either
# fast-forwards the checkout or rsyncs a fresh source tree over it. Either way
# everything under config/autoload/ is preserved — local.php is tracked
# upstream but holds this deployment's database credentials.
#
# It deliberately does no system-level work. PHP, MySQL and Apache belong to
# the setup script, and on the servers where odkdash runs alongside ept,
# ept-update already tunes them.
#
# Databases are dumped with the app's own db-tools where vendor/ has it, which
# means a .sql.zst archive restorable with:
#
#   (cd /var/www/odkdash && vendor/bin/db-tools restore /var/odkdash-backup/db/NAME.sql.zst)
#
# Trees too old to carry db-tools fall back to mysqldump and a .sql.gz.
#
# Usage:
#   sudo bin/upgrade.sh [-A] [-p PATH] [-b] [-f] [-y]
#
# Options:
#   -A       upgrade every odkdash instance found in /var/www
#   -p PATH  upgrade one instance (default /var/www/odkdash)
#   -b       skip the backups (the config/autoload tarball is always taken)
#   -f       on a checkout, set aside local changes to tracked files outside
#            config/autoload instead of stopping
#   -y       non-interactive: every prompt takes its default, which is the safe
#            answer, so a failed backup stops that instance rather than plough on
#
# Env:
#   ODKDASH_SRC_DIR   source mirror location (default /usr/local/lib/odkdash/src)

set -o pipefail

if [ "$EUID" -ne 0 ]; then
    echo "Need admin privileges for this script. Run it with sudo."
    exit 1
fi

log_file="/tmp/odkdash-upgrade-$(date +'%Y%m%d-%H%M%S').log"
stamp="$(date +'%Y%m%d-%H%M%S')"

REPO_GIT_URL="https://github.com/deforay/odkdash.git"
REPO_TARBALL_URL="https://codeload.github.com/deforay/odkdash/tar.gz/refs/heads/master"
SRC_DIR="${ODKDASH_SRC_DIR:-/usr/local/lib/odkdash/src}"
SEARCH_DIR="/var/www"
BACKUP_ROOT="/var/odkdash-backup"

# ---------------------------------------------------------------- helpers ---

print() {
    case "$1" in
        error)   printf "\033[1;91m❌ Error:\033[0m %s\n" "$2" ;;
        success) printf "\033[1;92m✅ Success:\033[0m %s\n" "$2" ;;
        warning) printf "\033[1;93m⚠️  Warning:\033[0m %s\n" "$2" ;;
        info)    printf "\033[1;96mℹ️  Info:\033[0m %s\n" "$2" ;;
        header)  printf "\n\033[1;96m===== %s =====\033[0m\n\n" "$2" ;;
        *)       printf "%s\n" "$2" ;;
    esac
}

log_action() {
    echo "$(date +'%Y-%m-%d %H:%M:%S') - $1" >>"$log_file"
}

say() {
    print "$1" "$2"
    log_action "$2"
}

# Fatal for the whole run.
die() {
    say error "$1"
    exit 1
}

# Fatal for the current instance only, so one bad instance does not stop the rest.
fail() {
    say error "$1"
    return 1
}

# Prompts read from /dev/tty, not stdin, so they still work when the script is
# piped in from curl. Without a terminal at all — cron, CI — the default wins.
ask_yes_no() {
    local question="$1" default="${2:-no}" answer

    # Probe by opening the terminal rather than testing for it. /dev/tty exists
    # under cron and in containers but cannot be opened, and the failed
    # redirect prints noise of its own.
    if [ "$assume_defaults" = true ] || ! { exec 3</dev/tty; } 2>/dev/null; then
        print info "${question}? [auto: ${default}]"
        [ "$default" = "yes" ]
        return
    fi

    printf "%s? [default: %s, auto in 20s] " "$question" "$default"
    if ! read -r -t 20 answer <&3; then
        printf '\n'
        print info "No input. Using the default: ${default}"
        answer="$default"
    fi
    exec 3<&-

    answer="$(printf '%s' "${answer:-$default}" | tr '[:upper:]' '[:lower:]')"
    [ "$answer" = "y" ] || [ "$answer" = "yes" ]
}

as_web() {
    sudo -u "$web_user" "$@"
}

# safe.directory keeps git from refusing a checkout it does not own. The
# low-speed abort stops a dead link from hanging the whole upgrade.
run_git() {
    git -c safe.directory='*' -c http.lowSpeedLimit=1000 -c http.lowSpeedTime=60 "$@"
}

# An odkdash tree, as opposed to any other Laminas app sharing the server. The
# composer.json name is the discriminating marker. bin/migrate is deliberately
# NOT required: it only landed in May 2026, and installs old enough to lack it
# are precisely the ones that need upgrading. The deploy puts it there.
is_odkdash_path() {
    [ -f "$1/composer.json" ] &&
    grep -q '"deforay/odkdash"' "$1/composer.json" 2>/dev/null &&
    [ -f "$1/public/index.php" ]
}

# Which part of the check failed, so a rejected path says why rather than
# leaving the operator to guess at three conditions.
why_not_odkdash() {
    if [ ! -f "$1/composer.json" ]; then
        echo "no composer.json there"
    elif ! grep -q '"deforay/odkdash"' "$1/composer.json" 2>/dev/null; then
        echo "composer.json is not deforay/odkdash"
    elif [ ! -f "$1/public/index.php" ]; then
        echo "no public/index.php"
    else
        echo "unknown reason"
    fi
}

checksum() {
    [ -f "$1" ] && md5sum "$1" | awk '{print $1}' || echo "none"
}

# Whichever downloader the box has; the tarball fallback is for machines
# without git, which are also the ones most likely to be missing one of these.
fetch_url() {
    local url="$1" dest="$2"
    if command -v curl &>/dev/null; then
        curl -fsSL --retry 3 -o "$dest" "$url" 2>>"$log_file"
    elif command -v wget &>/dev/null; then
        wget -q -O "$dest" "$url" 2>>"$log_file"
    else
        return 1
    fi
}

# The merged config decides which database an instance talks to, and with which
# credentials. Mirror the app's own glob and merge order rather than guessing
# from a single file. Emits name, user, password, host and port, one per line,
# so a value containing spaces survives the round trip.
resolve_db_config() {
    (cd "$app_path" && as_web php -r '
        $files = glob("config/autoload/{{,*.}global,{,*.}local}.php", GLOB_BRACE) ?: [];
        $db = [];
        foreach ($files as $file) {
            $conf = include $file;
            if (is_array($conf) && isset($conf["db"]) && is_array($conf["db"])) {
                $db = array_merge($db, $conf["db"]);
            }
        }
        $name = $host = $port = "";
        if (!empty($db["dsn"])) {
            foreach (["dbname" => "name", "host" => "host", "port" => "port"] as $key => $var) {
                if (preg_match("/" . $key . "=([^;]+)/", $db["dsn"], $m)) {
                    $$var = trim($m[1]);
                }
            }
        }
        if ($name === "" && !empty($db["data-base-name"])) $name = trim($db["data-base-name"]);
        if ($host === "" && !empty($db["data-base-host"])) $host = trim($db["data-base-host"]);
        if ($port === "" && !empty($db["port"])) $port = trim($db["port"]);
        foreach ([$name, $db["username"] ?? "", $db["password"] ?? "", $host, $port] as $value) {
            echo str_replace(["\r", "\n"], "", (string) $value), "\n";
        }
    ' 2>/dev/null)
}

# mysqldump needs a password on most boxes; putting it on the command line would
# expose it in ps, so it goes in a 0600 option file instead. Option-file values
# are quoted because a password may contain # or ; which otherwise start a
# comment.
write_defaults_file() {
    local user="$1" pass="$2" host="$3" port="$4" escaped

    defaults_file="$(mktemp)"
    chmod 600 "$defaults_file"
    {
        printf '[client]\n'
        escaped="${user//\\/\\\\}"; printf 'user="%s"\n' "${escaped//\"/\\\"}"
        if [ -n "$pass" ]; then
            escaped="${pass//\\/\\\\}"; printf 'password="%s"\n' "${escaped//\"/\\\"}"
        fi
        [ -n "$host" ] && printf 'host="%s"\n' "$host"
        [ -n "$port" ] && printf 'port=%s\n' "$port"
    } >"$defaults_file"
}

# db-tools is the app's own backup tool: db-tools.php reads the credentials out
# of config/autoload, and the archive comes out zstd-compressed where zstd is
# installed (pigz or gzip otherwise). It writes its own filename into the output
# directory, so the path is read back from what it prints.
#
# Encryption is on by default and retention would prune older archives; an
# upgrade backup has to be restorable without a key and must never delete a
# previous one, hence --no-encrypt and --retention=0.
db_tools_dump() {
    local output status path

    [ -f "$app_path/vendor/bin/db-tools" ] && [ -f "$app_path/db-tools.php" ] || return 1

    # As root, unlike the rest of the run: $BACKUP_ROOT is root-owned, and the
    # ownership sweep later in the upgrade tidies anything left in the app tree.
    output="$(cd "$app_path" && php vendor/bin/db-tools backup \
        --output-dir="$BACKUP_ROOT/db" --no-encrypt --retention=0 \
        --no-interaction 2>&1)"
    status=$?
    printf '%s\n' "$output" >>"$log_file"
    [ $status -eq 0 ] || return 1

    path="$(printf '%s\n' "$output" | grep -oE '/[^[:space:]]+\.sql\.(zst|gz|zip)' | tail -1)"
    [ -n "$path" ] && [ -f "$path" ] || return 1

    db_backup="$path"
}

# mysqldump, for trees whose vendor/ predates db-tools — the backup runs before
# composer install, so the new source has not landed yet. Root over the unix
# socket is what MIGRATION.md uses and needs no credentials, but it only
# authenticates where root uses auth_socket. The app user always reaches its own
# database, though it may lack PROCESS (tablespaces) and the rights to read
# routines and triggers, hence the narrowing retries.
mysqldump_fallback() {
    local rc=1
    local -a attempt

    db_backup="${BACKUP_ROOT}/db/${db_name}-${stamp}.sql.gz"

    if mysqldump --opt --routines --triggers --databases "$db_name" 2>>"$log_file" | gzip >"$db_backup"; then
        return 0
    fi

    [ -n "$db_user" ] || { rm -f "$db_backup"; return 1; }
    say info "Root has a password here; retrying the dump as ${db_user} from config/autoload."
    write_defaults_file "$db_user" "$db_pass" "$db_host" "$db_port"

    attempt=(--routines --triggers --no-tablespaces)
    while :; do
        if mysqldump --defaults-file="$defaults_file" --opt "${attempt[@]}" \
            --databases "$db_name" 2>>"$log_file" | gzip >"$db_backup"; then
            [ ${#attempt[@]} -eq 1 ] &&
                say warning "Dumped without routines or triggers: ${db_user} may not read them."
            rc=0
            break
        fi
        # Drop --routines --triggers and try once more; those are the privileges
        # an application user is most often denied.
        [ ${#attempt[@]} -gt 1 ] || break
        attempt=(--no-tablespaces)
    done

    # The option file holds the password in clear, so it does not outlive the dump.
    rm -f "$defaults_file"
    defaults_file=""
    [ $rc -eq 0 ] || rm -f "$db_backup"
    return $rc
}

# Sets db_backup to whatever was written.
dump_database() {
    db_tools_dump && return 0
    mysqldump_fallback
}

# What version an instance is on. A checkout knows from git; an rsynced tree
# only knows because the deploy stamped VERSION.txt.
installed_ref() {
    if [ -e "$app_path/.git" ]; then
        run_git -C "$app_path" rev-parse --short HEAD 2>/dev/null
    elif [ -f "$app_path/VERSION.txt" ]; then
        head -1 "$app_path/VERSION.txt"
    else
        echo "unknown"
    fi
}

detect_installations() {
    local dir
    for dir in "$SEARCH_DIR"/*/; do
        dir="${dir%/}"
        [ -d "$dir" ] || continue
        is_odkdash_path "$dir" && printf '%s\n' "$dir"
    done
}

trap 'rc=$?; [ $rc -ne 0 ] && print info "Full log: '"$log_file"'"; exit $rc' EXIT

# ----------------------------------------------------------------- source ---

# A persistent shallow mirror is updated with delta fetches, so after the first
# clone each run transfers only what changed instead of a fresh tarball. The
# tarball is the last resort for boxes without git. Acquired once per run and
# shared by every instance that needs it.
src_dir=""
temp_dir=""

acquire_source() {
    [ -z "$src_dir" ] || return 0

    print header "Downloading odkdash"
    temp_dir="$(mktemp -d)"

    if command -v git &>/dev/null && [ -d "$SRC_DIR/.git" ]; then
        print info "Updating the source mirror (delta fetch)..."
        if run_git -C "$SRC_DIR" fetch --depth 1 origin master >>"$log_file" 2>&1 &&
            run_git -C "$SRC_DIR" reset --hard FETCH_HEAD >>"$log_file" 2>&1 &&
            run_git -C "$SRC_DIR" clean -fd >>"$log_file" 2>&1; then
            # A shallow fetch orphans the old tip; sweep it so the mirror does
            # not bloat across upgrades.
            run_git -C "$SRC_DIR" gc --prune=now --quiet >>"$log_file" 2>&1 || true
            src_dir="$SRC_DIR"
            say success "Source mirror updated."
        else
            say warning "Delta fetch failed; re-cloning the mirror."
            rm -rf "$SRC_DIR"
        fi
    fi

    if [ -z "$src_dir" ] && command -v git &>/dev/null; then
        print info "Cloning master into the source mirror (shallow)..."
        mkdir -p "$(dirname "$SRC_DIR")"
        rm -rf "$SRC_DIR"
        if run_git clone --depth 1 --single-branch --branch master \
            "$REPO_GIT_URL" "$SRC_DIR" >>"$log_file" 2>&1; then
            src_dir="$SRC_DIR"
            say success "Source cloned. Later runs fetch deltas only."
        else
            say warning "git clone failed. See ${log_file}."
        fi
    fi

    if [ -z "$src_dir" ]; then
        print info "Falling back to the tarball..."
        if fetch_url "$REPO_TARBALL_URL" "$temp_dir/master.tar.gz" &&
            gzip -t "$temp_dir/master.tar.gz" 2>>"$log_file" &&
            tar -xzf "$temp_dir/master.tar.gz" -C "$temp_dir" 2>>"$log_file" &&
            [ -d "$temp_dir/odkdash-master" ]; then
            src_dir="$temp_dir/odkdash-master"
            say success "Source obtained via tarball."
        fi
    fi

    [ -n "$src_dir" ] || return 1

    # Stamp the ref into the tree. .git never reaches an instance, so
    # VERSION.txt is the only way an rsynced install knows what it runs.
    if [ -d "$src_dir/.git" ]; then
        run_git -C "$src_dir" rev-parse --short HEAD >"$src_dir/VERSION.txt" 2>/dev/null || true
    else
        echo "tarball-${stamp}" >"$src_dir/VERSION.txt"
    fi
}

# -------------------------------------------------------------- instance ----

upgrade_instance() {
    app_path="$1"
    local position="$2" total="$3"
    local deploy_mode ref_before ref_after lock_before branch remote_url dirty
    local config_stash config_backup db_backup folder_backup
    # Dynamically scoped, so dump_database and write_defaults_file see them.
    local db_name db_user db_pass db_host db_port defaults_file=""

    if [ -e "$app_path/.git" ]; then
        deploy_mode="git"
    else
        deploy_mode="rsync"
    fi

    print header "Upgrading ${position}/${total}: ${app_path}"
    say info "Deploy mode: ${deploy_mode}. Currently at: $(installed_ref)"

    # --- backups ------------------------------------------------------------
    mkdir -p "$BACKUP_ROOT/db" "$BACKUP_ROOT/config" "$BACKUP_ROOT/www"

    # The deploy rewrites tracked files under config/autoload, so this tarball
    # is taken on every run regardless of -b. It is a few kilobytes.
    config_backup="${BACKUP_ROOT}/config/$(basename "$app_path")-autoload-${stamp}.tar.gz"
    tar -czf "$config_backup" -C "$app_path" config/autoload ||
        { fail "Could not archive config/autoload for ${app_path}."; return 1; }
    say success "Configuration backed up to ${config_backup}"

    { read -r db_name; read -r db_user; read -r db_pass; read -r db_host; read -r db_port; } < <(resolve_db_config)
    [ -n "$db_name" ] || say warning "Could not resolve the database name from config/autoload."

    if [ "$skip_backups" = false ] && [ -n "$db_name" ]; then
        # Two instances sharing one database, which MIGRATION.md documents. The
        # archive name is db-tools' to choose, so track what has been dumped
        # rather than testing for a path this run would have picked.
        if printf '%s\n' "${dumped_dbs[@]}" | grep -Fxq "$db_name"; then
            say info "Database ${db_name} already dumped this run."
        else
            print info "Dumping database ${db_name}..."
            if dump_database; then
                dumped_dbs+=("$db_name")
                say success "Database backed up to ${db_backup} ($(du -h "$db_backup" | cut -f1))"
            else
                say warning "Database dump failed. Check ${log_file}."
                ask_yes_no "Continue upgrading ${app_path} without a database backup" "no" ||
                    { fail "Skipped ${app_path}: no database backup."; return 1; }
            fi
        fi
    elif [ "$skip_backups" = true ]; then
        say info "Skipping database backup (-b)."
    fi

    # An rsync deploy overwrites tracked files with no git history to fall back
    # on, so offer a copy of the tree first. Checkouts recover with git alone.
    if [ "$deploy_mode" = "rsync" ] && [ "$skip_backups" = false ]; then
        if ask_yes_no "Back up ${app_path} before overwriting it" "yes"; then
            folder_backup="${BACKUP_ROOT}/www/$(basename "$app_path")-${stamp}"
            print info "Copying ${app_path} to ${folder_backup}..."
            rsync -a --exclude 'public/temporary/' --exclude 'vendor/' \
                "$app_path/" "$folder_backup/" 2>>"$log_file" ||
                { fail "Folder backup failed for ${app_path}. See ${log_file}."; return 1; }
            say success "Folder backed up to ${folder_backup}"
        fi
    fi

    # --- source -------------------------------------------------------------
    lock_before="$(checksum "$app_path/composer.lock")"
    ref_before="$(installed_ref)"

    # config/autoload holds this deployment's credentials but is tracked
    # upstream, so park it and put it back once the new source is in place.
    config_stash="$(mktemp -d)"
    cp -a "$app_path/config/autoload/." "$config_stash/" ||
        { fail "Could not stage config/autoload for ${app_path}."; return 1; }

    if [ "$deploy_mode" = "git" ]; then
        if ! command -v git &>/dev/null; then
            fail "${app_path} is a git checkout but git is not installed."
            return 1
        fi

        branch="$(run_git -C "$app_path" rev-parse --abbrev-ref HEAD 2>/dev/null)"
        if [ -z "$branch" ] || [ "$branch" = "HEAD" ]; then
            fail "${app_path} is not on a branch (detached HEAD)."
            return 1
        fi

        remote_url="$(run_git -C "$app_path" remote get-url origin 2>/dev/null)"
        if [ -z "$remote_url" ]; then
            fail "${app_path} has no origin remote. Add one: git -C ${app_path} remote add origin ${REPO_GIT_URL}"
            return 1
        fi

        # Local modifications outside config/autoload are code changes that
        # belong upstream. Clobbering them silently is how deployments quietly
        # diverge.
        dirty="$(run_git -C "$app_path" status --porcelain --untracked-files=no -- . ':(exclude)config/autoload')"
        if [ -n "$dirty" ]; then
            print warning "Tracked files modified outside config/autoload:"
            printf '%s\n' "$dirty"
            if [ "$force_dirty" = true ]; then
                # Stash rather than checkout, so -f stays recoverable.
                if run_git -C "$app_path" stash push --quiet -m "odkdash-upgrade ${stamp}" \
                    -- . ':(exclude)config/autoload' 2>>"$log_file"; then
                    say warning "Local modifications stashed (-f). Recover with: git -C ${app_path} stash list"
                else
                    run_git -C "$app_path" checkout -- . 2>>"$log_file" ||
                        { fail "Could not discard local modifications in ${app_path}."; return 1; }
                    say warning "Local modifications discarded (-f)."
                fi
            else
                fail "${app_path} has local code changes. Push them upstream, or re-run with -f to set them aside."
                return 1
            fi
        fi

        run_git -C "$app_path" checkout -- config/autoload 2>>"$log_file" || true

        print info "Fetching origin/${branch}..."
        # An SSH remote is the usual failure here: the key belongs to the admin
        # who cloned it, and this script runs as root.
        if ! run_git -C "$app_path" fetch --prune origin "$branch" 2>>"$log_file"; then
            fail "git fetch from ${remote_url} failed (see ${log_file}). If it is an SSH remote, switch it: git -C ${app_path} remote set-url origin ${REPO_GIT_URL}"
            return 1
        fi

        if ! run_git -C "$app_path" merge --ff-only "origin/${branch}" 2>>"$log_file"; then
            fail "${app_path} has diverged from origin/${branch}. Resolve it by hand."
            return 1
        fi
    else
        acquire_source ||
            { fail "Could not obtain the source (mirror, clone and tarball all failed)."; return 1; }

        # Symlinked directories are how instances point uploads at another
        # volume; -K fills them rather than replacing them with real dirs. No
        # --delete: files the instance has and the repo does not are left alone.
        print info "Deploying source into ${app_path}..."
        rsync -a -K --info=progress2 \
            --exclude='.git' \
            --exclude='config/autoload/' \
            --exclude='vendor/' \
            --exclude='var/' \
            --exclude='uploads/' \
            --exclude='public/uploads/' \
            --exclude='public/temporary/' \
            --exclude='data/cache/' \
            "$src_dir/" "$app_path/" 2>>"$log_file" ||
            { fail "rsync deploy failed for ${app_path}. See ${log_file}."; return 1; }
    fi

    # config/autoload is excluded from the rsync and checked out clean before
    # the merge, but both modes restore the stash so they end in the same state.
    cp -a "$config_stash/." "$app_path/config/autoload/" ||
        { fail "Could not restore config/autoload for ${app_path}."; return 1; }
    rm -rf "$config_stash"
    say success "This deployment's config/autoload restored over the new tree."

    ref_after="$(installed_ref)"
    if [ "$ref_before" = "$ref_after" ]; then
        say info "Already up to date at ${ref_after}."
    else
        say success "Updated ${ref_before} → ${ref_after}"
        [ "$deploy_mode" = "git" ] &&
            run_git -C "$app_path" log --oneline "${ref_before}..${ref_after}" | head -20
    fi

    # Ownership before composer, not after: the deploy can land root-owned
    # files, and composer runs as the web user.
    chown -R "$web_user":"$web_user" "$app_path"

    # --- dependencies -------------------------------------------------------
    cd "$app_path" || { fail "Could not enter ${app_path}."; return 1; }

    if [ ! -d "$app_path/vendor" ] ||
       [ "$ref_before" != "$ref_after" ] ||
       [ "$lock_before" != "$(checksum "$app_path/composer.lock")" ]; then
        print info "Installing dependencies..."
        as_web composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader 2>&1 |
            tee -a "$log_file" || { fail "composer install failed for ${app_path}."; return 1; }
        say success "Dependencies installed."
    else
        as_web composer dump-autoload -o --no-interaction 2>&1 | tee -a "$log_file"
        say info "Source unchanged; refreshed the autoloader only."
    fi

    # --- migrations ---------------------------------------------------------
    print info "Running database migrations..."
    as_web php bin/migrate 2>&1 | tee -a "$log_file" ||
        { fail "Migrations failed for ${app_path}. See ${log_file}."; return 1; }

    # --status is the confirmation that the schema reached the code's version.
    # A migration run can report progress and still leave work pending.
    local status_output
    status_output="$(as_web php bin/migrate --status 2>&1)"
    printf '%s\n' "$status_output" | tee -a "$log_file"

    if printf '%s' "$status_output" | grep -qF "database is up to date"; then
        say success "Schema is at the version this code expects."
    else
        say warning "Migrations still pending. Re-run: (cd ${app_path} && sudo -u ${web_user} php bin/migrate)"
    fi

    # --- permissions --------------------------------------------------------
    # Directories the app writes to at runtime. Git tracks none of their
    # contents, and the rsync deploy excludes them, so they can be missing.
    local dir
    for dir in var/log var/api-logs var/backups data/cache public/temporary public/uploads uploads backup; do
        mkdir -p "$app_path/$dir"
    done

    chown -R "$web_user":"$web_user" "$app_path"
    chmod -R u+rwX,g+rwX "$app_path/var" "$app_path/data/cache" "$app_path/public/temporary" \
        "$app_path/public/uploads" "$app_path/uploads" "$app_path/backup"
    chmod +x "$app_path/cron.sh" "$app_path/bin/migrate" "$app_path/bin/upgrade.sh" 2>/dev/null

    say success "Ownership set to ${web_user} and runtime directories made writable."

    # --- cron ---------------------------------------------------------------
    # crunz reads crunz.yml from the working directory; cron.sh cd's there
    # itself, so the crontab line needs no cd of its own.
    local cron_line current_crontab
    cron_line="* * * * * ${app_path}/cron.sh >> ${app_path}/var/log/crunz.log 2>&1"
    current_crontab="$(crontab -u "$web_user" -l 2>/dev/null || true)"

    if printf '%s\n' "$current_crontab" | grep -Fq "${app_path}/cron.sh"; then
        say info "crunz cron entry already present for ${app_path}."
    else
        {
            if [ -z "$current_crontab" ]; then
                printf 'MAILTO=""\n'
                printf 'PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin\n\n'
            else
                printf '%s\n' "$current_crontab"
            fi
            printf '%s\n' "$cron_line"
        } | crontab -u "$web_user" -
        say success "crunz cron entry added to the ${web_user} crontab."
    fi

    say success "${app_path} upgraded."
    return 0
}

# ------------------------------------------------------------------ flags ---

app_path=""
target_path=""
upgrade_all=false
skip_backups=false
force_dirty=false
assume_defaults=false

while getopts ":Ap:bfy" opt; do
    case $opt in
        A) upgrade_all=true ;;
        p) target_path="$OPTARG" ;;
        b) skip_backups=true ;;
        f) force_dirty=true ;;
        y) assume_defaults=true ;;
        *) : ;;
    esac
done

for cmd in php composer rsync; do
    command -v "$cmd" &>/dev/null || die "${cmd} is not installed."
done

declare -a app_paths=()

if [ "$upgrade_all" = true ]; then
    [ -n "$target_path" ] && print warning "-A given, ignoring -p ${target_path}"
    print info "Scanning ${SEARCH_DIR} for odkdash installations..."
    mapfile -t app_paths < <(detect_installations)
    [ ${#app_paths[@]} -gt 0 ] || die "No odkdash installations found in ${SEARCH_DIR}."
else
    target_path="${target_path:-/var/www/odkdash}"
    target_path="$(cd "$target_path" 2>/dev/null && pwd)" || die "Path not found: ${target_path}"
    is_odkdash_path "$target_path" ||
        die "${target_path} does not look like an odkdash installation ($(why_not_odkdash "$target_path"))."
    app_paths=("$target_path")
fi

web_user="www-data"
id "$web_user" &>/dev/null || web_user="$(stat -c '%U' "${app_paths[0]}/public/index.php")"

# Composer needs a home it can write to when running as the web user.
export COMPOSER_HOME="${COMPOSER_HOME:-/var/www/.composer}"
mkdir -p "$COMPOSER_HOME"
chown -R "$web_user":"$web_user" "$COMPOSER_HOME"

print header "odkdash upgrade"
say info "Web user: ${web_user}"
say info "Log: ${log_file}"
print info "Instances to upgrade (${#app_paths[@]}):"
for p in "${app_paths[@]}"; do
    print info "  - ${p}"
done

if [ ${#app_paths[@]} -gt 1 ]; then
    ask_yes_no "Upgrade all ${#app_paths[@]} instances" "yes" || die "Aborted."
fi

# ------------------------------------------------------------------- run ----

declare -a upgraded=()
declare -a failed=()
declare -a dumped_dbs=()

for i in "${!app_paths[@]}"; do
    if upgrade_instance "${app_paths[$i]}" "$((i + 1))" "${#app_paths[@]}"; then
        upgraded+=("${app_paths[$i]}")
    else
        failed+=("${app_paths[$i]}")
    fi
done

[ -n "$temp_dir" ] && rm -rf "$temp_dir"

# --------------------------------------------------------------- web tier ---

print header "Reloading the web server"

if command -v apache2ctl &>/dev/null; then
    if apache2ctl -t 2>>"$log_file"; then
        apache2ctl -k graceful 2>>"$log_file" || systemctl reload apache2 2>>"$log_file" ||
            say warning "Could not reload Apache; do it by hand."
        say success "Apache reloaded."
    else
        say warning "apache2 config test failed; NOT reloading. Fix it and reload manually."
    fi
fi

# php-fpm keeps its own opcache, so a graceful Apache reload is not enough
# where the app runs behind fpm rather than mod_php.
for unit in $(systemctl list-units --type=service --state=running --no-legend 'php*-fpm.service' 2>/dev/null | awk '{print $1}'); do
    systemctl reload "$unit" 2>>"$log_file" && say success "Reloaded ${unit}."
done

# ---------------------------------------------------------------- summary ---

print header "Upgrade summary"

for p in "${upgraded[@]}"; do
    app_path="$p"
    print success "  ✓ ${p} at $(installed_ref)"
done
for p in "${failed[@]}"; do
    print error "  ✗ ${p}"
done

print info "Backups: ${BACKUP_ROOT}"
print info "Log: ${log_file}"
log_action "Upgrade complete. Updated: ${#upgraded[@]}, Failed: ${#failed[@]}"

[ ${#failed[@]} -eq 0 ]

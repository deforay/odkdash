#!/bin/bash

# Upgrade an odkdash deployment in place.
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
# Usage:
#   sudo bin/upgrade.sh [-p PATH] [-b] [-f] [-y]
#
# Options:
#   -p PATH  installation path (default /var/www/odkdash; the directory this
#            script lives in is used when it looks like an odkdash checkout).
#            Run once per path when a server hosts several instances.
#   -b       skip the backups (the config/autoload tarball is always taken)
#   -f       on a checkout, discard local modifications to tracked files outside
#            config/autoload instead of aborting
#   -y       non-interactive: every prompt takes its default, which is the safe
#            answer, so a failed backup aborts rather than plough on
#
# Env:
#   ODKDASH_SRC_DIR   source mirror location (default /usr/local/lib/odkdash/src)

set -o pipefail

if [ "$EUID" -ne 0 ]; then
    echo "Need admin privileges for this script. Run it with sudo."
    exit 1
fi

script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
log_file="/tmp/odkdash-upgrade-$(date +'%Y%m%d-%H%M%S').log"
stamp="$(date +'%Y%m%d-%H%M%S')"

REPO_GIT_URL="https://github.com/deforay/odkdash.git"
REPO_TARBALL_URL="https://codeload.github.com/deforay/odkdash/tar.gz/refs/heads/master"
SRC_DIR="${ODKDASH_SRC_DIR:-/usr/local/lib/odkdash/src}"

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

die() {
    say error "$1"
    exit 1
}

# Prompts default to the safe answer, and -y takes that default without asking.
ask_yes_no() {
    local question="$1" default="${2:-no}" answer

    if [ "$assume_defaults" = true ] || [ ! -t 0 ]; then
        print info "${question}? [auto: ${default}]"
        [ "$default" = "yes" ]
        return
    fi

    read -r -p "${question}? [y/N] " answer </dev/tty
    answer="${answer:-$default}"
    [[ "$answer" =~ ^([yY]|yes|YES)$ ]]
}

as_web() {
    sudo -u "$web_user" "$@"
}

# safe.directory keeps git from refusing a checkout it does not own. The
# low-speed abort stops a dead link from hanging the whole upgrade.
run_git() {
    git -c safe.directory='*' -c http.lowSpeedLimit=1000 -c http.lowSpeedTime=60 "$@"
}

# An odkdash tree, as opposed to any other Laminas app sharing the server.
is_odkdash_path() {
    [ -f "$1/composer.json" ] &&
    grep -q '"deforay/odkdash"' "$1/composer.json" 2>/dev/null &&
    [ -f "$1/bin/migrate" ] &&
    [ -f "$1/public/index.php" ]
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

# The merged config decides which database this instance talks to. Mirror the
# app's own glob and merge order rather than guessing from a single file.
resolve_db_name() {
    as_web php -r '
        $files = glob("config/autoload/{{,*.}global,{,*.}local}.php", GLOB_BRACE) ?: [];
        $db = [];
        foreach ($files as $file) {
            $conf = include $file;
            if (is_array($conf) && isset($conf["db"]) && is_array($conf["db"])) {
                $db = array_merge($db, $conf["db"]);
            }
        }
        if (!empty($db["dsn"]) && preg_match("/dbname=([^;]+)/", $db["dsn"], $m)) {
            echo trim($m[1]);
        } elseif (!empty($db["data-base-name"])) {
            echo trim($db["data-base-name"]);
        }
    ' 2>/dev/null
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

trap 'rc=$?; [ $rc -ne 0 ] && print info "Full log: '"$log_file"'"; exit $rc' EXIT

# ------------------------------------------------------------------ flags ---

app_path=""
skip_backups=false
force_dirty=false
assume_defaults=false

while getopts ":p:bfy" opt; do
    case $opt in
        p) app_path="$OPTARG" ;;
        b) skip_backups=true ;;
        f) force_dirty=true ;;
        y) assume_defaults=true ;;
        *) : ;;
    esac
done

if [ -z "$app_path" ]; then
    if is_odkdash_path "$script_dir"; then
        app_path="$script_dir"
    else
        app_path="/var/www/odkdash"
    fi
fi
app_path="$(cd "$app_path" 2>/dev/null && pwd)" || die "Path not found: ${app_path}"

is_odkdash_path "$app_path" || die "${app_path} does not look like an odkdash installation."

web_user="www-data"
id "$web_user" &>/dev/null || web_user="$(stat -c '%U' "$app_path/public/index.php")"

for cmd in php composer rsync; do
    command -v "$cmd" &>/dev/null || die "${cmd} is not installed."
done

# Composer needs a home it can write to when running as the web user.
export COMPOSER_HOME="${COMPOSER_HOME:-/var/www/.composer}"
mkdir -p "$COMPOSER_HOME"
chown -R "$web_user":"$web_user" "$COMPOSER_HOME"

if [ -e "$app_path/.git" ]; then
    deploy_mode="git"
else
    deploy_mode="rsync"
fi

print header "odkdash upgrade"
say info "Installation: ${app_path} (${deploy_mode} deploy)"
say info "Web user: ${web_user}"
say info "Currently at: $(installed_ref)"
say info "Log: ${log_file}"

# ----------------------------------------------------------------- backup ---

print header "Backups"

backup_root="/var/odkdash-backup"
mkdir -p "$backup_root/db" "$backup_root/config" "$backup_root/www"

# The deploy rewrites tracked files under config/autoload, so this tarball is
# taken on every run regardless of -b. It is a few kilobytes.
config_backup="${backup_root}/config/$(basename "$app_path")-autoload-${stamp}.tar.gz"
tar -czf "$config_backup" -C "$app_path" config/autoload ||
    die "Could not archive config/autoload."
say success "Configuration backed up to ${config_backup}"

db_name="$(cd "$app_path" && resolve_db_name)"
[ -n "$db_name" ] || say warning "Could not resolve the database name from config/autoload."

if [ "$skip_backups" = false ] && [ -n "$db_name" ]; then
    db_backup="${backup_root}/db/${db_name}-${stamp}.sql.gz"
    print info "Dumping database ${db_name}..."
    # Root over the unix socket, the same way MIGRATION.md dumps it — no
    # credentials to dig out of the config.
    if mysqldump --opt --routines --triggers --databases "$db_name" 2>>"$log_file" | gzip >"$db_backup"; then
        say success "Database backed up to ${db_backup} ($(du -h "$db_backup" | cut -f1))"
    else
        rm -f "$db_backup"
        say warning "Database dump failed. Check ${log_file}."
        ask_yes_no "Continue the upgrade without a database backup" "no" ||
            die "Aborted: no database backup."
    fi
elif [ "$skip_backups" = true ]; then
    say info "Skipping database backup (-b)."
fi

# An rsync deploy overwrites tracked files with no git history to fall back on,
# so offer a copy of the tree first. Checkouts can recover with git alone.
if [ "$deploy_mode" = "rsync" ] && [ "$skip_backups" = false ]; then
    if ask_yes_no "Back up ${app_path} before overwriting it" "yes"; then
        folder_backup="${backup_root}/www/$(basename "$app_path")-${stamp}"
        print info "Copying ${app_path} to ${folder_backup}..."
        rsync -a --exclude 'public/temporary/' --exclude 'vendor/' \
            "$app_path/" "$folder_backup/" 2>>"$log_file" ||
            die "Folder backup failed. See ${log_file}."
        say success "Folder backed up to ${folder_backup}"
    fi
fi

# ------------------------------------------------------------------ source ---

print header "Updating source"

lock_before="$(checksum "$app_path/composer.lock")"
ref_before="$(installed_ref)"

# config/autoload holds this deployment's credentials but is tracked upstream,
# so park it and put it back once the new source is in place. Both deploy modes
# use the same stash, so there is one restore path to get wrong.
config_stash="$(mktemp -d)"
cp -a "$app_path/config/autoload/." "$config_stash/" || die "Could not stage config/autoload."

restore_config() {
    cp -a "$config_stash/." "$app_path/config/autoload/" || die "Could not restore config/autoload."
    rm -rf "$config_stash"
    say success "This deployment's config/autoload restored over the new tree."
}

# --- Acquire the source tree (rsync mode only) -------------------------------
#
# A persistent shallow mirror is updated with delta fetches, so after the first
# clone each run transfers only what changed instead of a fresh tarball. The
# tarball is the last resort for boxes without git.
acquire_source() {
    src_dir=""
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

    [ -n "$src_dir" ] || die "Could not obtain the source (mirror, clone and tarball all failed)."
}

if [ "$deploy_mode" = "git" ]; then
    # --- Checkout: fast-forward in place -------------------------------------
    command -v git &>/dev/null || die "${app_path} is a git checkout but git is not installed."

    branch="$(run_git -C "$app_path" rev-parse --abbrev-ref HEAD 2>/dev/null)"
    [ -n "$branch" ] && [ "$branch" != "HEAD" ] || die "Checkout is not on a branch (detached HEAD)."

    remote_url="$(run_git -C "$app_path" remote get-url origin 2>/dev/null)"
    [ -n "$remote_url" ] ||
        die "The checkout has no origin remote. Add one: git -C ${app_path} remote add origin ${REPO_GIT_URL}"

    # Local modifications outside config/autoload are code changes that belong
    # upstream — clobbering them silently is how deployments quietly diverge.
    dirty="$(run_git -C "$app_path" status --porcelain --untracked-files=no -- . ':(exclude)config/autoload')"
    if [ -n "$dirty" ]; then
        print warning "Tracked files modified outside config/autoload:"
        printf '%s\n' "$dirty"
        if [ "$force_dirty" = true ]; then
            # Stash rather than checkout, so -f stays recoverable.
            if run_git -C "$app_path" stash push --quiet -m "odkdash-upgrade ${stamp}" \
                -- . ':(exclude)config/autoload' 2>>"$log_file"; then
                say warning "Local modifications stashed (-f). Recover with: git stash list"
            else
                run_git -C "$app_path" checkout -- . 2>>"$log_file" ||
                    die "Could not discard local modifications."
                say warning "Local modifications discarded (-f)."
            fi
        else
            die "Push these changes upstream, or re-run with -f to set them aside."
        fi
    fi

    run_git -C "$app_path" checkout -- config/autoload 2>>"$log_file" || true

    print info "Fetching origin/${branch}..."
    # An SSH remote is the usual failure here: the key belongs to the admin who
    # cloned it, and this script runs as root.
    run_git -C "$app_path" fetch --prune origin "$branch" 2>>"$log_file" ||
        die "git fetch from ${remote_url} failed (see ${log_file}). If it is an SSH remote, switch it: git -C ${app_path} remote set-url origin ${REPO_GIT_URL}"

    run_git -C "$app_path" merge --ff-only "origin/${branch}" 2>>"$log_file" ||
        die "Fast-forward merge failed. The checkout has diverged from origin/${branch}; resolve it by hand."

    restore_config
    ref_after="$(run_git -C "$app_path" rev-parse --short HEAD)"

    if [ "$ref_before" = "$ref_after" ]; then
        say info "Already up to date at ${ref_after}."
    else
        say success "Updated ${ref_before} → ${ref_after} on ${branch}"
        run_git -C "$app_path" log --oneline "${ref_before}..${ref_after}" | head -20
    fi
else
    # --- No checkout: rsync a fresh source tree over the install --------------
    acquire_source

    # Stamp the ref into the tree. .git never reaches the instance, so
    # VERSION.txt is the only way an rsynced install knows what it is running.
    if [ -d "$src_dir/.git" ]; then
        run_git -C "$src_dir" rev-parse --short HEAD >"$src_dir/VERSION.txt" 2>/dev/null || true
    else
        echo "tarball-${stamp}" >"$src_dir/VERSION.txt"
    fi

    # Symlinked directories are how instances point uploads at another volume;
    # -K makes rsync fill them rather than replace them with real dirs. No
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
        die "rsync deploy failed. See ${log_file}."

    # config/autoload is excluded above, but the stash is still restored so the
    # two modes converge on the same known-good state.
    restore_config

    rm -rf "$temp_dir"
    ref_after="$(installed_ref)"

    if [ "$ref_before" = "$ref_after" ]; then
        say info "Already up to date at ${ref_after}."
    else
        say success "Updated ${ref_before} → ${ref_after}"
    fi
fi

# ------------------------------------------------------------- ownership ---

# Before composer, not after: the deploy can land root-owned files, and
# composer runs as the web user.
chown -R "$web_user":"$web_user" "$app_path"

# ----------------------------------------------------------- dependencies ---

print header "Dependencies"

cd "$app_path" || die "Could not enter ${app_path}."

if [ ! -d "$app_path/vendor" ] ||
   [ "$ref_before" != "$ref_after" ] ||
   [ "$lock_before" != "$(checksum "$app_path/composer.lock")" ]; then
    as_web composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader 2>&1 |
        tee -a "$log_file" || die "composer install failed."
    say success "Dependencies installed."
else
    as_web composer dump-autoload -o --no-interaction 2>&1 | tee -a "$log_file"
    say info "Source unchanged; refreshed the autoloader only."
fi

# ------------------------------------------------------------- migrations ---

print header "Database"

as_web php bin/migrate 2>&1 | tee -a "$log_file" || die "Migrations failed. See ${log_file}."

# --status is the confirmation that the schema actually reached the code's
# version; a migration run can report progress and still leave work pending.
status_output="$(as_web php bin/migrate --status 2>&1)"
printf '%s\n' "$status_output" | tee -a "$log_file"

if printf '%s' "$status_output" | grep -qF "database is up to date"; then
    say success "Schema is at the version this code expects."
else
    say warning "Migrations still pending. Re-run: (cd ${app_path} && sudo -u ${web_user} php bin/migrate)"
fi

# ------------------------------------------------------------ permissions ---

print header "Permissions"

# Directories the app writes to at runtime. Git tracks none of their contents,
# and the rsync deploy excludes them, so a fresh install can be missing them.
for dir in var/log var/api-logs var/backups data/cache public/temporary public/uploads uploads backup; do
    mkdir -p "$app_path/$dir"
done

chown -R "$web_user":"$web_user" "$app_path"
chmod -R u+rwX,g+rwX "$app_path/var" "$app_path/data/cache" "$app_path/public/temporary" \
    "$app_path/public/uploads" "$app_path/uploads" "$app_path/backup"
chmod +x "$app_path/cron.sh" "$app_path/bin/migrate" "$app_path/bin/upgrade.sh" 2>/dev/null

say success "Ownership set to ${web_user} and runtime directories made writable."

# ------------------------------------------------------------------- cron ---

print header "Cron"

# crunz reads crunz.yml from the working directory; cron.sh cd's there itself,
# so the crontab line needs no cd of its own.
cron_line="* * * * * ${app_path}/cron.sh >> ${app_path}/var/log/crunz.log 2>&1"
current_crontab="$(crontab -u "$web_user" -l 2>/dev/null || true)"

if printf '%s\n' "$current_crontab" | grep -Fq "${app_path}/cron.sh"; then
    say info "crunz cron entry already present for ${web_user}."
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

print header "Upgrade complete"
print success "${app_path} is at $(installed_ref)"
print info "Backups: ${backup_root}"
print info "Log: ${log_file}"
log_action "Upgrade complete for ${app_path} (${ref_before} → ${ref_after})."

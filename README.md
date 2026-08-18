ODK Dashboard Application
=======================

SPI RRT dashboard. Laminas MVC application on PHP 8.2 or later, MySQL, and Apache.

## Operational scripts

| Script | Purpose |
|---|---|
| `bin/upgrade.sh` | Upgrades an installed instance to the latest master |
| `bin/remote-backup.sh` | Sets up scheduled off-machine backups of the database and configuration |
| `bin/migrate` | Applies pending database migrations |
| `cron.sh` | Runs the crunz scheduler, one crontab entry per server |

## How to upgrade an installation

Run the upgrade script straight from the repo, as root on the server:

```bash
sudo bash -c "$(curl -fsSL "https://raw.githubusercontent.com/deforay/odkdash/master/bin/upgrade.sh?v=$(date +%s)")" -- -A
```

Pass the script to `bash -c` as an argument, exactly as shown. Piping it in with
`curl ... | sudo bash` breaks the prompts, because sudo then feeds the script
itself to them and takes the default for every question.

The `?v=` query defeats the raw CDN cache. The `-A` flag upgrades every odkdash
instance in `/var/www`, so a server hosting both `odkdash` and
`odkdash-trinidad` needs one run.

The script updates the source, installs dependencies, applies migrations, resets
permissions, installs the crunz cron entry, and reloads Apache. It does no
system-level work, so PHP, MySQL, and Apache are left as the setup script
configured them.

Two things are backed up to `/var/odkdash-backup/` before anything changes. The
`config/autoload/` tarball is taken on every run, including under `-b`. The
database is dumped with `db-tools` unless `-b` says otherwise. The code is not
backed up, because it comes back from git and the deploy neither deletes
untracked files nor touches uploads.

These backups sit on the same disk as the installation. For off-machine copies,
see [How to set up off-machine backups](#how-to-set-up-off-machine-backups).

To upgrade one instance, pass `-p`:

```bash
sudo /var/www/odkdash/bin/upgrade.sh -p /var/www/odkdash-trinidad
```

| Option | Effect |
|---|---|
| `-A` | Upgrades every odkdash instance found in `/var/www` |
| `-p PATH` | Upgrades one instance. Defaults to `/var/www/odkdash` |
| `-b` | Skips the database backup. The `config/autoload/` tarball is always taken |
| `-y` | Answers every prompt with its default and never blocks |

One failing instance does not stop the others. The summary at the end lists what
upgraded and what failed, and the exit status is non-zero if any instance failed.

The script preserves everything under `config/autoload/`, which holds the
database credentials for the deployment. It also preserves `var/`, `uploads/`,
`public/uploads/`, `public/temporary/`, and `data/cache/`.

To confirm the upgrade succeeded, check the schema version:

```bash
cd /var/www/odkdash && sudo -u www-data php bin/migrate --status
```

The output reports the current version and `(none — database is up to date)`.

## How to set up off-machine backups

Run the backup setup script on the server, as root:

```bash
sudo bash -c "$(curl -fsSL "https://raw.githubusercontent.com/deforay/odkdash/master/bin/remote-backup.sh?v=$(date +%s)")"
```

Pass the script to `bash -c` as an argument, as shown. Piping it in with
`curl ... | sudo bash` breaks the prompts.

The script asks where the backups should go and how often to take them. It
covers three destinations: another Linux machine over SSH, a Windows shared
folder over SMB, and a USB or external drive plugged into the server. Answers
are saved to `/etc/odkdash/backup.conf`, so a re-run is a matter of pressing
Enter through the prompts.

Two things are backed up, and nothing else. The database is dumped fresh on
every run with `db-tools`. `config/autoload/` is copied as a tarball. The
application code is not backed up, because it comes back from git and
`bin/upgrade.sh` rebuilds a deployment from those two pieces plus a checkout.

Each run lands in its own dated folder at the destination, so a run that copies
a corrupt dump cannot destroy the good one before it. The last 14 runs are kept
by default.

Once setup finishes, the runner at `/usr/local/bin/odkdash-backup.sh` handles
everything from root's crontab:

| Command | Effect |
|---|---|
| `sudo odkdash-backup.sh` | Runs a backup now |
| `sudo odkdash-backup.sh --status` | Reports when the last backup ran and whether it worked |
| `sudo odkdash-backup.sh --test` | Checks the connection and the database, changing nothing |
| `sudo odkdash-backup.sh --list` | Lists the backups held at the destination |
| `sudo odkdash-backup.sh --restore latest` | Restores the newest backup into this installation |
| `sudo odkdash-backup.sh --disable` | Stops the scheduled backups |

A restore copies the current database and configuration aside first, into
`/var/odkdash-backup/before-restore/`.

To recover a rebuilt server, run the setup script on it and pick the existing
folder when it offers the backups already stored at the destination. It restores
the newest one and carries on the same backup history.

## How to apply migrations on their own

Run the migration script from the installation directory:

```bash
cd /var/www/odkdash && sudo -u www-data php bin/migrate
```

To preview the statements without running them, pass `--dry-run`. See
`migrations/README.md` for the migration file format.

## How to schedule the crunz jobs

Add one entry to the `www-data` crontab:

```
* * * * * /var/www/odkdash/cron.sh >> /var/www/odkdash/var/log/crunz.log 2>&1
```

`bin/upgrade.sh` adds this entry when it is missing. The schedule itself lives in
`tasks/GeneralTasks.php`.

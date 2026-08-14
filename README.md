ODK Dashboard Application
=======================

SPI RRT dashboard. Laminas MVC application on PHP 8.2 or later, MySQL, and Apache.

## Operational scripts

| Script | Purpose |
|---|---|
| `bin/upgrade.sh` | Upgrades an installed instance to the latest master |
| `bin/migrate` | Applies pending database migrations |
| `cron.sh` | Runs the crunz scheduler, one crontab entry per server |

## How to upgrade an installation

Run the upgrade script straight from the repo, as root on the server:

```bash
curl -fsSL "https://raw.githubusercontent.com/deforay/odkdash/master/bin/upgrade.sh?v=$(date +%s)" | sudo bash -s -- -A
```

The `?v=` query defeats the raw CDN cache. The `-A` flag upgrades every odkdash
instance in `/var/www`, so a server hosting both `odkdash` and
`odkdash-trinidad` needs one run.

The script updates the source, installs dependencies, applies migrations, resets
permissions, installs the crunz cron entry, and reloads Apache. It backs up
`config/autoload/` and the database to `/var/odkdash-backup/` first. Prompts read
from the terminal, so they still work through the pipe.

To upgrade one instance, pass `-p`:

```bash
sudo /var/www/odkdash/bin/upgrade.sh -p /var/www/odkdash-trinidad
```

| Option | Effect |
|---|---|
| `-A` | Upgrades every odkdash instance found in `/var/www` |
| `-p PATH` | Upgrades one instance. Defaults to `/var/www/odkdash` |
| `-b` | Skips the database and folder backups |
| `-f` | Sets aside local changes to tracked files on a git checkout |
| `-y` | Answers every prompt with its default and never blocks |

One failing instance does not stop the others. The summary at the end lists what
upgraded and what failed, and the exit status is non-zero if any instance failed.

The script preserves everything under `config/autoload/`, which holds the
database credentials for the deployment. It also preserves `var/`, `uploads/`,
`public/uploads/`, `public/temporary/`, and `data/cache/`.

If the script reports modified tracked files and stops, push those changes
upstream. To set them aside instead, re-run with `-f`.

To confirm the upgrade succeeded, check the schema version:

```bash
cd /var/www/odkdash && sudo -u www-data php bin/migrate --status
```

The output reports the current version and `(none — database is up to date)`.

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

<?php

use Crunz\Schedule;

$schedule = new Schedule();

$projectRoot = dirname(__DIR__);
$customConfigPath = "$projectRoot/config/autoload/custom.global.php";
$customConfig = is_file($customConfigPath) ? (require_once $customConfigPath) : [];
if (!is_array($customConfig)) {
    $customConfig = [];
}

// crunz.yml pins the global timezone to UTC so the dispatcher itself is
// deterministic, but we want time-anchored jobs (e.g. nightly backup) to
// fire at LOCAL clock time on whatever server we're on — Malawi, Jamaica,
// Zimbabwe, etc.
//
// Resolution order:
//   1. custom.global.php 'timezone' key (explicit per-deployment choice)
//   2. PHP's date_default_timezone_get() (server clock)
//   3. UTC (safe last-ditch default)
$timezone = $customConfig['timezone']
    ?? date_default_timezone_get()
    ?: 'UTC';

/** True if any entry in the section has a non-empty `url`. The dist
 *  template ships with empty placeholders, so the section being present
 *  isn't enough — we want at least one real project configured. */
$hasOdkProject = static function (array $config, string $key): bool {
    $entries = $config['odkcentral'][$key] ?? null;
    if (!is_array($entries)) {
        return false;
    }
    foreach ($entries as $entry) {
        if (!empty($entry['url'])) {
            return true;
        }
    }
    return false;
};

/** Email is considered configured when both an SMTP host and a username
 *  are set; the dist file has the host but a blank username. */
$hasEmailConfig = static function (array $config): bool {
    return !empty($config['email']['host'])
        && !empty($config['email']['config']['username']);
};

// ─── Nightly database backup ───
// db-tools dumps and gzips the DB into var/backups/db/, keeping the last
// 7 days (the retention is also pinned in db-tools.php for safety; passing
// it on the CLI keeps the schedule self-documenting).
$schedule->run('vendor/bin/db-tools backup --retention=7')
    ->daily()
    ->at('02:00')
    ->timezone($timezone)
    ->description('Nightly database backup')
    ->appendOutputTo(__DIR__ . '/../var/log/cron-backup.log');

// ─── ODK Central sync (v6 forms) — every minute ───
// Only registered if custom.global.php has a real spirrt project URL —
// otherwise the command would auth-fail every tick against empty creds.
if ($hasOdkProject($customConfig, 'spirrt')) {
    $schedule->run('vendor/bin/laminas sync-central-v6')
        ->everyMinute()
        ->timezone($timezone)
        ->description('Sync ODK Central spirrt (v6) submissions')
        ->appendOutputTo(__DIR__ . '/../var/log/cron-sync-central-v6.log');
}

// ─── ODK Central sync (v3 forms) — every minute ───
if ($hasOdkProject($customConfig, 'spirt')) {
    $schedule->run('vendor/bin/laminas sync-central-v3')
        ->everyMinute()
        ->timezone($timezone)
        ->description('Sync ODK Central spirt (v3) submissions')
        ->appendOutputTo(__DIR__ . '/../var/log/cron-sync-central-v3.log');
}

// ─── Outgoing mail queue worker ───
// Every minute; skip entirely when SMTP isn't configured so the box
// doesn't churn through cron logs trying to talk to an empty server.
if ($hasEmailConfig($customConfig)) {
    $schedule->run('vendor/bin/laminas send-mail')
        ->everyMinute()
        ->timezone($timezone)
        ->description('Flush pending outbound mail')
        ->appendOutputTo(__DIR__ . '/../var/log/cron-send-mail.log');
}

return $schedule;

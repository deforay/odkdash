<?php

use Crunz\Schedule;

$schedule = new Schedule();

// ─── Nightly database backup ───
// db-tools dumps and gzips the DB into var/backups/db/, keeping the last
// 7 days (the retention is also pinned in db-tools.php for safety; passing
// it on the CLI keeps the schedule self-documenting).
$schedule->run('vendor/bin/db-tools backup --retention=7')
    ->daily()
    ->at('02:00')
    ->description('Nightly database backup')
    ->appendOutputTo(__DIR__ . '/../var/log/cron-backup.log');

return $schedule;

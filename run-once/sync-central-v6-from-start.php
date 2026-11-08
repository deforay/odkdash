<?php

declare(strict_types=1);

/*
 * Runs the first V6 sync after migration 1.2.12, during the deploy.
 *
 * 1.2.12 added central_sync_checkpoint. A form with no checkpoint is
 * fetched from the start, so the first sync fetches every submission from
 * Central. It skips the stored ones and saves any that earlier syncs lost.
 * It also downloads each photo of a stored audit that is missing on disk.
 * That can take long, so it runs here and not in the next scheduled sync.
 *
 * The script fails and is not recorded when the sync is not complete: when
 * another sync holds the lock, a project fails, or a submission is not saved
 * in full. bin/migrate then warns, and the next bin/migrate runs it again.
 * The scheduled sync retries the same submissions in the meantime.
 */

require __DIR__ . '/../vendor/autoload.php';

use App\Services\ServiceDefinitions;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

App\RunOnce::run(__FILE__, function (Connection $db): void {
    ini_set('memory_limit', '-1');
    set_time_limit(0);

    $io = new SymfonyStyle(new ArrayInput([]), new ConsoleOutput());
    $io->title('First V6 sync from the start');
    if (!ServiceDefinitions::container(dirname(__DIR__))->get('OdkFormService')->syncOdkCentralV6($io)) {
        throw new \RuntimeException('The sync is not complete. See the messages above.');
    }
});

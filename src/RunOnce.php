<?php

declare(strict_types=1);

namespace App;

use App\Services\ServiceDefinitions;
use Doctrine\DBAL\Connection;
use Throwable;

/**
 * Runs a run-once/*.php script at most once per database.
 *
 * A script is a data fix that a SQL migration cannot express. It looks like:
 *
 *     require __DIR__ . '/../vendor/autoload.php';
 *
 *     App\RunOnce::run(__FILE__, function (Doctrine\DBAL\Connection $db): void {
 *         // the fix. Return to finish, throw to fail.
 *     });
 *
 * bin/migrate runs every script after the migrations and reads its exit code:
 *
 *   0  ran, and is recorded in s_run_once_scripts_log
 *   3  already recorded, so skipped
 *   1  threw. Nothing is recorded, so the next bin/migrate retries it.
 *
 * Pass -f to run a recorded script again.
 */
final class RunOnce
{
    public const int EXIT_RAN = 0;
    public const int EXIT_FAILED = 1;
    public const int EXIT_SKIPPED = 3;

    /** @param callable(Connection): void $work */
    public static function run(string $scriptFile, callable $work): never
    {
        if (PHP_SAPI !== 'cli') {
            exit(self::EXIT_FAILED);
        }

        $name = basename($scriptFile);
        $force = in_array('-f', $_SERVER['argv'] ?? [], true);
        $db = ServiceDefinitions::container(dirname(__DIR__))->get(Connection::class);

        if (!$force && $db->fetchOne('SELECT 1 FROM s_run_once_scripts_log WHERE script_name = ?', [$name]) !== false) {
            exit(self::EXIT_SKIPPED);
        }

        try {
            $work($db);
        } catch (Throwable $e) {
            fwrite(STDERR, "{$name} failed: {$e->getMessage()}\n");
            exit(self::EXIT_FAILED);
        }

        $db->executeStatement(
            'REPLACE INTO s_run_once_scripts_log (script_name, execution_date, status) VALUES (?, NOW(), ?)',
            [$name, $force ? 'forced' : 'executed']
        );
        exit(self::EXIT_RAN);
    }
}

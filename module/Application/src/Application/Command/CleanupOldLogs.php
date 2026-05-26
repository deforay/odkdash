<?php

namespace Application\Command;

use Application\Model\ApiLogsTable;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Sql;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Periodic cleanup for high-volume log tables (api_logs, form_dump).
 *
 * Default policy: drop rows older than --days (365) but ALWAYS keep at
 * least --floor most-recent rows per table even if older. The floor
 * stops the dashboard from ever looking "empty" if a low-activity site
 * goes quiet for a year (audits are infrequent — a naive age purge
 * would wipe the table and read as "broken").
 *
 * For each row deleted we also remove the associated compressed body
 * file on disk so var/api-logs and public/uploads/form_dump don't grow
 * unboundedly.
 */
class CleanupOldLogs extends Command
{
    private const FORM_DUMP_FLOOR = 500;
    private const API_LOGS_FLOOR = 5000;
    private const DEFAULT_DAYS = 365;

    public function __construct(private readonly Adapter $dbAdapter, private readonly ApiLogsTable $apiLogsTable)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('days', null, InputOption::VALUE_REQUIRED, 'Delete rows older than N days', (string) self::DEFAULT_DAYS);
        $this->addOption('api-logs-floor', null, InputOption::VALUE_REQUIRED, 'Minimum api_logs rows to retain', (string) self::API_LOGS_FLOOR);
        $this->addOption('form-dump-floor', null, InputOption::VALUE_REQUIRED, 'Minimum form_dump rows to retain', (string) self::FORM_DUMP_FLOOR);
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Report what would be deleted without touching anything');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $days = max(1, (int) $input->getOption('days'));
        $apiFloor = max(0, (int) $input->getOption('api-logs-floor'));
        $formFloor = max(0, (int) $input->getOption('form-dump-floor'));
        $dryRun = (bool) $input->getOption('dry-run');

        $cutoff = new \DateTimeImmutable("-$days days");
        $io->title('Log cleanup' . ($dryRun ? ' (dry-run)' : ''));
        $io->writeln(sprintf('Cutoff: <info>%s</info> · api_logs floor: <info>%d</info> · form_dump floor: <info>%d</info>',
            $cutoff->format('Y-m-d H:i'), $apiFloor, $formFloor));

        $this->cleanApiLogs($io, $cutoff, $apiFloor, $dryRun);
        $this->cleanFormDump($io, $cutoff, $formFloor, $dryRun);

        return Command::SUCCESS;
    }

    private function cleanApiLogs(SymfonyStyle $io, \DateTimeImmutable $cutoff, int $floor, bool $dryRun): void
    {
        if ($dryRun) {
            // Count-only: rows older than cutoff, excluding floor.
            $sql = new Sql($this->dbAdapter);
            $total = (int) ($sql->prepareStatementForSqlObject(
                $sql->select('api_logs')->columns(['c' => new Expression('COUNT(*)')])
            )->execute()->current()['c'] ?? 0);
            $oldCount = (int) ($sql->prepareStatementForSqlObject(
                $sql->select('api_logs')
                    ->columns(['c' => new Expression('COUNT(*)')])
                    ->where(['created_at < ?' => $cutoff->format('Y-m-d H:i:s')])
            )->execute()->current()['c'] ?? 0);
            $wouldDelete = max(0, min($oldCount, $total - $floor));
            $io->writeln(sprintf('api_logs: total=%d · older-than-cutoff=%d · would-delete=%d (after %d-row floor)',
                $total, $oldCount, $wouldDelete, $floor));
            return;
        }

        $result = $this->apiLogsTable->purgeOlderThan($cutoff, $floor);
        $filesRemoved = 0;
        foreach ($result['paths'] as $paths) {
            foreach (['request_body_path', 'response_body_path'] as $key) {
                if (!empty($paths[$key]) && is_file($paths[$key]) && @unlink($paths[$key])) {
                    $filesRemoved++;
                }
            }
        }
        $io->writeln(sprintf('api_logs: deleted <info>%d</info> rows, removed <info>%d</info> body files', $result['deleted'], $filesRemoved));
    }

    private function cleanFormDump(SymfonyStyle $io, \DateTimeImmutable $cutoff, int $floor, bool $dryRun): void
    {
        $sql = new Sql($this->dbAdapter);

        // Find the cutoff effectively in use: the LATER of $cutoff or
        // the received_on of row #($floor+1) ordered newest-first. That
        // guarantees we never go below the floor regardless of dataset.
        $effectiveCutoff = $cutoff->format('Y-m-d H:i:s');
        if ($floor > 0) {
            $boundary = $sql->select('form_dump')
                ->columns(['received_on'])
                ->order('received_on DESC')
                ->offset($floor)
                ->limit(1);
            $boundaryRow = $sql->prepareStatementForSqlObject($boundary)->execute()->current();
            if ($boundaryRow && !empty($boundaryRow['received_on'])) {
                // If the floor-boundary row is NEWER than our age cutoff,
                // there aren't enough rows older than the cutoff to need
                // floor protection — leave $effectiveCutoff as-is. If
                // it's OLDER, raise the cutoff so the floor wins.
                $effectiveCutoff = min($effectiveCutoff, $boundaryRow['received_on']);
            } else {
                // Table has <= $floor rows. Nothing to delete.
                $io->writeln('form_dump: under floor — nothing to delete');
                return;
            }
        }

        $victims = iterator_to_array($sql->prepareStatementForSqlObject(
            $sql->select('form_dump')
                ->columns(['id', 'file_path'])
                ->where(['received_on < ?' => $effectiveCutoff])
        )->execute());

        if (!$victims) {
            $io->writeln('form_dump: nothing to delete');
            return;
        }

        if ($dryRun) {
            $io->writeln(sprintf('form_dump: would delete <info>%d</info> rows (cutoff %s)', count($victims), $effectiveCutoff));
            return;
        }

        $ids = array_column($victims, 'id');
        $del = $sql->delete('form_dump');
        $del->where->in('id', $ids);
        $sql->prepareStatementForSqlObject($del)->execute();

        $filesRemoved = 0;
        foreach ($victims as $v) {
            if (!empty($v['file_path']) && is_file($v['file_path']) && @unlink($v['file_path'])) {
                $filesRemoved++;
            }
        }
        $io->writeln(sprintf('form_dump: deleted <info>%d</info> rows, removed <info>%d</info> dump files', count($victims), $filesRemoved));
    }
}

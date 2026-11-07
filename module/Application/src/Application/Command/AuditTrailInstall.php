<?php

namespace Application\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

/**
 * Bring the audit trail tables and triggers in line with the audit tables.
 *
 * audit_spi_form_v_3 and audit_spi_form_v_6 hold a copy of an audit each
 * time it is inserted, updated or deleted. The audit trail page reads them.
 * Triggers on spi_form_v_3 and spi_form_v_6 write the copies.
 *
 * For each version, this command:
 *
 * 1. Creates the trail table when it is missing: the audit's columns with
 *    no keys, after the action, the revision and the time. MyISAM numbers
 *    each audit's revisions from 1, through the (id, revision) key.
 * 2. Adds each audit column the trail table lacks, and gives each one whose
 *    type or character set differs the audit's. Every trail column accepts
 *    NULL. The changes run in strict mode, so one that would cut trail
 *    values short fails and changes nothing, while the others still apply.
 * 3. Replaces each trigger whose definition differs, naming each column
 *    both tables have. See replaceTriggers(). A column a change could not
 *    add goes unrecorded and is reported, but writing an audit never fails
 *    because of the trail.
 *
 * No column is named by hand, so a migration that changes spi_form_v_3 or
 * spi_form_v_6 needs no matching change. bin/migrate runs this after the
 * migrations. Running it again changes nothing. It never deletes a table,
 * a column or a trail row.
 */
class AuditTrailInstall extends Command
{
    /** Each audit table and the table that holds its trail. */
    private const array TABLES = [
        'spi_form_v_3' => 'audit_spi_form_v_3',
        'spi_form_v_6' => 'audit_spi_form_v_6',
    ];

    /** The trail table's own columns, before the audit's columns. */
    private const array TRAIL_COLUMNS = ['action', 'revision', 'dt_datetime'];

    /** Each trigger: its name suffix, when it fires, the action and the row it copies. */
    private const array TRIGGERS = [
        ['ai', 'AFTER INSERT', 'insert', 'NEW'],
        ['au', 'AFTER UPDATE', 'update', 'NEW'],
        ['bd', 'BEFORE DELETE', 'delete', 'OLD'],
    ];

    /** Seconds a statement waits for a table that a sync or an edit holds. */
    private const int LOCK_WAIT_SECONDS = 30;

    private bool $dryRun = false;

    public function __construct(private readonly Connection $db)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Create or update the audit trail tables and triggers');
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Print the SQL without running it');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->dryRun = (bool) $input->getOption('dry-run');

        $failed = false;
        foreach (self::TABLES as $source => $trail) {
            if ($this->dryRun) {
                $io->section($trail);
            }
            try {
                $problems = $this->install($source, $trail, $output);
            } catch (Throwable $e) {
                $io->error("{$trail}: {$e->getMessage()} Changes to {$source} may not be recorded. Fix the cause, then run: php bin/console audit-trail:install");
                $failed = true;
                continue;
            }

            if ($problems === null) {
                $io->note("{$source} does not exist. Skipped.");
            } elseif ($problems !== []) {
                // A column that failed to be added is not recorded. One that
                // failed to change type is, with its old type.
                $io->warning(["{$trail}: these column changes did not apply. The triggers record the columns {$trail} has.", ...$problems]);
                $failed = true;
            } elseif (!$this->dryRun) {
                $io->writeln("{$trail}: table and triggers up to date.");
            }
        }

        return $failed ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * Brings one trail table and its triggers in line. Returns the column
     * changes that failed, or null when the audit table does not exist.
     *
     * @return list<string>|null
     */
    private function install(string $source, string $trail, OutputInterface $output): ?array
    {
        $sourceColumns = $this->columns($source);
        if ($sourceColumns === []) {
            return null;
        }
        $s = $this->db->quoteSingleIdentifier($source);
        $t = $this->db->quoteSingleIdentifier($trail);

        if (!$this->dryRun) {
            $this->db->executeStatement('SET SESSION lock_wait_timeout = ' . self::LOCK_WAIT_SECONDS);
        }

        $trailColumns = $this->columns($trail);
        if ($trailColumns === []) {
            $this->runSql($output, "CREATE TABLE {$t} ENGINE = MyISAM SELECT * FROM {$s} WHERE 1 = 0");
            // The new table has every audit column, with the audit's type.
            $trailColumns = $sourceColumns;
        }
        // Also when an earlier run created the table and stopped here.
        if (!isset($trailColumns['action'])) {
            $this->runSql($output, "ALTER TABLE {$t}"
                . " ADD `action` VARCHAR(8) DEFAULT 'insert' FIRST,"
                . ' ADD `revision` INT NOT NULL AUTO_INCREMENT AFTER `action`,'
                . ' ADD `dt_datetime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `revision`,'
                . ' MODIFY COLUMN `id` INT NOT NULL,'
                . ' ADD PRIMARY KEY (`id`, `revision`)');
        }

        // All changes in one ALTER, since each ALTER copies the table. When
        // that fails, each change on its own, so the others still apply.
        $problems = [];
        $changes = $this->columnChanges($sourceColumns, $trailColumns);
        if ($changes !== []) {
            try {
                $this->runStrict($output, "ALTER TABLE {$t} " . implode(', ', $changes));
            } catch (Throwable) {
                foreach ($changes as $column => $change) {
                    try {
                        $this->runStrict($output, "ALTER TABLE {$t} {$change}");
                    } catch (Throwable $e) {
                        $problems[] = "{$column}: {$e->getMessage()}";
                    }
                }
            }
        }

        // The columns the trail table has now. A dry run assumes every
        // change above worked.
        $recorded = $this->dryRun ? array_keys($sourceColumns) : array_keys(array_intersect_key($sourceColumns, $this->columns($trail)));
        $this->replaceTriggers($source, $trail, $recorded, $output);

        return $problems;
    }

    /**
     * Creates the trigger for each event that does not match the columns,
     * then drops the one it replaces. A trigger's name ends in a hash of
     * its definition, so an unchanged trigger is left alone, and the old
     * and new names differ. A CREATE that fails, for want of a privilege,
     * leaves the old trigger in place. Both tables are locked meanwhile, so
     * no write fires both triggers or neither.
     *
     * @param list<string> $recorded
     */
    private function replaceTriggers(string $source, string $trail, array $recorded, OutputInterface $output): void
    {
        $s = $this->db->quoteSingleIdentifier($source);
        $t = $this->db->quoteSingleIdentifier($trail);
        $names = implode(', ', array_map($this->db->quoteSingleIdentifier(...), [...self::TRAIL_COLUMNS, ...$recorded]));
        $existing = $this->db->fetchFirstColumn(
            'SELECT TRIGGER_NAME FROM INFORMATION_SCHEMA.TRIGGERS WHERE TRIGGER_SCHEMA = DATABASE() AND EVENT_OBJECT_TABLE = ?',
            [$source],
        );

        $steps = [];
        foreach (self::TRIGGERS as [$suffix, $timing, $action, $row]) {
            $values = implode(', ', array_map(
                fn(string $column): string => $row . '.' . $this->db->quoteSingleIdentifier($column),
                $recorded,
            ));
            $definition = "{$timing} ON {$s} FOR EACH ROW INSERT INTO {$t} ({$names}) VALUES ('{$action}', NULL, NOW(), {$values})";
            $name = "{$source}_trail_{$suffix}_" . substr(hash('sha256', $definition), 0, 8);

            // This event's other triggers: data/audit-triggers.sql named them
            // {source}_data__{suffix}, and this command names them by hash.
            // One is left over when a run stopped between a CREATE and its DROP.
            $old = array_filter($existing, static fn(string $trigger): bool
                => $trigger !== $name
                && ($trigger === "{$source}_data__{$suffix}"
                    || preg_match('/^' . preg_quote("{$source}_trail_{$suffix}_", '/') . '[0-9a-f]{8}$/', $trigger) === 1));

            if (!in_array($name, $existing, true)) {
                $steps[] = 'CREATE TRIGGER ' . $this->db->quoteSingleIdentifier($name) . ' ' . $definition;
            }
            foreach ($old as $trigger) {
                $steps[] = 'DROP TRIGGER IF EXISTS ' . $this->db->quoteSingleIdentifier($trigger);
            }
        }
        if ($steps === []) {
            return;
        }

        $this->runSql($output, "LOCK TABLES {$s} WRITE, {$t} WRITE");
        try {
            foreach ($steps as $sql) {
                $this->runSql($output, $sql);
            }
        } finally {
            $this->runSql($output, 'UNLOCK TABLES');
        }
    }

    /**
     * The ALTER clause for each trail column that differs from the audit
     * table, keyed by column name.
     *
     * @param array<string, array{string, bool, string}> $sourceColumns
     * @param array<string, array{string, bool, string}> $trailColumns
     * @return array<string, string>
     */
    private function columnChanges(array $sourceColumns, array $trailColumns): array
    {
        $changes = [];
        foreach ($sourceColumns as $column => [$type, , $charset]) {
            if ($column === 'id' || in_array($column, self::TRAIL_COLUMNS, true)) {
                continue;
            }
            $c = $this->db->quoteSingleIdentifier($column);
            if (!isset($trailColumns[$column])) {
                $changes[$column] = "ADD {$c} {$type}{$charset} NULL DEFAULT NULL";
            } elseif (
                strcasecmp($trailColumns[$column][0], $type) !== 0
                || strcasecmp($trailColumns[$column][2], $charset) !== 0
                || !$trailColumns[$column][1]
            ) {
                $changes[$column] = "MODIFY COLUMN {$c} {$type}{$charset} NULL DEFAULT NULL";
            }
        }
        // A column the audit table no longer has gets no value from the
        // triggers, so it must accept NULL.
        foreach ($trailColumns as $column => [$type, $nullable, $charset]) {
            if (!isset($sourceColumns[$column]) && !in_array($column, self::TRAIL_COLUMNS, true) && !$nullable) {
                $changes[$column] = 'MODIFY COLUMN ' . $this->db->quoteSingleIdentifier($column) . " {$type}{$charset} NULL DEFAULT NULL";
            }
        }

        return $changes;
    }

    private function runSql(OutputInterface $output, string $sql): void
    {
        if ($this->dryRun) {
            $output->writeln($sql . ';');
            return;
        }
        $this->db->executeStatement($sql);
    }

    /**
     * Runs a table change in strict mode, so a change that would cut trail
     * values short fails and leaves the table as it was. The triggers are
     * created outside it, in the session's own mode, which they keep.
     */
    private function runStrict(OutputInterface $output, string $sql): void
    {
        if ($this->dryRun) {
            $this->runSql($output, $sql);
            return;
        }
        $mode = (string) $this->db->fetchOne('SELECT @@SESSION.sql_mode');
        $this->db->executeStatement("SET SESSION sql_mode = CONCAT_WS(',', NULLIF(@@SESSION.sql_mode, ''), 'STRICT_ALL_TABLES')");
        try {
            $this->db->executeStatement($sql);
        } finally {
            $this->db->executeStatement('SET SESSION sql_mode = ?', [$mode]);
        }
    }

    /**
     * A table's columns, in order: each one's type, whether it accepts
     * NULL, and its character set and collation as a column definition
     * states them (empty for a column that has none). A MODIFY without them
     * would give the column the table's default. Empty when the table does
     * not exist.
     *
     * @return array<string, array{string, bool, string}>
     */
    private function columns(string $table): array
    {
        $rows = $this->db->fetchAllAssociative(
            'SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, CHARACTER_SET_NAME, COLLATION_NAME
               FROM INFORMATION_SCHEMA.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
              ORDER BY ORDINAL_POSITION',
            [$table],
        );

        $columns = [];
        foreach ($rows as $row) {
            $charset = $row['CHARACTER_SET_NAME'] !== null
                ? " CHARACTER SET {$row['CHARACTER_SET_NAME']} COLLATE {$row['COLLATION_NAME']}"
                : '';
            $columns[(string) $row['COLUMN_NAME']] = [(string) $row['COLUMN_TYPE'], $row['IS_NULLABLE'] === 'YES', $charset];
        }

        return $columns;
    }
}

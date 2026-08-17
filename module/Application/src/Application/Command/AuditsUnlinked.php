<?php

namespace Application\Command;

use Laminas\Db\Adapter\Adapter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Report v6 audits that are not linked to a facility.
 *
 * Every location filter joins facilities on spi_form_v_6.facility, so an audit
 * with that column empty is invisible to any mapped user — silently, and
 * without changing the total the page reports. This surfaces them.
 *
 * Two ways an audit ends up here. It carries no facilityname, so there is
 * nothing to resolve it against; those need the name filled in or the audit
 * deleted. Or it names a facility that is not in the master list yet, which is
 * the normal state for a pending audit naming a new facility — approval creates
 * the facility and the link. --fix resolves the first kind of straggler by
 * re-running the ingest link against facilities that already exist; it never
 * creates a facility, since that is approval's job.
 */
class AuditsUnlinked extends Command
{
    public function __construct(private readonly Adapter $dbAdapter)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('List v6 audits with no facility link (invisible to location-mapped users)');
        $this->addOption('fix', null, InputOption::VALUE_NONE, 'Link audits whose facility already exists in the master list');
        $this->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Maximum rows to list', '50');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = max(1, (int) $input->getOption('limit'));

        if ($input->getOption('fix')) {
            $linked = $this->dbAdapter->query(
                'UPDATE `spi_form_v_6` v
                   JOIN (SELECT f.facility_name, MIN(f.id) AS facility_id
                           FROM `spi_rt_3_facilities` f
                          WHERE TRIM(COALESCE(f.facility_name, "")) <> ""
                          GROUP BY f.facility_name) pick
                     ON pick.facility_name = v.facilityname
                    SET v.facility = pick.facility_id
                  WHERE v.facility IS NULL OR v.facility = 0',
                Adapter::QUERY_MODE_EXECUTE
            )->getAffectedRows();
            $io->success(sprintf('Linked %d audit(s) to an existing facility.', $linked));
        }

        $rows = $this->dbAdapter->query(
            'SELECT v.id, v.facilityname, v.facilityid, v.assesmentofaudit, v.status
               FROM `spi_form_v_6` v
              WHERE v.status <> "deleted"
                AND (v.facility IS NULL OR v.facility = 0)
              ORDER BY v.id',
            Adapter::QUERY_MODE_EXECUTE
        )->toArray();

        if (!$rows) {
            $io->success('Every v6 audit is linked to a facility.');
            return Command::SUCCESS;
        }

        $io->warning(sprintf('%d audit(s) are not linked to a facility and are hidden from location-mapped users.', count($rows)));
        $io->table(
            ['Audit', 'Facility name', 'Facility id', 'Audit date', 'Status', 'Why'],
            array_map(static function (array $r): array {
                $named = trim((string) $r['facilityname']) !== '';
                return [
                    $r['id'],
                    $named ? $r['facilityname'] : '(blank)',
                    trim((string) $r['facilityid']) !== '' ? $r['facilityid'] : '(blank)',
                    $r['assesmentofaudit'],
                    $r['status'],
                    $named ? 'not in facility list' : 'no facility name',
                ];
            }, array_slice($rows, 0, $limit))
        );

        if (count($rows) > $limit) {
            $io->writeln(sprintf('<comment>... and %d more. Use --limit to show them.</comment>', count($rows) - $limit));
        }

        $io->writeln('Audits named "not in facility list" are linked by <info>--fix</info>; blank-named ones need the name filled in or the audit removed.');

        return Command::SUCCESS;
    }
}

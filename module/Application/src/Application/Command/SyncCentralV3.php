<?php

namespace Application\Command;

use Application\Service\OdkFormService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SyncCentralV3 extends Command
{

    protected OdkFormService $odkFormService;

    public function __construct($odkFormService)
    {
        $this->odkFormService = $odkFormService;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Long-running CLI: lift web defaults so a big backlog doesn't trip
        // the request-side memory limit / max_execution_time.
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $io = new SymfonyStyle($input, $output);
        $io->title('Sync ODK Central (v3)');

        try {
            $this->odkFormService->syncOdkCentralV3($io);
        } catch (\Throwable $e) {
            $io->error($e->getMessage());
            $io->writeln('<comment>' . $e->getTraceAsString() . '</comment>', OutputInterface::VERBOSITY_VERBOSE);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

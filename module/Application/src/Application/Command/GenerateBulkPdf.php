<?php

namespace Application\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateBulkPdf extends Command
{

    public \Application\Service\OdkFormService  $odkFormService;

    public function __construct($odkFormService)
    {
        $this->odkFormService = $odkFormService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Generate queued bulk PDF exports for v3 and v6 forms');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->odkFormService->getDownloadDataList();
        $this->odkFormService->getV6DownloadDataList();
        return Command::SUCCESS;
    }
}

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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->odkFormService->getDownloadDataList();
        $this->odkFormService->getV5DownloadDataList();
        $this->odkFormService->getV6DownloadDataList();
        return 1;
    }
}

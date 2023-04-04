<?php

namespace Application\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncCentralV6 extends Command
{

    public \Application\Service\OdkFormService  $odkFormService;

    public function __construct($odkFormService)
    {
        $this->odkFormService = $odkFormService;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->odkFormService->syncOdkCentralV6();
        return 1;
    }
}

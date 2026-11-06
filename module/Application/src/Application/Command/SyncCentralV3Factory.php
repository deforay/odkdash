<?php

namespace Application\Command;

use Psr\Container\ContainerInterface;
use Application\Command\SyncCentralV3;

class SyncCentralV3Factory
{
    public function __invoke(ContainerInterface $container)
    {
        $odkFormService = $container->get('OdkFormService');
        return new SyncCentralV3($odkFormService);
    }
}

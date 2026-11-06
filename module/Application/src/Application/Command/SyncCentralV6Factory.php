<?php

namespace Application\Command;

use Psr\Container\ContainerInterface;
use Application\Command\SyncCentralV6;

class SyncCentralV6Factory
{
    public function __invoke(ContainerInterface $container)
    {
        $odkFormService = $container->get('OdkFormService');
        return new SyncCentralV6($odkFormService);
    }
}

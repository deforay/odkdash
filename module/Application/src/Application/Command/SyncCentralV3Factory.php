<?php

namespace Application\Command;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Application\Command\SyncCentralV3;

class SyncCentralV3Factory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $odkFormService = $container->get('OdkFormService');
        return new SyncCentralV3($odkFormService);
    }
}

<?php

namespace Application\Command;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CleanupOldLogsFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $dbAdapter = $container->get('Laminas\Db\Adapter\Adapter');
        $apiLogsTable = $container->get('ApiLogsTable');
        return new CleanupOldLogs($dbAdapter, $apiLogsTable);
    }
}

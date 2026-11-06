<?php

namespace Application\Command;

use Psr\Container\ContainerInterface;

class CleanupOldLogsFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $dbAdapter = $container->get('Laminas\Db\Adapter\Adapter');
        $apiLogsTable = $container->get('ApiLogsTable');
        return new CleanupOldLogs($dbAdapter, $apiLogsTable);
    }
}

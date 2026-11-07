<?php

namespace Application\Command;

use Doctrine\DBAL\Connection;
use Psr\Container\ContainerInterface;

class CleanupOldLogsFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $apiLogsTable = $container->get('ApiLogsTable');
        return new CleanupOldLogs($container->get(Connection::class), $apiLogsTable);
    }
}

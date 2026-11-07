<?php

namespace Application\Command;

use Doctrine\DBAL\Connection;
use Psr\Container\ContainerInterface;

class AuditTrailInstallFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new AuditTrailInstall($container->get(Connection::class));
    }
}

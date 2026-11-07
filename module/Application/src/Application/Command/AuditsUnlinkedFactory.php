<?php

namespace Application\Command;

use Doctrine\DBAL\Connection;
use Psr\Container\ContainerInterface;

class AuditsUnlinkedFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new AuditsUnlinked($container->get(Connection::class));
    }
}

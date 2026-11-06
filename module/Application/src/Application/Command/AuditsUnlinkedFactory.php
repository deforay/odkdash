<?php

namespace Application\Command;

use Psr\Container\ContainerInterface;

class AuditsUnlinkedFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $dbAdapter = $container->get('Laminas\Db\Adapter\Adapter');
        return new AuditsUnlinked($dbAdapter);
    }
}

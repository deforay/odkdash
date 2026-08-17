<?php

namespace Application\Command;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class AuditsUnlinkedFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $dbAdapter = $container->get('Laminas\Db\Adapter\Adapter');
        return new AuditsUnlinked($dbAdapter);
    }
}

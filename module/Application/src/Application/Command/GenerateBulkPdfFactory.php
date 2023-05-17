<?php

namespace Application\Command;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Application\Command\GenerateBulkPdf;

class GenerateBulkPdfFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $odkFormService = $container->get('OdkFormService');
        return new GenerateBulkPdf($odkFormService);
    }
}

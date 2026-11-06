<?php

namespace Application\Command;

use Psr\Container\ContainerInterface;
use Application\Command\GenerateBulkPdf;

class GenerateBulkPdfFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $odkFormService = $container->get('OdkFormService');
        return new GenerateBulkPdf($odkFormService);
    }
}

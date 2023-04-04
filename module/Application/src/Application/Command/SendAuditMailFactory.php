<?php

namespace Application\Command;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Application\Command\SendAuditMail;

class SendAuditMailFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $commonService = $container->get('CommonService');
        return new SendAuditMail($commonService);
    }
}

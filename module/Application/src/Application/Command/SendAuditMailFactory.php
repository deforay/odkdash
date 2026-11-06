<?php

namespace Application\Command;

use Psr\Container\ContainerInterface;
use Application\Command\SendAuditMail;

class SendAuditMailFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $commonService = $container->get('CommonService');
        return new SendAuditMail($commonService);
    }
}

<?php

namespace Application\Command;

use Psr\Container\ContainerInterface;
use Application\Command\SendTempMail;

class SendTempMailFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $commonService = $container->get('CommonService');
        return new SendTempMail($commonService);
    }
}

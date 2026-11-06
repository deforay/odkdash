<?php

namespace Application\Command;

use Psr\Container\ContainerInterface;

class ResetPasswordFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new ResetPassword($container->get('UsersTable'));
    }
}

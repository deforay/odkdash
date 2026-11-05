<?php

declare(strict_types=1);

namespace App\Routing;

use App\Http\Kernel;
use Laminas\Router\Http\TreeRouteStack;
use Psr\Container\ContainerInterface;

/**
 * Adds every Slim route to the Laminas router as a SlimRoute, under its
 * Laminas route name. Laminas code then builds links and redirects to moved
 * pages without knowing they moved: the url() view helper, the
 * redirect()->toRoute() and url()->fromRoute() controller plugins, and
 * $router->assemble() calls.
 *
 * Registered in Module::getServiceConfig() as a delegator on TreeRouteStack,
 * the class behind the HttpRouter and Router services. Outside a web request
 * there is no Kernel, and the router stays as configured.
 */
final class SlimRoutesDelegator
{
    public function __invoke(ContainerInterface $container, string $name, callable $callback): TreeRouteStack
    {
        /** @var TreeRouteStack $router */
        $router = $callback();

        $urls = Kernel::current()?->urls();
        foreach ($urls?->names() ?? [] as $route) {
            $router->addRoute($route, new SlimRoute($urls, $route));
        }

        return $router;
    }
}

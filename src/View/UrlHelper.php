<?php

declare(strict_types=1);

namespace App\View;

use Closure;
use Laminas\Router\RouteStackInterface;
use RuntimeException;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteParserInterface;

/**
 * $this->url(name, params, options), across both routers.
 *
 * A route already moved to Slim is built by Slim. Any other name is built
 * by the Laminas router, so a page on Slim can still link to pages that
 * have not moved yet. Slim routes keep their old Laminas route name, so
 * templates do not change when a page moves.
 */
final class UrlHelper
{
    private ?RouteStackInterface $laminasRouter = null;

    /** @param Closure(): RouteStackInterface $laminasRouterFactory */
    public function __construct(
        private readonly RouteCollectorInterface $slimRoutes,
        private readonly RouteParserInterface $slimRouteParser,
        private readonly Closure $laminasRouterFactory,
    ) {
    }

    /**
     * @param array<string, mixed> $params
     * @param array{query?: array<string, mixed>} $options
     */
    public function __invoke(?string $name = null, array $params = [], array $options = []): string
    {
        if ($name === null) {
            throw new RuntimeException('url() needs a route name outside Laminas.');
        }

        $query = $options['query'] ?? [];

        if ($this->isSlimRoute($name)) {
            return $this->slimRouteParser->urlFor($name, array_map('strval', $params), $query);
        }

        $this->laminasRouter ??= ($this->laminasRouterFactory)();

        $routeOptions = ['name' => $name];
        if ($query !== []) {
            $routeOptions['query'] = $query;
        }

        return $this->laminasRouter->assemble($params, $routeOptions);
    }

    private function isSlimRoute(string $name): bool
    {
        try {
            $this->slimRoutes->getNamedRoute($name);

            return true;
        } catch (RuntimeException) {
            return false;
        }
    }
}

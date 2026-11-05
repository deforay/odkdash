<?php

declare(strict_types=1);

namespace App\Routing;

use App\Http\SlimUrls;
use Laminas\Router\Http\RouteInterface;
use Laminas\Stdlib\RequestInterface;

/**
 * A Slim route as the Laminas router sees it. It never matches a request,
 * because public/index.php hands those to Slim before Laminas runs. It only
 * assembles URLs, so Laminas code that builds a link or a redirect to a
 * moved page still works.
 */
final class SlimRoute implements RouteInterface
{
    public function __construct(
        private readonly SlimUrls $urls,
        private readonly string $name,
    ) {
    }

    public static function factory($options = []): never
    {
        throw new \LogicException('SlimRoute is built by SlimRoutesDelegator, not from config.');
    }

    public function match(RequestInterface $request, $pathOffset = null, array $options = []): null
    {
        return null;
    }

    /** The path without the base path, which the Laminas router adds. */
    public function assemble(array $params = [], array $options = []): string
    {
        return $this->urls->path($this->name, $params);
    }

    public function getAssembledParams(): array
    {
        return [];
    }
}

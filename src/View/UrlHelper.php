<?php

declare(strict_types=1);

namespace App\View;

use App\Http\SlimUrls;
use RuntimeException;

/**
 * $this->url(name, params, options) in the templates.
 *
 * SlimUrls builds each URL from the route's segment pattern.
 */
final class UrlHelper
{
    public function __construct(private readonly SlimUrls $slimUrls)
    {
    }

    /**
     * @param array<string, mixed> $params
     * @param array{query?: array<string, mixed>} $options
     */
    public function __invoke(?string $name = null, array $params = [], array $options = []): string
    {
        if ($name === null) {
            throw new RuntimeException('url() needs a route name.');
        }
        if (!$this->slimUrls->has($name)) {
            throw new RuntimeException(sprintf('No route named "%s".', $name));
        }

        return $this->slimUrls->build($name, $params, $options['query'] ?? []);
    }
}

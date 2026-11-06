<?php

declare(strict_types=1);

namespace App\View;

use App\Http\SlimUrls;
use RuntimeException;

/**
 * $this->url(name, params, options) in the templates.
 *
 * Routes keep their Laminas route names, and SlimUrls builds each URL as
 * the Laminas router built it, so the templates did not change.
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

<?php

declare(strict_types=1);

namespace App\View;

use Closure;
use RuntimeException;

/**
 * The helpers templates call as $this->name(...).
 *
 * Each helper is built on first use, so a page that never translates never
 * loads the translator. Names match case-insensitively, as the Laminas
 * helper manager matched them: $this->GlobalConfigHelper() and
 * $this->globalConfigHelper() reach the same helper.
 */
final class ViewHelpers
{
    /** @var array<string, Closure(): callable> */
    private array $factories = [];

    /** @var array<string, callable> */
    private array $helpers = [];

    /** @param array<string, Closure(): callable> $factories */
    public function __construct(array $factories)
    {
        foreach ($factories as $name => $factory) {
            $this->factories[strtolower($name)] = $factory;
        }
    }

    /** @param array<int, mixed> $arguments */
    public function call(string $name, array $arguments): mixed
    {
        $key = strtolower($name);

        if (!isset($this->helpers[$key])) {
            if (!isset($this->factories[$key])) {
                throw new RuntimeException(sprintf('No view helper named "%s".', $name));
            }
            $this->helpers[$key] = ($this->factories[$key])();
        }

        return ($this->helpers[$key])(...$arguments);
    }
}

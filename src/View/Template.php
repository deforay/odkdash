<?php

declare(strict_types=1);

namespace App\View;

use Throwable;

/**
 * $this inside a .phtml template.
 *
 * Variables read both ways laminas-view allowed: as locals ($files) and as
 * properties ($this->files). Method calls go to the view helpers, except
 * partial() and layout(), which need the renderer.
 */
final class Template
{
    /** @param array<string, mixed> $variables */
    public function __construct(
        private readonly ViewRenderer $renderer,
        private readonly ViewHelpers $helpers,
        private array $variables,
    ) {
    }

    public function __get(string $name): mixed
    {
        return $this->variables[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->variables[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->variables[$name]);
    }

    /** @param array<int, mixed> $arguments */
    public function __call(string $name, array $arguments): mixed
    {
        return $this->helpers->call($name, $arguments);
    }

    /** @param array<string, mixed> $variables */
    public function partial(string $template, array $variables = []): string
    {
        return $this->renderer->render($template, $variables);
    }

    /**
     * The layout's variables, read as $this->layout()->acl. Laminas returned
     * the layout ViewModel here. Templates only ever read variables off it.
     */
    public function layout(): object
    {
        return (object) $this->renderer->layoutVariables();
    }

    public function renderFile(string $file): string
    {
        // Overwrites $file when a template has a variable of that name, which
        // is why the include reads the path from func_get_arg() instead.
        extract($this->variables);

        ob_start();
        try {
            include func_get_arg(0);
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return (string) ob_get_clean();
    }
}

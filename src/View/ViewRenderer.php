<?php

declare(strict_types=1);

namespace App\View;

use RuntimeException;

/**
 * Renders the .phtml templates.
 *
 * A template name is a path under templates/:
 * 'application/log-viewer/index' is
 * templates/application/log-viewer/index.phtml. Inside a
 * template, $this is a Template, so $this->translate(), $this->url(),
 * $this->layout()->acl and the other calls the templates make keep working.
 */
final class ViewRenderer
{
    /** @var array<string, mixed> */
    private array $layoutVariables = [];

    public function __construct(
        private readonly string $viewDir,
        private readonly ViewHelpers $helpers,
    ) {
    }

    /**
     * Sets a variable on the layout. The layout reads it as $name, and page
     * templates read it as $this->layout()->name. The access middleware puts
     * the ACL here.
     */
    public function setLayoutVariable(string $name, mixed $value): void
    {
        $this->layoutVariables[$name] = $value;
    }

    /** @return array<string, mixed> */
    public function layoutVariables(): array
    {
        return $this->layoutVariables;
    }

    /**
     * Renders a template inside a layout.
     *
     * @param array<string, mixed> $variables
     */
    public function page(string $template, array $variables = [], string $layout = 'layout/layout'): string
    {
        $content = $this->render($template, $variables);

        return $this->render($layout, [...$this->layoutVariables, 'content' => $content]);
    }

    /**
     * Renders one template on its own, as a terminal ViewModel or a partial did.
     *
     * @param array<string, mixed> $variables
     */
    public function render(string $template, array $variables = []): string
    {
        return (new Template($this, $this->helpers, $variables))->renderFile($this->resolve($template));
    }

    private function resolve(string $template): string
    {
        if (str_contains($template, '..')) {
            throw new RuntimeException(sprintf('Template name "%s" is not allowed.', $template));
        }

        $file = $this->viewDir . '/' . $template;
        if (!str_ends_with($file, '.phtml')) {
            $file .= '.phtml';
        }

        if (!is_file($file)) {
            throw new RuntimeException(sprintf('Template "%s" not found at %s.', $template, $file));
        }

        return $file;
    }
}

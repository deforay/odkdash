<?php

declare(strict_types=1);

namespace App\Http;

use App\View\UrlHelper;
use App\View\ViewRenderer;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Builds the responses a Laminas action used to return.
 *
 * Every HTML and text response carries "text/html; charset=UTF-8". That is
 * what PHP sent for Laminas responses, which set no Content-Type of their
 * own, including the ones whose body is JSON. Some jQuery code parses those
 * bodies itself and breaks if the type changes.
 */
final class Responder
{
    private const string HTML = 'text/html; charset=UTF-8';

    public function __construct(
        private readonly ResponseFactoryInterface $responses,
        private readonly ViewRenderer $view,
        private readonly UrlHelper $url,
    ) {
    }

    /**
     * A template inside a layout: what returning a ViewModel did.
     *
     * @param array<string, mixed> $variables
     */
    public function page(string $template, array $variables = [], string $layout = 'layout/layout'): ResponseInterface
    {
        return $this->html($this->view->page($template, $variables, $layout));
    }

    /**
     * A template without the layout: what a terminal ViewModel did.
     *
     * @param array<string, mixed> $variables
     */
    public function fragment(string $template, array $variables = []): ResponseInterface
    {
        return $this->html($this->view->render($template, $variables));
    }

    /** A body as is: what $this->getResponse()->setContent() did. */
    public function html(string $body, int $status = 200): ResponseInterface
    {
        $response = $this->responses->createResponse($status)->withHeader('Content-Type', self::HTML);
        $response->getBody()->write($body);

        return $response;
    }

    /** For endpoints that already sent application/json under Laminas. */
    public function json(string $body, int $status = 200): ResponseInterface
    {
        $response = $this->responses->createResponse($status)->withHeader('Content-Type', 'application/json');
        $response->getBody()->write($body);

        return $response;
    }

    /**
     * What $this->redirect()->toRoute() did.
     *
     * @param array<string, mixed> $params
     */
    public function redirect(string $route, array $params = []): ResponseInterface
    {
        return $this->responses->createResponse(302)->withHeader('Location', ($this->url)($route, $params));
    }

    /** What $this->redirect()->toUrl() did: the URL as given. */
    public function redirectToUrl(string $url): ResponseInterface
    {
        return $this->responses->createResponse(302)->withHeader('Location', $url);
    }

    public function status(int $status): ResponseInterface
    {
        return $this->responses->createResponse($status);
    }
}

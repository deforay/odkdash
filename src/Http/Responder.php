<?php

declare(strict_types=1);

namespace App\Http;

use App\View\UrlHelper;
use App\View\ViewRenderer;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Factory\StreamFactory;

/**
 * Builds the responses the handlers return.
 *
 * Every HTML and text response carries "text/html; charset=UTF-8", including
 * the ones whose body is JSON. Some jQuery code parses those bodies itself
 * and breaks if the type changes.
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

    /**
     * A template that sends its own headers, such as a TCPDF Output(..., 'I')
     * that sets application/pdf with header(). The response sets no
     * Content-Type, so the emitter does not replace the template's.
     *
     * @param array<string, mixed> $variables
     */
    public function output(string $template, array $variables = []): ResponseInterface
    {
        $body = $this->view->render($template, $variables);
        $response = $this->responses->createResponse();
        $response->getBody()->write($body);

        return $response;
    }

    /** A body as is: what $this->getResponse()->setContent() did. */
    public function html(string $body, int $status = 200): ResponseInterface
    {
        $response = $this->responses->createResponse($status)->withHeader('Content-Type', self::HTML);
        $response->getBody()->write($body);

        return $response;
    }

    /** For the endpoints whose callers expect application/json. */
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

    /** A file on disk, streamed rather than read into memory. "inline" shows it in the browser. */
    public function file(string $path, string $contentType, string $disposition = 'attachment'): ResponseInterface
    {
        return $this->responses->createResponse()
            ->withHeader('Content-Type', $contentType)
            ->withHeader('Content-Disposition', $disposition . '; filename="' . basename($path) . '"')
            ->withHeader('Content-Length', (string) filesize($path))
            ->withBody((new StreamFactory())->createStreamFromFile($path));
    }

    public function status(int $status): ResponseInterface
    {
        return $this->responses->createResponse($status);
    }
}

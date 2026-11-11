<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use Application\Session\Container;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;

/**
 * Checks the session's request token on every POST, PUT, PATCH and DELETE.
 *
 * Each page prints the token through templates/partials/csrf-token.phtml.
 * That script sends it as the X-CSRF-Token header on every same-origin
 * XMLHttpRequest and fetch, and as the csrf_token field on every form that
 * posts. The middleware removes the field before the handler reads the body.
 *
 * A wrong token answers 403. A missing token is only logged, with the path
 * and Referer, so a page that does not send the token yet shows up in the
 * log instead of failing.
 *
 * A session without a token yet has nothing to check against, for example
 * a login form posted after the session expired. The request goes on.
 */
final class CsrfMiddleware implements MiddlewareInterface
{
    public const FIELD = 'csrf_token';
    public const HEADER = 'X-CSRF-Token';

    /**
     * Routes that take requests without a token. The browser error reporter
     * posts with sendBeacon, which cannot send the header.
     */
    private const EXCLUDED_ROUTES = ['client-errors'];

    private const CHECKED_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function __construct(private readonly ResponseFactoryInterface $responses)
    {
    }

    /** The session's token, made on first use. */
    public static function token(): string
    {
        $session = new Container('csrf');
        if (!is_string($session->token) || $session->token === '') {
            $session->token = bin2hex(random_bytes(32));
        }

        return $session->token;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!in_array(strtoupper($request->getMethod()), self::CHECKED_METHODS, true)) {
            return $handler->handle($request);
        }

        $body = $request->getParsedBody();
        $sent = $request->getHeaderLine(self::HEADER);
        if ($sent === '' && is_array($body) && is_string($body[self::FIELD] ?? null)) {
            $sent = $body[self::FIELD];
        }
        if (is_array($body) && array_key_exists(self::FIELD, $body)) {
            unset($body[self::FIELD]);
            $request = $request->withParsedBody($body);
        }
        unset($_POST[self::FIELD]);

        $path = $request->getUri()->getPath();
        $route = RouteContext::fromRequest($request)->getRoute();
        $expected = (new Container('csrf'))->token;
        if (in_array($route?->getName(), self::EXCLUDED_ROUTES, true) || !is_string($expected) || $expected === '') {
            return $handler->handle($request);
        }

        if ($sent === '') {
            error_log(sprintf(
                'Request token missing: %s %s, Referer: %s',
                $request->getMethod(),
                $path,
                $request->getHeaderLine('Referer') ?: '-'
            ));
            return $handler->handle($request);
        }

        if (!hash_equals($expected, $sent)) {
            error_log(sprintf('Request token wrong: %s %s', $request->getMethod(), $path));
            return $this->responses->createResponse(403);
        }

        return $handler->handle($request);
    }
}

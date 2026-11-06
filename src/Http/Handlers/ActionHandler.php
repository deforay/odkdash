<?php

declare(strict_types=1);

namespace App\Http\Handlers;

use App\Http\Parameters;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionMethod;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;

/**
 * A ported Laminas controller: one handler for a route with an {action}
 * placeholder, one public method per action.
 *
 * Action names map to methods as Laminas mapped them, so "check-password",
 * "check_password" and "checkPassword" all reach checkPasswordAction(). An
 * absent action is "index". An action without a public method is a 404.
 * Methods keep the Action suffix, so a ported controller reads like the
 * original and a public helper method cannot be reached from a URL.
 */
abstract class ActionHandler implements RequestHandlerInterface
{
    final public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $method = self::methodFor(self::action($request));

        if (!method_exists($this, $method) || !(new ReflectionMethod($this, $method))->isPublic()) {
            throw new HttpNotFoundException($request);
        }

        return $this->{$method}($request);
    }

    /** The action as spelled in the URL, or "index". */
    public static function action(ServerRequestInterface $request): string
    {
        $action = RouteContext::fromRequest($request)->getRoute()?->getArgument('action');

        return $action === null || $action === '' ? 'index' : $action;
    }

    /** Laminas AbstractController::getMethodFromAction(). */
    public static function methodFor(string $action): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace(['.', '-', '_'], ' ', $action)))) . 'Action';
    }

    /**
     * POST data as Laminas handed it to the services: a Parameters object,
     * readable as $params['x'] and $params->x, with null for missing keys.
     */
    protected function post(ServerRequestInterface $request): Parameters
    {
        $body = $request->getParsedBody();

        return new Parameters(is_array($body) ? $body : []);
    }

    protected function isPost(ServerRequestInterface $request): bool
    {
        return $request->getMethod() === 'POST';
    }

    /** A route placeholder such as {id}, or null. */
    protected function routeParam(ServerRequestInterface $request, string $name): ?string
    {
        return RouteContext::fromRequest($request)->getRoute()?->getArgument($name);
    }
}

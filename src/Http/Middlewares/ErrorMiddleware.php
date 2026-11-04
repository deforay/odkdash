<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use Application\Service\Logger;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpException;
use Throwable;

/**
 * Logs uncaught exceptions to the app log, as Module::onBootstrap does for
 * the Laminas side, and answers with a bare status instead of a stack trace.
 */
final class ErrorMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responses,
        private readonly Logger $logger,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (HttpException $e) {
            return $this->responses->createResponse($e->getCode());
        } catch (Throwable $e) {
            $this->logger->logException($e, sprintf('[dispatch.error] %s', (string) $request->getUri()));

            return $this->responses->createResponse(500);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use App\Http\Responder;
use Application\Service\Logger;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpException;
use Slim\Exception\HttpNotFoundException;
use Throwable;

/**
 * Turns exceptions into the error pages Laminas showed, and logs the
 * unexpected ones to the app log as Module::onBootstrap does.
 *
 * A missing action renders error/404 and any other exception renders
 * error/index, both inside the layout, with the variables the Laminas
 * strategies set. Exception details appear only when
 * view_manager.display_exceptions is on. If the error page itself fails,
 * the response is the bare status code.
 */
final class ErrorMiddleware implements MiddlewareInterface
{
    /** Built on demand: a working request never needs the view layer. */
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly Logger $logger,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (HttpNotFoundException $e) {
            return $this->page(404, 'error/404', [
                'message' => 'Page not found.',
                'reason' => 'error-controller-invalid',
                'display_not_found_reason' => false,
                'exception' => $e,
            ]);
        } catch (HttpException $e) {
            return $this->respond()->status($e->getCode());
        } catch (Throwable $e) {
            $this->logger->logException($e, sprintf('[dispatch.error] %s', (string) $request->getUri()));

            return $this->page(500, 'error/index', [
                'message' => 'An error occurred during execution; please try again later.',
                'exception' => $e,
            ]);
        }
    }

    /** @param array<string, mixed> $variables */
    private function page(int $status, string $template, array $variables): ResponseInterface
    {
        try {
            $variables['display_exceptions'] = $this->displayExceptions();
            $page = $this->respond()->page($template, $variables);

            return $page->withStatus($status);
        } catch (Throwable $e) {
            $this->logger->logException($e, sprintf('Rendering %s failed', $template));

            return $this->respond()->status($status);
        }
    }

    private function displayExceptions(): bool
    {
        $config = $this->container->get('config');

        return !empty($config['view_manager']['display_exceptions']);
    }

    private function respond(): Responder
    {
        return $this->container->get(Responder::class);
    }
}

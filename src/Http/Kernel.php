<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Handlers\ClientErrorHandler;
use App\Http\Middlewares\ErrorMiddleware;
use Application\Service\Logger;
use DI\Container;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Routing\RoutingResults;

use function DI\autowire;

/**
 * The Slim side of the move off laminas-mvc.
 *
 * public/index.php builds this first and asks handles(). Slim serves every
 * request that matches a route registered here. Every other request falls
 * through to the Laminas MVC application. Moving a page across means adding
 * its route here and removing it from module.config.php.
 */
final class Kernel
{
    private function __construct(
        private readonly App $app,
        private readonly ServerRequestInterface $request,
    ) {
    }

    public static function fromGlobals(): self
    {
        $container = new Container([
            ResponseFactoryInterface::class => static fn(): ResponseFactoryInterface => new ResponseFactory(),

            // Same channels the Laminas service manager hands out as 'Logger'
            // and 'ClientErrorLogger'. Neither needs Laminas to build.
            Logger::class => static fn(): Logger => new Logger(),
            'ClientErrorLogger' => static fn(): Logger => new Logger(channel: 'client-errors'),

            ErrorMiddleware::class => autowire(),
            ClientErrorHandler::class => static fn(ContainerInterface $c): ClientErrorHandler => new ClientErrorHandler(
                $c->get(ResponseFactoryInterface::class),
                $c->get('ClientErrorLogger'),
            ),
        ]);

        AppFactory::setContainer($container);
        $app = AppFactory::create();
        $app->setBasePath(self::basePath());

        $app->any('/client-errors', ClientErrorHandler::class);

        $app->addRoutingMiddleware();
        $app->add(ErrorMiddleware::class);

        $request = ServerRequestCreatorFactory::create()->createServerRequestFromGlobals();

        return new self($app, $request);
    }

    /**
     * True when a Slim route matches the path. A path that matches with the
     * wrong method still counts, so Slim answers it with a 405 rather than
     * Laminas answering it with a 404.
     */
    public function handles(): bool
    {
        $results = $this->app->getRouteResolver()->computeRoutingResults(
            $this->request->getUri()->getPath(),
            $this->request->getMethod(),
        );

        return $results->getRouteStatus() !== RoutingResults::NOT_FOUND;
    }

    public function run(): void
    {
        $this->app->run($this->request);
    }

    /**
     * Where the app lives under the web root: empty at the root, "/odkdash"
     * when installed in a sub-directory. Read from SCRIPT_NAME, as Laminas
     * does, but only when it names the front controller. Under the built-in
     * server's router mode it holds the request path instead.
     */
    private static function basePath(): string
    {
        $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));

        return str_ends_with($scriptName, '.php') ? rtrim(dirname($scriptName), '/') : '';
    }
}

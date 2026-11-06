<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Handlers\ClientErrorHandler;
use App\Http\Middlewares\AccessMiddleware;
use App\Http\Middlewares\ErrorMiddleware;
use App\Services\LaminasBridge;
use App\View\UrlHelper;
use App\View\ViewHelpers;
use App\View\ViewRenderer;
use Application\I18n\Translator;
use Application\Model\ApiLogsTable;
use Application\Service\AuditTrailService;
use Application\Service\CommonService;
use Application\Service\EventService;
use Application\Service\FacilityService;
use Application\Service\OdkFormService;
use Application\Service\ProvinceService;
use Application\Service\RoleService;
use Application\Service\UserService;
use Application\Service\Logger;
use Application\Session\Container as Session;
use Application\View\Helper\GetCountryDetailsByIdHelper;
use Application\View\Helper\GlobalConfigHelper;
use Application\View\Helper\HumanReadableDateFormat;
use Application\View\Helper\Translate;
use DI\Container;
use Laminas\Escaper\Escaper;
use Laminas\ServiceManager\ServiceManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Psr7\Factory\ResponseFactory;

use function DI\autowire;

/**
 * Builds the Slim app that serves every request. public/index.php runs it.
 *
 * The routes live in src/Http/Routes/. A path with no route answers the
 * error/404 page. Services still come from the Laminas service manager
 * through LaminasBridge.
 */
final class Kernel
{
    /**
     * The Slim pattern for each Laminas optional tail the routes use.
     *
     * FastRoute allows optional parts only at the end of a pattern, nested,
     * so /x[/:action][/][:id] becomes /x[/[{action}[/[{id}]]]]. That matches
     * /x, /x/, /x/edit, /x/edit/ and /x/edit/5, which covers every URL
     * Laminas builds for the route. A pattern with a tail not listed here
     * stops the app with an error naming it. Add its line here.
     */
    private const array SLIM_TAILS = [
        '[/:action][/][:id]' => '[/[{action}[/[{id}]]]]',
        '[/:action]' => '[/{action}]',
    ];

    private function __construct(
        private readonly App $app,
        private readonly ServerRequestInterface $request,
    ) {
    }

    public static function fromGlobals(): self
    {
        $appRoot = dirname(__DIR__, 2);
        $basePath = self::basePath();

        $container = new Container([
            ResponseFactoryInterface::class => static fn(): ResponseFactoryInterface => new ResponseFactory(),

            LaminasBridge::class => static fn(): LaminasBridge => new LaminasBridge(
                $appRoot,
                static fn(ServiceManager $services) => self::applyLocale($services),
            ),

            // Same channels the Laminas service manager hands out as 'Logger'
            // and 'ClientErrorLogger'. Neither needs Laminas to build.
            Logger::class => static fn(): Logger => new Logger(),
            'ClientErrorLogger' => static fn(): Logger => new Logger(channel: 'client-errors'),

            ViewRenderer::class => static fn(ContainerInterface $c): ViewRenderer => new ViewRenderer(
                $appRoot . '/templates',
                $c->get(ViewHelpers::class),
            ),
            ViewHelpers::class => static fn(ContainerInterface $c): ViewHelpers => self::viewHelpers($c, $basePath),
            SlimUrls::class => static fn(ContainerInterface $c): SlimUrls
                => new SlimUrls($c->get(App::class)->getRouteCollector()),
            UrlHelper::class => static fn(ContainerInterface $c): UrlHelper => new UrlHelper($c->get(SlimUrls::class)),

            HelperAccess::class => static fn(): HelperAccess => new HelperAccess($appRoot . '/config/helper-access.php'),
            // Handlers, middleware and the Responder are autowired: PHP-DI
            // builds any class without a definition from its constructor.
            ErrorMiddleware::class => autowire(),
            AccessMiddleware::class => autowire(),

            ClientErrorHandler::class => static fn(ContainerInterface $c): ClientErrorHandler => new ClientErrorHandler(
                $c->get(ResponseFactoryInterface::class),
                $c->get('ClientErrorLogger'),
            ),
            Responder::class => autowire(),

            // Services that still come from the Laminas service manager,
            // under the keys Module.php registers them with.
            ...self::fromLaminas([
                ApiLogsTable::class => 'ApiLogsTable',
                AuditTrailService::class => 'AuditTrailService',
                CommonService::class => 'CommonService',
                EventService::class => 'EventService',
                FacilityService::class => 'FacilityService',
                OdkFormService::class => 'OdkFormService',
                ProvinceService::class => 'ProvinceService',
                RoleService::class => 'RoleService',
                UserService::class => 'UserService',
            ]),
        ]);

        AppFactory::setContainer($container);
        $app = AppFactory::create();
        $app->setBasePath($basePath);
        $container->set(App::class, $app);

        // Route names are the old Laminas route names, and each route
        // registers the Laminas pattern it replaced, so $this->url() calls in
        // the templates produce the same links after a page moves.
        $urls = $container->get(SlimUrls::class);

        foreach (self::routes() as $name => $route) {
            self::addRoute($app, $urls, $name, $route);
        }

        // Slim runs the middleware added last first. The order a request
        // meets them is: errors, routing, body parsing, access, handler.
        $app->add(AccessMiddleware::class);
        $app->addBodyParsingMiddleware();
        $app->addRoutingMiddleware();
        $app->add(ErrorMiddleware::class);

        $request = ServerRequestCreatorFactory::create()->createServerRequestFromGlobals();

        return new self($app, $request);
    }

    /**
     * Every route in src/Http/Routes/, keyed by route name. Each file holds
     * one module's routes in the Laminas route config format. See
     * Routes/Application.php. bin/dev/helper-access reads them too.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function routes(): array
    {
        $routes = [];
        $files = glob(__DIR__ . '/Routes/*.php') ?: [];
        sort($files);
        foreach ($files as $file) {
            foreach (require $file as $name => $route) {
                if (isset($routes[$name])) {
                    throw new \RuntimeException(sprintf('Route "%s" is defined twice, again in %s.', $name, $file));
                }
                $routes[$name] = $route;
            }
        }

        return $routes;
    }

    /**
     * Registers one route: its name and Laminas pattern for URL building,
     * then the Slim route with its ACL resource and defaults.
     *
     * @param array<string, mixed> $route
     */
    private static function addRoute(App $app, SlimUrls $urls, string $name, array $route): void
    {
        $defaults = $route['defaults'] ?? [];
        $resource = $defaults['controller'] ?? null;
        unset($defaults['controller']);

        $urls->register($name, $route['route'], $defaults);
        if (!isset($route['handler'])) {
            return;
        }
        if ($resource === null && empty($route['public'])) {
            throw new \RuntimeException(sprintf('Route "%s" names no controller and is not public.', $name));
        }

        $methods = $route['methods'] ?? ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
        $pattern = $route['slim'] ?? self::slimPattern($route['route']);
        $slimRoute = $app->map($methods, $pattern, $route['handler'])->setName($name);
        if (!empty($route['public'])) {
            $slimRoute->setArgument('access', 'public');
        } else {
            $slimRoute->setArgument('resource', $resource);
        }
        // A value the URL leaves out, such as the action on /roles.
        foreach ($defaults as $key => $value) {
            $slimRoute->setArgument($key, (string) $value);
        }
    }

    private static function slimPattern(string $laminasPattern): string
    {
        $start = strpos($laminasPattern, '[');
        if ($start === false) {
            return $laminasPattern;
        }
        $tail = substr($laminasPattern, $start);

        return substr($laminasPattern, 0, $start) . (self::SLIM_TAILS[$tail]
            ?? throw new \RuntimeException(sprintf('No Slim pattern for "%s". Add one to Kernel::SLIM_TAILS.', $laminasPattern)));
    }

    public function run(): void
    {
        $this->app->run($this->request);
    }

    /**
     * PHP-DI definitions that fetch each class from the Laminas service
     * manager by its key there.
     *
     * @param array<class-string, string> $map
     * @return array<class-string, \Closure>
     */
    private static function fromLaminas(array $map): array
    {
        $definitions = [];
        foreach ($map as $class => $key) {
            $definitions[$class] = static fn(ContainerInterface $c): object => $c->get(LaminasBridge::class)->get($key);
        }

        return $definitions;
    }

    /**
     * The helpers the templates call. Names and signatures match the Laminas
     * helpers they replace.
     */
    private static function viewHelpers(ContainerInterface $c, string $basePath): ViewHelpers
    {
        $laminas = static fn(): LaminasBridge => $c->get(LaminasBridge::class);
        $escaper = static fn(): Escaper => new Escaper('utf-8');

        return new ViewHelpers([
            'translate' => static fn(): Translate => new Translate($laminas()->get('translator')),
            'basePath' => static fn(): \Closure => static fn(?string $file = null): string
                => $basePath . ($file !== null ? '/' . ltrim($file, '/') : ''),
            'url' => static fn(): UrlHelper => $c->get(UrlHelper::class),
            'escapeHtml' => static fn(): \Closure => static fn(mixed $value): string
                => $escaper()->escapeHtml((string) $value),
            'escapeHtmlAttr' => static fn(): \Closure => static fn(mixed $value): string
                => $escaper()->escapeHtmlAttr((string) $value),
            'humanReadableDateFormat' => static fn(): HumanReadableDateFormat => new HumanReadableDateFormat(),
            'GlobalConfigHelper' => static fn(): GlobalConfigHelper
                => new GlobalConfigHelper($laminas()->get('GlobalTable')),
            'GetCountryDetailsByIdHelper' => static fn(): GetCountryDetailsByIdHelper
                => new GetCountryDetailsByIdHelper($laminas()->get('CountriesTable')),
            // Laminas collected titles for the layout to print. The layouts
            // never print them, so a call outputs nothing.
            'headTitle' => static fn(): \Closure => static fn(mixed ...$ignored): string => '',
        ]);
    }

    /**
     * Sets the translator's locale, as Module::initTranslator does: the
     * user's chosen language, or the instance default from global_config.
     */
    private static function applyLocale(ServiceManager $services): void
    {
        /** @var Translator $translator */
        $translator = $services->get('translator');
        $language = (new Session('credo'))->language;
        if (empty($language)) {
            $language = $services->get('GlobalTable')->getGlobalValue('language');
        }
        $translator->setLocale((string) $language)->setFallbackLocale('en_US');
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

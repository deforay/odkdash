<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Handlers\ApiLogsHandler;
use App\Http\Handlers\AuditTrailHandler;
use App\Http\Handlers\ClientErrorHandler;
use App\Http\Handlers\CommonHandler;
use App\Http\Handlers\DashboardHandler;
use App\Http\Handlers\DashboardV6Handler;
use App\Http\Handlers\ConfigHandler;
use App\Http\Handlers\DistrictHandler;
use App\Http\Handlers\EmailHandler;
use App\Http\Handlers\EventHandler;
use App\Http\Handlers\FacilityHandler;
use App\Http\Handlers\ProvincesHandler;
use App\Http\Handlers\RolesHandler;
use App\Http\Handlers\SpiV3ReportsHandler;
use App\Http\Handlers\SpiV6ReportsHandler;
use App\Http\Handlers\UsersHandler;
use App\Http\Handlers\LogViewerHandler;
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
use Laminas\Router\Http\TreeRouteStack;
use Laminas\ServiceManager\ServiceManager;
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
    private static ?self $current = null;

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
                $appRoot . '/module/Application/view',
                $c->get(ViewHelpers::class),
            ),
            ViewHelpers::class => static fn(ContainerInterface $c): ViewHelpers => self::viewHelpers($c, $basePath),
            SlimUrls::class => static fn(ContainerInterface $c): SlimUrls
                => new SlimUrls($c->get(App::class)->getRouteCollector()),
            UrlHelper::class => static function (ContainerInterface $c) use ($basePath): UrlHelper {
                return new UrlHelper(
                    $c->get(SlimUrls::class),
                    static function () use ($c, $basePath): TreeRouteStack {
                        $router = $c->get(LaminasBridge::class)->get('HttpRouter');
                        $router->setBaseUrl($basePath);

                        return $router;
                    },
                );
            },

            ErrorMiddleware::class => autowire(),
            AccessMiddleware::class => autowire(),

            ClientErrorHandler::class => static fn(ContainerInterface $c): ClientErrorHandler => new ClientErrorHandler(
                $c->get(ResponseFactoryInterface::class),
                $c->get('ClientErrorLogger'),
            ),
            LogViewerHandler::class => autowire(),
            ApiLogsHandler::class => autowire(),
            RolesHandler::class => autowire(),
            UsersHandler::class => autowire(),
            ProvincesHandler::class => autowire(),
            ConfigHandler::class => autowire(),
            EventHandler::class => autowire(),
            AuditTrailHandler::class => autowire(),
            DistrictHandler::class => autowire(),
            EmailHandler::class => autowire(),
            CommonHandler::class => autowire(),
            DashboardHandler::class => autowire(),
            DashboardV6Handler::class => autowire(),
            SpiV3ReportsHandler::class => autowire(),
            SpiV6ReportsHandler::class => autowire(),
            FacilityHandler::class => autowire(),
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

        $urls->register('client-errors', '/client-errors');
        $app->any('/client-errors', ClientErrorHandler::class)
            ->setName('client-errors')
            ->setArgument('access', 'public');

        $urls->register('log-viewer', '/log-viewer');
        $app->map(['GET', 'POST'], '/log-viewer', LogViewerHandler::class)
            ->setName('log-viewer')
            ->setArgument('resource', 'Application\Controller\LogViewerController')
            ->setArgument('privilege', 'index');

        // Ported controllers. Laminas patterns of the form
        // /x[/:action][/][:id] become /x[/[{action}[/[{id}]]]] in Slim,
        // which matches /x, /x/, /x/edit, /x/edit/ and /x/edit/<id>.
        $controllers = [
            // name, Laminas pattern, handler, ACL resource, login-only actions
            ['api-logs', '/api-logs[/:action]', ApiLogsHandler::class, 'ApiLogsController', ''],
            ['roles', '/roles[/:action][/][:id]', RolesHandler::class, 'RolesController', ''],
            ['users', '/users[/:action][/][:id]', UsersHandler::class, 'UsersController', 'check-password'],
            ['provinces', '/provinces[/:action][/][:id]', ProvincesHandler::class, 'ProvincesController', 'getDistrictByProvince'],
            ['district', '/district[/:action][/][:id]', DistrictHandler::class, 'DistrictController', ''],
            ['config', '/config[/:action][/][:id]', ConfigHandler::class, 'ConfigController', ''],
            ['event-log', '/event-log[/:action][/][:id]', EventHandler::class, 'EventController', ''],
            ['audit-trail', '/audit-trail[/:action][/][:id]', AuditTrailHandler::class, 'AuditTrailController', ''],
            ['email', '/email[/:action][/][:id]', EmailHandler::class, 'EmailController', ''],
            ['common', '/common[/:action][/][:id]', CommonHandler::class, 'CommonController',
                'index,get-audit-location-based-on-form,generate-password'],
            ['dashboard', '/dashboard[/:action][/][:id]', DashboardHandler::class, 'DashboardController', ''],
            ['dashboard-v6', '/dashboard-v6[/:action][/][:id]', DashboardV6Handler::class, 'DashboardV6Controller', ''],
            ['spi-v3-reports', '/spi-v3-reports[/:action][/][:id]', SpiV3ReportsHandler::class, 'SpiV3ReportsController',
                'export-facility-report'],
            ['spi-v6-reports', '/spi-v6-reports[/:action][/][:id]', SpiV6ReportsHandler::class, 'SpiV6ReportsController',
                'export-facility-report'],
            ['spi-facility', '/facility[/:action][/][:id]', FacilityHandler::class, 'FacilityController', ''],
        ];
        foreach ($controllers as [$name, $laminasPattern, $handler, $controller, $loginOnly]) {
            $urls->register($name, $laminasPattern, ['action' => 'index']);
            $pattern = strtr($laminasPattern, [
                '[/:action][/][:id]' => '[/[{action}[/[{id}]]]]',
                '[/:action]' => '[/{action}]',
            ]);
            $route = $app->any($pattern, $handler)
                ->setName($name)
                ->setArgument('resource', 'Application\\Controller\\' . $controller);
            if ($loginOnly !== '') {
                $route->setArgument('login-only', $loginOnly);
            }
        }

        // A second Laminas name for one facility URL. The spi-facility route
        // serves the path, so only the URL is registered.
        $urls->register('upload-facility', '/facility/upload-facility');

        // Slim runs the middleware added last first. The order a request
        // meets them is: errors, routing, body parsing, access, handler.
        $app->add(AccessMiddleware::class);
        $app->addBodyParsingMiddleware();
        $app->addRoutingMiddleware();
        $app->add(ErrorMiddleware::class);

        $request = ServerRequestCreatorFactory::create()->createServerRequestFromGlobals();

        return self::$current = new self($app, $request);
    }

    /**
     * The Kernel public/index.php built for this request, or null outside a
     * web request. The Laminas router reaches Slim routes through it.
     */
    public static function current(): ?self
    {
        return self::$current;
    }

    /** URL building for Slim routes. The Laminas router reaches it through SlimRoutesDelegator. */
    public function urls(): SlimUrls
    {
        return $this->app->getContainer()->get(SlimUrls::class);
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

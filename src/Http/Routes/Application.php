<?php

declare(strict_types=1);

/**
 * Slim routes for the Application module, keyed by their Laminas route
 * names.
 *
 * Each entry is the route's entry in module.config.php with a handler
 * added, so moving a route is a copy:
 *
 * - route: the Laminas segment pattern. URLs are built from it as Laminas
 *   built them. Kernel::SLIM_TAILS turns it into the Slim pattern.
 * - defaults: as in Laminas. defaults.controller is the ACL resource, the
 *   value Module::preSetter() checked. defaults.action is the action, and
 *   the privilege, when the URL names none.
 * - handler: the class that serves the route. Without one, the name is
 *   registered for URL building only.
 * - public: true for a route that needs no login.
 * - methods: the HTTP methods. All of them when absent.
 *
 * The Kernel loads every file in this folder. A project with several
 * modules keeps one file per module.
 */

use App\Http\Handlers\ApiLogsHandler;
use App\Http\Handlers\AuditTrailHandler;
use App\Http\Handlers\ClientErrorHandler;
use App\Http\Handlers\CommonHandler;
use App\Http\Handlers\ConfigHandler;
use App\Http\Handlers\DashboardHandler;
use App\Http\Handlers\DashboardV6Handler;
use App\Http\Handlers\DistrictHandler;
use App\Http\Handlers\EmailHandler;
use App\Http\Handlers\EventHandler;
use App\Http\Handlers\FacilityHandler;
use App\Http\Handlers\LogViewerHandler;
use App\Http\Handlers\ProvincesHandler;
use App\Http\Handlers\RolesHandler;
use App\Http\Handlers\SpiV3Handler;
use App\Http\Handlers\SpiV3ReportsHandler;
use App\Http\Handlers\SpiV6ReportsHandler;
use App\Http\Handlers\UsersHandler;

$controller = static fn(string $route, string $handler, string $controller): array => [
    'route' => $route,
    'handler' => $handler,
    'defaults' => ['controller' => 'Application\\Controller\\' . $controller, 'action' => 'index'],
];

return [
    'client-errors' => [
        'route' => '/client-errors',
        'handler' => ClientErrorHandler::class,
        'public' => true,
    ],
    'log-viewer' => [
        'route' => '/log-viewer',
        'handler' => LogViewerHandler::class,
        'defaults' => ['controller' => 'Application\\Controller\\LogViewerController', 'action' => 'index'],
        'methods' => ['GET', 'POST'],
    ],
    'api-logs' => $controller('/api-logs[/:action]', ApiLogsHandler::class, 'ApiLogsController'),
    'roles' => $controller('/roles[/:action][/][:id]', RolesHandler::class, 'RolesController'),
    'users' => $controller('/users[/:action][/][:id]', UsersHandler::class, 'UsersController'),
    'provinces' => $controller('/provinces[/:action][/][:id]', ProvincesHandler::class, 'ProvincesController'),
    'district' => $controller('/district[/:action][/][:id]', DistrictHandler::class, 'DistrictController'),
    'config' => $controller('/config[/:action][/][:id]', ConfigHandler::class, 'ConfigController'),
    'event-log' => $controller('/event-log[/:action][/][:id]', EventHandler::class, 'EventController'),
    'audit-trail' => $controller('/audit-trail[/:action][/][:id]', AuditTrailHandler::class, 'AuditTrailController'),
    'email' => $controller('/email[/:action][/][:id]', EmailHandler::class, 'EmailController'),
    'common' => $controller('/common[/:action][/][:id]', CommonHandler::class, 'CommonController'),
    'dashboard' => $controller('/dashboard[/:action][/][:id]', DashboardHandler::class, 'DashboardController'),
    'dashboard-v6' => $controller('/dashboard-v6[/:action][/][:id]', DashboardV6Handler::class, 'DashboardV6Controller'),
    'spi-v3-reports' => $controller('/spi-v3-reports[/:action][/][:id]', SpiV3ReportsHandler::class, 'SpiV3ReportsController'),
    'spi-v6-reports' => $controller('/spi-v6-reports[/:action][/][:id]', SpiV6ReportsHandler::class, 'SpiV6ReportsController'),
    'spi-facility' => $controller('/facility[/:action][/][:id]', FacilityHandler::class, 'FacilityController'),
    // A second name for one facility URL. spi-facility serves the path.
    'upload-facility' => [
        'route' => '/facility/upload-facility',
        'defaults' => ['controller' => 'Application\\Controller\\FacilityController', 'action' => 'upload-facility'],
    ],
    'spi-v3-form' => $controller('/spi-v3[/:action][/][:id]', SpiV3Handler::class, 'SpiV3Controller'),
    // The dashboards link to the V3 approved audits page by this name.
    'view-data' => [
        'route' => '/view-data',
        'handler' => SpiV3Handler::class,
        'defaults' => ['controller' => 'Application\\Controller\\SpiV3Controller', 'action' => 'view-data'],
    ],
];

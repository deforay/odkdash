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
use App\Http\Handlers\SpiV6Handler;
use App\Http\Handlers\SpiV6ReportsHandler;
use App\Http\Handlers\UsersHandler;

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
    'api-logs' => [
        'route' => '/api-logs[/:action]',
        'handler' => ApiLogsHandler::class,
        'defaults' => ['controller' => 'Application\\Controller\\ApiLogsController', 'action' => 'index'],
    ],
    'roles' => [
        'route' => '/roles[/:action][/][:id]',
        'handler' => RolesHandler::class,
        'defaults' => ['controller' => 'Application\\Controller\\RolesController', 'action' => 'index'],
    ],
    'users' => [
        'route' => '/users[/:action][/][:id]',
        'handler' => UsersHandler::class,
        'defaults' => ['controller' => 'Application\\Controller\\UsersController', 'action' => 'index'],
    ],
    'provinces' => [
        'route' => '/provinces[/:action][/][:id]',
        'handler' => ProvincesHandler::class,
        'defaults' => ['controller' => 'Application\\Controller\\ProvincesController', 'action' => 'index'],
    ],
    'district' => [
        'route' => '/district[/:action][/][:id]',
        'handler' => DistrictHandler::class,
        'defaults' => ['controller' => 'Application\\Controller\\DistrictController', 'action' => 'index'],
    ],
    'config' => [
        'route' => '/config[/:action][/][:id]',
        'handler' => ConfigHandler::class,
        'defaults' => ['controller' => 'Application\\Controller\\ConfigController', 'action' => 'index'],
    ],
    'event-log' => [
        'route' => '/event-log[/:action][/][:id]',
        'handler' => EventHandler::class,
        'defaults' => ['controller' => 'Application\\Controller\\EventController', 'action' => 'index'],
    ],
    'audit-trail' => [
        'route' => '/audit-trail[/:action][/][:id]',
        'handler' => AuditTrailHandler::class,
        'defaults' => ['controller' => 'Application\\Controller\\AuditTrailController', 'action' => 'index'],
    ],
    'email' => [
        'route' => '/email[/:action][/][:id]',
        'handler' => EmailHandler::class,
        'defaults' => ['controller' => 'Application\\Controller\\EmailController', 'action' => 'index'],
    ],
    'common' => [
        'route' => '/common[/:action][/][:id]',
        'handler' => CommonHandler::class,
        'defaults' => ['controller' => 'Application\\Controller\\CommonController', 'action' => 'index'],
    ],
    'dashboard' => [
        'route' => '/dashboard[/:action][/][:id]',
        'handler' => DashboardHandler::class,
        'defaults' => ['controller' => 'Application\\Controller\\DashboardController', 'action' => 'index'],
    ],
    'dashboard-v6' => [
        'route' => '/dashboard-v6[/:action][/][:id]',
        'handler' => DashboardV6Handler::class,
        'defaults' => ['controller' => 'Application\\Controller\\DashboardV6Controller', 'action' => 'index'],
    ],
    'spi-v3-reports' => [
        'route' => '/spi-v3-reports[/:action][/][:id]',
        'handler' => SpiV3ReportsHandler::class,
        'defaults' => ['controller' => 'Application\\Controller\\SpiV3ReportsController', 'action' => 'index'],
    ],
    'spi-v6-reports' => [
        'route' => '/spi-v6-reports[/:action][/][:id]',
        'handler' => SpiV6ReportsHandler::class,
        'defaults' => ['controller' => 'Application\\Controller\\SpiV6ReportsController', 'action' => 'index'],
    ],
    'spi-facility' => [
        'route' => '/facility[/:action][/][:id]',
        'handler' => FacilityHandler::class,
        'defaults' => ['controller' => 'Application\\Controller\\FacilityController', 'action' => 'index'],
    ],
    // A second name for one facility URL. spi-facility serves the path.
    'upload-facility' => [
        'route' => '/facility/upload-facility',
        'defaults' => ['controller' => 'Application\\Controller\\FacilityController', 'action' => 'upload-facility'],
    ],
    'spi-v3-form' => [
        'route' => '/spi-v3[/:action][/][:id]',
        'handler' => SpiV3Handler::class,
        'defaults' => ['controller' => 'Application\\Controller\\SpiV3Controller', 'action' => 'index'],
    ],
    // The dashboards link to the V3 approved audits page by this name.
    'view-data' => [
        'route' => '/view-data',
        'handler' => SpiV3Handler::class,
        'defaults' => ['controller' => 'Application\\Controller\\SpiV3Controller', 'action' => 'view-data'],
    ],
    'spi-v6-form' => [
        'route' => '/spi-v6[/:action][/][:id]',
        'handler' => SpiV6Handler::class,
        'defaults' => ['controller' => 'Application\\Controller\\SpiV6Controller', 'action' => 'index'],
    ],
    // The V6 dashboard links to the approved audits pages by these names.
    'view-data-v6' => [
        'route' => '/view-data-v6',
        'handler' => SpiV6Handler::class,
        'defaults' => ['controller' => 'Application\\Controller\\SpiV6Controller', 'action' => 'view-data-v6'],
    ],
    'view-data-section-zero-v6' => [
        'route' => '/view-data-section-zero-v6',
        'handler' => SpiV6Handler::class,
        'defaults' => ['controller' => 'Application\\Controller\\SpiV6Controller', 'action' => 'view-data-section-zero-v6'],
    ],
    'view-data-section-zero-protocol-v6' => [
        'route' => '/view-data-section-zero-protocol-v6',
        'handler' => SpiV6Handler::class,
        'defaults' => ['controller' => 'Application\\Controller\\SpiV6Controller', 'action' => 'view-data-section-zero-protocol-v6'],
    ],
];

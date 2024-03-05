<?php

namespace Application;

use Application\Command\SendTempMail;
use Application\Command\SendAuditMail;
use Application\Command\SyncCentralV3;
use Application\Command\SyncCentralV6;
use Application\Command\GenerateBulkPdf;
use Application\Command\SendTempMailFactory;
use Application\Command\SendAuditMailFactory;
use Application\Command\SyncCentralV3Factory;
use Application\Command\SyncCentralV6Factory;
use Application\Command\GenerateBulkPdfFactory;

return array(
    'router' => array(
        'routes' => array(
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'home' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route' => '/[/:action][/][:id]',
                    'defaults' => array(
                        'controller'    => 'Application\Controller\IndexController',
                        'action'        => 'index',
                    ),
                ),

            ),
            'homeAuditPerformance' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/audit-performance',
                    'defaults' => array(
                        'controller' => 'Application\Controller\IndexController',
                        'action' => 'audit-performance',
                    ),
                ),
            ),
            'homeAuditLocations' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/audit-locations',
                    'defaults' => array(
                        'controller' => 'Application\Controller\IndexController',
                        'action' => 'audit-locations',
                    ),
                ),
            ),
            'odk-receiver' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/receiver[/]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\ReceiverController',
                        'action' => 'index',
                    ),
                ),
            ),
            'odk-receiver-v5' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/receiver-spi-v5[/]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\ReceiverSpiV5Controller',
                        'action' => 'index',
                    ),
                ),
            ),

            'odk-receiver-v6' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/receiver-spi-v6[/]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\ReceiverSpiV6Controller',
                        'action' => 'index',
                    ),
                ),
            ),
            'login' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/login[/:action]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\LoginController',
                        'action' => 'index',
                    ),
                ),
            ),
            'spi-v3-form' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/spi-v3[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\SpiV3Controller',
                        'action' => 'index',
                    ),
                ),
            ),
            'spi-v5-form' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/spi-v5[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\SpiV5Controller',
                        'action' => 'index',
                    ),
                ),
            ),
            'spi-v6-form' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/spi-v6[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\SpiV6Controller',
                        'action' => 'index',
                    ),
                ),
            ),
            'spi-facility' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/facility[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\FacilityController',
                        'action' => 'index',
                    ),
                ),
            ),
            'spi-v3-reports' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/spi-v3-reports[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\SpiV3ReportsController',
                        'action' => 'index',
                    ),
                ),
            ),

            'spi-v5-reports' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/spi-v5-reports[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\SpiV5ReportsController',
                        'action' => 'index',
                    ),
                ),
            ),
            'spi-v6-reports' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/spi-v6-reports[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\SpiV6ReportsController',
                        'action' => 'index',
                    ),
                ),
            ),
            'common' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/common[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\CommonController',
                        'action' => 'index',
                    ),
                ),
            ),
            'roles' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/roles[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\RolesController',
                        'action' => 'index',
                    ),
                ),
            ),
            'user-login-history' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/user-login-history[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\UserLoginHistoryController',
                        'action' => 'index',
                    ),
                ),
            ),
            'event-log' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/event-log[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\EventController',
                        'action' => 'index',
                    ),
                ),
            ),
            'audit-trail' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/audit-trail[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\AuditTrailController',
                        'action' => 'index',
                    ),
                ),
            ),
            'users' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/users[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\UsersController',
                        'action' => 'index',
                    ),
                ),
            ),
            'config' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/config[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\ConfigController',
                        'action' => 'index',
                    ),
                ),
            ),
            'email' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/email[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\EmailController',
                        'action' => 'index',
                    ),
                ),
            ),
            'dashboard' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/dashboard[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\DashboardController',
                        'action' => 'index',
                    ),
                ),
            ),
            'dashboard-v5' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/dashboard-v5[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\DashboardV5Controller',
                        'action' => 'index',
                    ),
                ),
            ),
            'dashboard-v6' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/dashboard-v6[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\DashboardV6Controller',
                        'action' => 'index',
                    ),
                ),
            ),
            'view-data' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/view-data',
                    'defaults' => array(
                        'controller' => 'Application\Controller\SpiV3Controller',
                        'action' => 'view-data',
                    ),
                ),
            ),
            'view-data-v6' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/view-data-v6',
                    'defaults' => array(
                        'controller' => 'Application\Controller\SpiV6Controller',
                        'action' => 'view-data-v6',
                    ),
                ),
            ),

            'view-data-section-zero-v6' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/view-data-section-zero-v6',
                    'defaults' => array(
                        'controller' => 'Application\Controller\SpiV6Controller',
                        'action' => 'view-data-section-zero-v6',
                    ),
                ),
            ),

            'view-data-section-zero-protocol-v6' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/view-data-section-zero-protocol-v6',
                    'defaults' => array(
                        'controller' => 'Application\Controller\SpiV6Controller',
                        'action' => 'view-data-section-zero-protocol-v6',
                    ),
                ),
            ),
            'view-data-v5' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/view-data-v5',
                    'defaults' => array(
                        'controller' => 'Application\Controller\SpiV5Controller',
                        'action' => 'view-data-v5',
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => [
        'abstract_factories' => [
            'Laminas\Cache\Service\StorageCacheAbstractServiceFactory',
            'Laminas\Log\LoggerAbstractServiceFactory',
        ],
        'factories' => [
            'translator' => 'Laminas\Mvc\I18n\TranslatorFactory',
            SendTempMail::class => SendTempMailFactory::class,
            SendAuditMail::class => SendAuditMailFactory::class,
            SyncCentralV3::class => SyncCentralV3Factory::class,
            SyncCentralV6::class => SyncCentralV6Factory::class,
            GenerateBulkPdf::class => GenerateBulkPdfFactory::class,
        ]
    ],
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'laminas-cli' => [
        'commands' => [
            'send-mail' => SendTempMail::class,
            'send-audit-mail' => SendAuditMail::class,
            'sync-central-v3' => SyncCentralV3::class,
            'sync-central-v6' => SyncCentralV6::class,
            'generate-bulk-pdf' => GenerateBulkPdf::class,
        ],
    ],
);

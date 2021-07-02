<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

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
                        'controller'    => 'Application\Controller\Index',
                        'action'        => 'index',
                    ),
                ),

            ),
            'homeAuditPerformance' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/audit-performance',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'audit-performance',
                    ),
                ),
            ),
            'homeAuditLocations' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/audit-locations',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'audit-locations',
                    ),
                ),
            ),
            'odk-receiver' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/receiver[/]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Receiver',
                        'action' => 'index',
                    ),
                ),
            ),
            'odk-receiver-v5' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/receiver-spi-v5[/]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\ReceiverSpiV5',
                        'action' => 'index',
                    ),
                ),
            ),

            'odk-receiver-v6' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/receiver-spi-v6[/]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\ReceiverSpiV6',
                        'action' => 'index',
                    ),
                ),
            ),
            'login' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/login[/:action]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Login',
                        'action' => 'index',
                    ),
                ),
            ),
            'spi-v3-form' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/spi-v3[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\SpiV3',
                        'action' => 'index',
                    ),
                ),
            ),
            'spi-v5-form' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/spi-v5[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\SpiV5',
                        'action' => 'index',
                    ),
                ),
            ),
            'spi-v6-form' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/spi-v6[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\SpiV6',
                        'action' => 'index',
                    ),
                ),
            ),
            'spi-facility' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/facility[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Facility',
                        'action' => 'index',
                    ),
                ),
            ),
            'spi-v3-reports' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/spi-v3-reports[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\SpiV3Reports',
                        'action' => 'index',
                    ),
                ),
            ),

            'spi-v5-reports' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/spi-v5-reports[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\SpiV5Reports',
                        'action' => 'index',
                    ),
                ),
            ),
            'spi-v6-reports' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/spi-v6-reports[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\SpiV6Reports',
                        'action' => 'index',
                    ),
                ),
            ),
            'common' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/common[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Common',
                        'action' => 'index',
                    ),
                ),
            ),
            'roles' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/roles[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Roles',
                        'action' => 'index',
                    ),
                ),
            ),
            'users' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/users[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Users',
                        'action' => 'index',
                    ),
                ),
            ),
            'config' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/config[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Config',
                        'action' => 'index',
                    ),
                ),
            ),
            'email' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/email[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Email',
                        'action' => 'index',
                    ),
                ),
            ),
            'dashboard' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/dashboard[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Dashboard',
                        'action' => 'index',
                    ),
                ),
            ),
            'dashboard-v5' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/dashboard-v5[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\DashboardV5',
                        'action' => 'index',
                    ),
                ),
            ),
            'dashboard-v6' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/dashboard-v6[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\DashboardV6',
                        'action' => 'index',
                    ),
                ),
            ),
            'view-data' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/view-data',
                    'defaults' => array(
                        'controller' => 'Application\Controller\SpiV3',
                        'action' => 'view-data',
                    ),
                ),
            ),
            'view-data-v6' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/view-data-v6',
                    'defaults' => array(
                        'controller' => 'Application\Controller\SpiV6',
                        'action' => 'view-data-v6',
                    ),
                ),
            ),

            'view-data-section-zero-v6' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/view-data-section-zero-v6',
                    'defaults' => array(
                        'controller' => 'Application\Controller\SpiV6',
                        'action' => 'view-data-section-zero-v6',
                    ),
                ),
            ),

            'view-data-section-zero-protocol-v6' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/view-data-section-zero-protocol-v6',
                    'defaults' => array(
                        'controller' => 'Application\Controller\SpiV6',
                        'action' => 'view-data-section-zero-protocol-v6',
                    ),
                ),
            ),
            'view-data-v5' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/view-data-v5',
                    'defaults' => array(
                        'controller' => 'Application\Controller\SpiV5',
                        'action' => 'view-data-v5',
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Laminas\Cache\Service\StorageCacheAbstractServiceFactory',
            'Laminas\Log\LoggerAbstractServiceFactory',
        ),
        'factories' => array(
            'translator' => 'Laminas\Mvc\Service\TranslatorServiceFactory',
        ),
    ),
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
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
                'mail-console-route' => array(
                    'type' => 'simple',
                    'options' => array(
                        'route' => 'send-mail',
                        'defaults' => array(
                            'controller' => 'Application\Controller\Cron',
                            'action' => 'send-mail'
                        ),
                    ),
                ),
                'db-backup-console-route' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'db-backup',
                        'defaults' => array(
                            'controller' => 'Application\Controller\Cron',
                            'action' => 'db-backup'
                        ),
                    ),
                ),
                'audit-mail-console-route' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'send-audit-mail',
                        'defaults' => array(
                            'controller' => 'Application\Controller\Cron',
                            'action' => 'send-audit-mail'
                        ),
                    ),
                ),
                'generate-bulk-pdf' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'generate-bulk-pdf',
                        'defaults' => array(
                            'controller' => 'Application\Controller\Cron',
                            'action' => 'generate-bulk-pdf'
                        ),
                    ),
                ),
                'sync-odk-spirt-v3' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'sync-odk-spirt-v3',
                        'defaults' => array(
                            'controller' => 'Application\Controller\Cron',
                            'action' => 'sync-odk-spirt-v3'
                        ),
                    ),
                ),
                'sync-odk-spirt-v6' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'sync-odk-spirt-v6',
                        'defaults' => array(
                            'controller' => 'Application\Controller\Cron',
                            'action' => 'sync-odk-spirt-v6'
                        ),
                    ),
                ),
            ),
        ),
    ),
);

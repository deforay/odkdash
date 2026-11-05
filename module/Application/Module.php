<?php

namespace Application;

use Laminas\Mvc\MvcEvent;
use Application\Model\Acl;
use Application\Session\Container;
use Application\Model\RolesTable;
use Application\Model\UsersTable;
use Application\Model\GlobalTable;
use Application\Model\EventLogTable;
use Application\Model\TempMailTable;
use Application\Service\RoleService;
use Application\Service\UserService;
use Laminas\Mvc\ModuleRouteListener;
use Application\Model\AuditMailTable;
use Application\Model\CountriesTable;
use Application\Model\ResourcesTable;
use Application\Service\EventService;
use Application\Service\TcpdfExtends;
use Application\Service\CommonService;
use Application\Model\SpiFormVer3Table;
use Application\Model\SpiFormVer6Table;
use Application\Model\UserRoleMapTable;
use Application\Service\OdkFormService;
use Application\Model\UserTokenMapTable;
use Application\Service\FacilityService;
use Application\Model\SpiFormLabelsTable;
use Application\Model\AuditSpiFormV3Table;
use Application\Model\AuditSpiFormV6Table;


use Application\Model\SpiForm6LabelsTable;
use Application\Model\UserCountryMapTable;
use Application\Service\AuditTrailService;
use Application\Model\SpiFormVer3TempTable;
use Application\Model\SpiRtFacilitiesTable;
use Application\Model\SpiFormVer3DownloadTable;

use Application\Model\SpiFormVer6DownloadTable;
use Application\View\Helper\GlobalConfigHelper;
use Application\Model\SpiFormVer3DuplicateTable;

use Application\Model\SpiFormVer6DuplicateTable;
use Application\View\Helper\GetCountryDetailsByIdHelper;
use Application\View\Helper\Translate;

use Application\Service\Logger;
use Application\Service\ProvinceService;
use Application\Service\ApiLogger;
use Application\Model\ApiLogsTable;
use Application\Model\GeographicalDivisionsTable;
use Application\Model\UserLocationMapTable;

class Module
{


    public function onBootstrap(MvcEvent $e)
    {
        /** @var \Laminas\Mvc\Application $application */
        $application = $e->getApplication();

        $eventManager        = $application->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        //$languagecontainer = new Container('language');

        if (php_sapi_name() != 'cli') {
            $eventManager->attach('dispatch', function (MvcEvent $e) {
                return $this->preSetter($e);
            }, 100);
        }

        $serviceManager = $application->getServiceManager();
        $logException = function (MvcEvent $event) use ($serviceManager): void {
            $exception = $event->getParam('exception');
            if (!$exception instanceof \Throwable) {
                return;
            }
            try {
                /** @var Logger $logger */
                $logger = $serviceManager->get('Logger');
                $request = $event->getRequest();
                $logger->logException($exception, sprintf(
                    '[%s] %s',
                    $event->getName(),
                    method_exists($request, 'getUriString') ? $request->getUriString() : ''
                ));
            } catch (\Throwable $t) {
                @error_log('dispatch.error logger failed: ' . $t->getMessage());
            }
        };
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, $logException, 100);
        $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, $logException, 100);

        $this->initTranslator($e);
    }

    public function preSetter(MvcEvent $e)
    {

        $session = new Container('credo');


        /** @var \Laminas\Mvc\Application $application*/
        $application = $e->getApplication();

        $diContainer = $application->getServiceManager();
        $commonService = $diContainer->get('CommonService');

        if (empty($session->countryName)) {
            $config = $commonService->getGlobalConfigDetails();
            $session->countryName = $config['country-name'];
        }
        /** @var \Laminas\Http\Request $request */
        $request = $e->getRequest();

        $isPublic = in_array($e->getRouteMatch()->getParam('controller'), [
            'Application\Controller\LoginController',
            'Application\Controller\IndexController',
            'Application\Controller\ReceiverController',
            'Application\Controller\ReceiverSpiV6Controller',
        ], true);

        // Without a login, a script request gets a 401. A page load gets
        // the redirect to the login page below.
        if (!$isPublic && $request->isXmlHttpRequest() && empty($session->userId)) {
            /** @var \Laminas\Http\PhpEnvironment\Response $response */
            $response = $e->getResponse();
            $response->setStatusCode(401);
            return $response;
        }

        if (!$request->isXmlHttpRequest() && !$isPublic) {
            if (empty($session) || empty($session->userId)) {
                $url = $e->getRouter()->assemble([], ['name' => 'login']);
                /** @var \Laminas\Http\PhpEnvironment\Response $response */
                $response = $e->getResponse();
                $response->getHeaders()->addHeaderLine('Location', $url);
                $response->setStatusCode(302);

                $response->sendHeaders();

                // To avoid additional processing
                // we can attach a listener for Event Route with a high priority
                $stopCallBack = function ($event) use ($response) {
                    $event->stopPropagation();
                    return $response;
                };
                //Attach the "break" as a listener with a high priority
                $application->getEventManager()->attach(MvcEvent::EVENT_ROUTE, $stopCallBack, -10000);
                return $response;
            } else {
                $diContainer = $application->getServiceManager();
                $viewModel = $application->getMvcEvent()->getViewModel();
                $acl = $diContainer->get('AppAcl');
                $viewModel->acl = $acl;
                $session->acl = serialize($acl);

                $params = $e->getRouteMatch()->getParams();
                $resource = $params['controller'];
                $privilege = $params['action'];

                $role = $session->roleCode;

                // if (!$acl->hasResource($resource) || (!$acl->isAllowed($role, $resource, $privilege))) {
                //     $e->setError('ACL_ACCESS_DENIED')->setParam('route', $e->getRouteMatch());
                //     $application->getEventManager()->trigger('dispatch.error', $e);
                // }


                // echo $role ."<br>";
                // echo $resource ."<br>";
                // echo $privilege ."<br>";

                // die;

                if (!$acl->hasResource($resource) || (!$acl->isAllowed($role, $resource, $privilege))) {

                    /** @var \Laminas\Http\PhpEnvironment\Response $response */
                    $response = $e->getResponse();
                    $response->setStatusCode(403);
                    $response->sendHeaders();

                    // To avoid additional processing
                    // we can attach a listener for Event Route with a high priority
                    $stopCallBack = function ($event) use ($response) {
                        $event->stopPropagation();
                        return $response;
                    };
                    //Attach the "break" as a listener with a high priority
                    $application->getEventManager()->attach(MvcEvent::EVENT_ROUTE, $stopCallBack, -10000);
                    return $response;
                }
            }
        } elseif (!empty($session->userId)) {
            $diContainer = $application->getServiceManager();
            $viewModel = $application->getMvcEvent()->getViewModel();
            $acl = $diContainer->get('AppAcl');
            $viewModel->acl = $acl;
            $session->acl = serialize($acl);

            // Record script requests the ACL does not grant, so the grants
            // can be reviewed in the log viewer.
            if (!$isPublic) {
                $resource = $e->getRouteMatch()->getParam('controller');
                $privilege = $e->getRouteMatch()->getParam('action');
                if (!$acl->hasResource($resource) || !$acl->isAllowed($session->roleCode, $resource, $privilege)) {
                    $diContainer->get('Logger')->warning('ACL denies script request', [
                        'role' => $session->roleCode,
                        'resource' => $resource,
                        'privilege' => $privilege,
                        'uri' => $request->getUriString(),
                        'user_id' => $session->userId,
                    ]);
                }
            }
        }
    }

    protected function initTranslator(MvcEvent $event)
    {
        $loginContainer = new Container('credo');
        $serviceManager = $event->getApplication()->getServiceManager();
        $translator = $serviceManager->get('translator');
        if (empty($loginContainer->language)) {
            $globalTable = $serviceManager->get('GlobalTable');
            $language = $globalTable->getGlobalValue('language');
        } else {
            $language = $loginContainer->language;
        }
        $translator->setLocale($language)
            ->setFallbackLocale('en_US');
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                'Application\Controller\IndexController' => new class {
                    public function __invoke($diContainer)
                    {
                        $commonService = $diContainer->get('CommonService');
                        $odkFormService = $diContainer->get('OdkFormService');
                        return new \Application\Controller\IndexController($odkFormService, $commonService);
                    }
                },
                'Application\Controller\ReceiverController' => new class {
                    public function __invoke($diContainer)
                    {
                        $odkFormService = $diContainer->get('OdkFormService');
                        return new \Application\Controller\ReceiverController($odkFormService);
                    }
                },
                'Application\Controller\ReceiverSpiV6Controller' => new class {
                    public function __invoke($diContainer)
                    {
                        $odkFormService = $diContainer->get('OdkFormService');
                        return new \Application\Controller\ReceiverSpiV6Controller($odkFormService);
                    }
                },
                'Application\Controller\LoginController' => new class {
                    public function __invoke($diContainer)
                    {
                        $userService = $diContainer->get('UserService');
                        return new \Application\Controller\LoginController($userService);
                    }
                },
                'Application\Controller\SpiV3Controller' => new class {
                    public function __invoke($diContainer)
                    {
                        $commonService = $diContainer->get('CommonService');
                        $odkFormService = $diContainer->get('OdkFormService');
                        return new \Application\Controller\SpiV3Controller($odkFormService, $commonService);
                    }
                },
                'Application\Controller\SpiV6Controller' => new class {
                    public function __invoke($diContainer)
                    {
                        $commonService = $diContainer->get('CommonService');
                        $odkFormService = $diContainer->get('OdkFormService');
                        $provinceService = $diContainer->get('ProvinceService');
                        return new \Application\Controller\SpiV6Controller($odkFormService, $commonService, $provinceService);
                    }
                },
                'Application\Controller\FacilityController' => new class {
                    public function __invoke($diContainer)
                    {
                        $facilityService = $diContainer->get('FacilityService');
                        $odkFormService = $diContainer->get('OdkFormService');
                        $provinceService = $diContainer->get('ProvinceService');
                        return new \Application\Controller\FacilityController($facilityService, $odkFormService, $provinceService);
                    }
                },
            ],
        ];
    }


    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }


    public function getServiceConfig()
    {
        return array(
            // Pages moved to Slim have no Laminas route. This adds them to
            // the router, so links and redirects to them still assemble.
            'delegators' => [
                \Laminas\Router\Http\TreeRouteStack::class => [\App\Routing\SlimRoutesDelegator::class],
            ],
            'factories' => array(
                'Logger' => new class
                {
                    public function __invoke($diContainer)
                    {
                        return new Logger();
                    }
                },
                'AppAcl' => new class
                {

                    public function __invoke($diContainer)
                    {
                        $resourcesTable = $diContainer->get('ResourcesTable');
                        $rolesTable = $diContainer->get('RolesTable');
                        // return new Acl($resourcesTable->fetchAllResourceMap(), $rolesTable->fecthAllActiveRoles());
                        return new Acl($resourcesTable->fetchAllResourceMap(), $rolesTable->fecthAllActiveRoles(), $rolesTable->getAllPrivilegesMap(), $rolesTable->getAllPrivileges());
                    }
                },
                'SpiFormVer3Table' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new SpiFormVer3Table($dbAdapter);
                    }
                },
                'SpiFormVer6Table' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new SpiFormVer6Table($dbAdapter);
                    }
                },
                'UsersTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new UsersTable($dbAdapter);
                    }
                },
                'SpiFormLabelsTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new SpiFormLabelsTable($dbAdapter);
                    }
                },
                'SpiForm6LabelsTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new SpiForm6LabelsTable($dbAdapter);
                    }
                },
                'SpiRtFacilitiesTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new SpiRtFacilitiesTable($dbAdapter);
                    }
                },
                'RolesTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new RolesTable($dbAdapter);
                    }
                },
                'UserRoleMapTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new UserRoleMapTable($dbAdapter);
                    }
                },
                'GlobalTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new GlobalTable($dbAdapter);
                    }
                },
                'EventLogTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new EventLogTable($dbAdapter);
                    }
                },
                'ApiLogsTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new ApiLogsTable($dbAdapter);
                    }
                },
                'ApiLogger' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new ApiLogger($dbAdapter);
                    }
                },
                'ResourcesTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new ResourcesTable($dbAdapter);
                    }
                },
                'TempMailTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new TempMailTable($dbAdapter);
                    }
                },
                'UserTokenMapTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new UserTokenMapTable($dbAdapter);
                    }
                },
                'AuditMailTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new AuditMailTable($dbAdapter);
                    }
                },
                'SpiFormVer3DownloadTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new SpiFormVer3DownloadTable($dbAdapter);
                    }
                },
                'SpiFormVer6DownloadTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new SpiFormVer6DownloadTable($dbAdapter);
                    }
                },
                'SpiFormVer3DuplicateTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new SpiFormVer3DuplicateTable($dbAdapter);
                    }
                },
                'SpiFormVer6DuplicateTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new SpiFormVer6DuplicateTable($dbAdapter);
                    }
                },
                'SpiFormVer3TempTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new SpiFormVer3TempTable($dbAdapter);
                    }
                },
                'CountriesTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new CountriesTable($dbAdapter);
                    }
                },
                'UserCountryMapTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new UserCountryMapTable($dbAdapter);
                    }
                },
                'AuditSpiFormV3Table' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new AuditSpiFormV3Table($dbAdapter);
                    }
                },
                'AuditSpiFormV6Table' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new AuditSpiFormV6Table($dbAdapter);
                    }
                },
                'GeographicalDivisionsTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new GeographicalDivisionsTable($dbAdapter);
                    }
                },
                'UserLocationMapTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new UserLocationMapTable($dbAdapter);
                    }
                },
                'OdkFormService' => new class
                {
                    public function __invoke($diContainer)
                    {
                        return new OdkFormService($diContainer);
                    }
                },
                'CommonService' => new class
                {
                    public function __invoke($diContainer)
                    {
                        return new CommonService($diContainer);
                    }
                },
                'UserService' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $usersTable = $diContainer->get('UsersTable');
                        return new UserService($diContainer, $usersTable);
                    }
                },
                'FacilityService' => new class
                {
                    public function __invoke($diContainer)
                    {
                        return new FacilityService($diContainer);
                    }
                },
                'RoleService' => new class
                {
                    public function __invoke($diContainer)
                    {
                        return new RoleService($diContainer);
                    }
                },
                'EventService' => new class
                {
                    public function __invoke($diContainer)
                    {
                        return new EventService($diContainer);
                    }
                },
                'AuditTrailService' => new class
                {
                    public function __invoke($diContainer)
                    {
                        return new AuditTrailService($diContainer);
                    }
                },
                'TcpdfExtends' => new class
                {
                    public function __invoke($diContainer)
                    {
                        return new TcpdfExtends($diContainer);
                    }
                },
                'ProvinceService' => new class
                {
                    public function __invoke($diContainer)
                    {
                        return new ProvinceService($diContainer);
                    }
                },
            ),

        );
    }


    public function getViewHelperConfig()
    {
        return [
            'invokables' => [
                'humanReadableDateFormat' => 'Application\View\Helper\HumanReadableDateFormat',
            ],
            'factories' => [
                'GlobalConfigHelper' => new class {
                    public function __invoke($diContainer)
                    {
                        $globalTable = $diContainer->get('GlobalTable');
                        return new GlobalConfigHelper($globalTable);
                    }
                },
                'GetCountryDetailsByIdHelper' => new class {
                    public function __invoke($diContainer)
                    {
                        $countriesTable = $diContainer->get('CountriesTable');
                        return new GetCountryDetailsByIdHelper($countriesTable);
                    }
                },
                // laminas-i18n used to register this one. Same name and
                // signature, so the ~2900 translate() calls in the views are
                // untouched.
                'translate' => new class {
                    public function __invoke($diContainer)
                    {
                        return new Translate($diContainer->get('translator'));
                    }
                }
            ]


        ];
    }


    public function getAutoloaderConfig()
    {
        return array(
            'Laminas\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}

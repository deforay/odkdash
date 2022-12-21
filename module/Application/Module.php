<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Application\Model\SpiFormVer3Table;
use Application\Model\SpiFormVer5Table;
use Application\Model\SpiFormVer6Table;
use Application\Model\SpiFormVer3DuplicateTable;
use Application\Model\SpiFormVer5DuplicateTable;
use Application\Model\SpiFormVer6DuplicateTable;
use Application\Model\UsersTable;
use Application\Model\SpiFormLabelsTable;
use Application\Model\SpiForm5LabelsTable;
use Application\Model\SpiForm6LabelsTable;
use Application\Model\SpiRtFacilitiesTable;
use Application\Model\RolesTable;
use Application\Model\UserLoginHistoryTable;
use Application\Model\UserRoleMapTable;
use Application\Model\GlobalTable;
use Application\Model\EventLogTable;
use Application\Model\ResourcesTable;
use Application\Model\TempMailTable;
use Application\Model\UserTokenMapTable;
use Application\Model\AuditMailTable;
use Application\Model\SpiFormVer3DownloadTable;
use Application\Model\SpiFormVer5DownloadTable;
use Application\Model\SpiFormVer6DownloadTable;
use Application\Model\SpiFormVer3TempTable;
use Application\Model\CountriesTable;
use Application\Model\UserCountryMapTable;


use Application\Service\OdkFormService;
use Application\Service\UserService;
use Application\Service\FacilityService;
use Application\Service\CommonService;
use Application\Service\RoleService;
use Application\Service\UserLoginHistoryService;
use Application\Service\TcpdfExtends;

use Application\Model\Acl;
use Laminas\Mvc\ModuleRouteListener;
use Laminas\Mvc\MvcEvent;

use Laminas\Session\Container;
use Laminas\View\Model\ViewModel;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $languagecontainer = new Container('language');

        if (php_sapi_name() != 'cli') {
            $eventManager->attach('dispatch', array($this, 'preSetter'), 100);
            //$eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'dispatchError'), -999);
        }

        // //Attach render errors
        // $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, function ($e) {
        //     if ($e->getParam('exception')) {
        //         $this->exception($e->getParam('exception')); //Custom error render function.
        //     }
        // });
        // //Attach dispatch errors
        // $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, function ($e) {
        //     if ($e->getParam('exception')) {
        //         $this->exception($e->getParam('exception')); //Custom error render function.
        //     }
        // });

        $this->initTranslator($e);
    }


    // public function exception($e) {
    //     echo "<span style='font-family: courier new; padding: 2px 5px; background:red; color: white;'> " . $e->getMessage() . '</span><br/>' ;
    //     echo "<pre>" . $e->getTraceAsString() . '</pre>' ;   
    //     die;
    // }

    public function preSetter(MvcEvent $e)
    {


        $session = new Container('credo');
        $sm = $e->getApplication()->getServiceManager();
        $commonService = $sm->get('CommonService');
        $config = $commonService->getGlobalConfigDetails();
        $session->countryName = $config['country-name'];

        if (
            $e->getRouteMatch()->getParam('controller') != 'Application\Controller\Login'
            && $e->getRouteMatch()->getParam('controller') != 'Application\Controller\Index'
            && $e->getRouteMatch()->getParam('controller') != 'Application\Controller\Receiver'
            && $e->getRouteMatch()->getParam('controller') != 'Application\Controller\ReceiverSpiV5'
            && $e->getRouteMatch()->getParam('controller') != 'Application\Controller\ReceiverSpiV6'
        ) {
            if (!isset($session->userId) || $session->userId == "") {
                if ($e->getRequest()->isXmlHttpRequest()) {
                    return;
                }
                $url = $e->getRouter()->assemble(array(), array('name' => 'login'));
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
                $e->getApplication()->getEventManager()->attach(MvcEvent::EVENT_ROUTE, $stopCallBack, -10000);
                return $response;
            } else {
                $sm = $e->getApplication()->getServiceManager();
                $viewModel = $e->getApplication()->getMvcEvent()->getViewModel();
                $acl = $sm->get('AppAcl');
                $viewModel->acl = $acl;
                $session->acl = serialize($acl);

                $params = $e->getRouteMatch()->getParams();
                $resource = $params['controller'];
                $privilege = $params['action'];

                $role = $session->roleCode;
                
                //\Zend\Debug\Debug::dump($role);
                
                //\Zend\Debug\Debug::dump($acl->isAllowed($role, $resource, $privilege));
                //\Zend\Debug\Debug::dump($privilege);
                //die;
                //if($e->getRequest()->isXmlHttpRequest() || $role == 'SA') {
                if ($e->getRequest()->isXmlHttpRequest()) {
                    return;
                } else {
                        
                    if (!$acl->hasResource($resource) || (!$acl->isAllowed($role, $resource, $privilege))) {
                            $e->setError('ACL_ACCESS_DENIED')->setParam('route', $e->getRouteMatch());
                            $e->getApplication()->getEventManager()->trigger('dispatch.error', $e);
                    }
                }
            }
        } else {
            if (isset($session->userId)) {
                $sm = $e->getApplication()->getServiceManager();
                $viewModel = $e->getApplication()->getMvcEvent()->getViewModel();
                $acl = $sm->get('AppAcl');
                $viewModel->acl = $acl;
                $session->acl = serialize($acl);
            }
        }
    }

    protected function initTranslator(MvcEvent $event)
    {

        $serviceManager = $event->getApplication()->getServiceManager();
        $translator = $serviceManager->get('translator');
        $config = ($serviceManager->get('Config'));
        $translator->setLocale($config['settings']['locale'])
            ->setFallbackLocale('en_US');
    }

    public function getControllerConfig()
    {
        return array(
            'factories' => array(
                'Application\Controller\Index' => function ($sm) {
                    $commonService = $sm->getServiceLocator()->get('CommonService');
                    $odkFormService = $sm->getServiceLocator()->get('OdkFormService');
                    return new \Application\Controller\IndexController($odkFormService, $commonService);
                },
                'Application\Controller\Receiver' => function ($sm) {
                    $odkFormService = $sm->getServiceLocator()->get('OdkFormService');
                    return new \Application\Controller\ReceiverController($odkFormService);
                },
                'Application\Controller\ReceiverSpiV5' => function ($sm) {
                    $odkFormService = $sm->getServiceLocator()->get('OdkFormService');
                    return new \Application\Controller\ReceiverSpiV5Controller($odkFormService);
                },
                'Application\Controller\ReceiverSpiV6' => function ($sm) {
                    $odkFormService = $sm->getServiceLocator()->get('OdkFormService');
                    return new \Application\Controller\ReceiverSpiV6Controller($odkFormService);
                },
                'Application\Controller\Login' => function ($sm) {
                    $userService = $sm->getServiceLocator()->get('UserService');
                    return new \Application\Controller\LoginController($userService);
                },
                'Application\Controller\SpiV3Reports' => function ($sm) {
                    $odkFormService = $sm->getServiceLocator()->get('OdkFormService');
                    return new \Application\Controller\SpiV3ReportsController($odkFormService);
                },
                'Application\Controller\SpiV5Reports' => function ($sm) {
                    $odkFormService = $sm->getServiceLocator()->get('OdkFormService');
                    return new \Application\Controller\SpiV5ReportsController($odkFormService);
                },
                'Application\Controller\SpiV6Reports' => function ($sm) {
                    $odkFormService = $sm->getServiceLocator()->get('OdkFormService');
                    return new \Application\Controller\SpiV6ReportsController($odkFormService);
                },
                'Application\Controller\SpiV3' => function ($sm) {
                    $commonService = $sm->getServiceLocator()->get('CommonService');
                    $odkFormService = $sm->getServiceLocator()->get('OdkFormService');
                    return new \Application\Controller\SpiV3Controller($odkFormService, $commonService);
                },
                'Application\Controller\SpiV5' => function ($sm) {
                    $commonService = $sm->getServiceLocator()->get('CommonService');
                    $odkFormService = $sm->getServiceLocator()->get('OdkFormService');
                    return new \Application\Controller\SpiV5Controller($odkFormService, $commonService);
                },
                'Application\Controller\SpiV6' => function ($sm) {
                    $commonService = $sm->getServiceLocator()->get('CommonService');
                    $odkFormService = $sm->getServiceLocator()->get('OdkFormService');
                    return new \Application\Controller\SpiV6Controller($odkFormService, $commonService);
                },
                'Application\Controller\Common' => function ($sm) {
                    $commonService = $sm->getServiceLocator()->get('CommonService');
                    $odkFormService = $sm->getServiceLocator()->get('OdkFormService');
                    return new \Application\Controller\CommonController($commonService, $odkFormService);
                },
                'Application\Controller\Cron' => function ($sm) {
                    $commonService = $sm->getServiceLocator()->get('CommonService');
                    $odkFormService = $sm->getServiceLocator()->get('OdkFormService');
                    return new \Application\Controller\CronController($commonService, $odkFormService);
                },
                'Application\Controller\Config' => function ($sm) {
                    $commonService = $sm->getServiceLocator()->get('CommonService');
                    return new \Application\Controller\ConfigController($commonService);
                },
                'Application\Controller\Facility' => function ($sm) {
                    $facilityService = $sm->getServiceLocator()->get('FacilityService');
                    $odkFormService = $sm->getServiceLocator()->get('OdkFormService');
                    return new \Application\Controller\FacilityController($facilityService, $odkFormService);
                },
                'Application\Controller\Users' => function ($sm) {
                    $userService = $sm->getServiceLocator()->get('UserService');
                    $roleService = $sm->getServiceLocator()->get('RoleService');
                    $odkFormService = $sm->getServiceLocator()->get('OdkFormService');
                    $commonService = $sm->getServiceLocator()->get('CommonService');
                    return new \Application\Controller\UsersController($userService, $roleService, $odkFormService, $commonService);
                },
                'Application\Controller\Email' => function ($sm) {
                    $facilityService = $sm->getServiceLocator()->get('FacilityService');
                    $commonService = $sm->getServiceLocator()->get('CommonService');
                    $odkFormService = $sm->getServiceLocator()->get('OdkFormService');
                    return new \Application\Controller\EmailController($facilityService, $odkFormService, $commonService);
                },
                'Application\Controller\Roles' => function ($sm) {
                    $roleService = $sm->getServiceLocator()->get('RoleService');
                    return new \Application\Controller\RolesController($roleService);
                },
                'Application\Controller\UserLoginHistory' => function ($sm) {
                    $userLoginHistoryService = $sm->getServiceLocator()->get('UserLoginHistoryService');
                    return new \Application\Controller\UserLoginHistoryController($userLoginHistoryService);
                },
                'Application\Controller\Dashboard' => function ($sm) {
                    $odkFormService = $sm->getServiceLocator()->get('OdkFormService');
                    return new \Application\Controller\DashboardController($odkFormService);
                },
                'Application\Controller\DashboardV5' => function ($sm) {
                    $odkFormService = $sm->getServiceLocator()->get('OdkFormService');
                    return new \Application\Controller\DashboardV5Controller($odkFormService);
                },
                'Application\Controller\DashboardV6' => function ($sm) {
                    $odkFormService = $sm->getServiceLocator()->get('OdkFormService');
                    return new \Application\Controller\DashboardV6Controller($odkFormService);
                },
            ),
        );
    }


    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }


    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'AppAcl' => function ($sm) {
                    $resourcesTable = $sm->get('ResourcesTable');
                    $rolesTable = $sm->get('RolesTable');
                    return new Acl($resourcesTable->fetchAllResourceMap(), $rolesTable->fecthAllActiveRoles());
                },
                'SpiFormVer3Table' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $table = new SpiFormVer3Table($dbAdapter);
                    return $table;
                },
                'SpiFormVer5Table' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $table = new SpiFormVer5Table($dbAdapter);
                    return $table;
                },
                'SpiFormVer6Table' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $table = new SpiFormVer6Table($dbAdapter);
                    return $table;
                },
                'UsersTable' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $table = new UsersTable($dbAdapter);
                    return $table;
                },
                'SpiFormLabelsTable' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $table = new SpiFormLabelsTable($dbAdapter);
                    return $table;
                },
                'SpiForm5LabelsTable' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $table = new SpiForm5LabelsTable($dbAdapter);
                    return $table;
                },
                'SpiForm6LabelsTable' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $table = new SpiForm6LabelsTable($dbAdapter);
                    return $table;
                },
                'SpiRtFacilitiesTable' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $table = new SpiRtFacilitiesTable($dbAdapter);
                    return $table;
                },
                'RolesTable' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $table = new RolesTable($dbAdapter);
                    return $table;
                },
                'UserLoginHistoryTable' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $table = new UserLoginHistoryTable($dbAdapter);
                    return $table;
                },
                'UserRoleMapTable' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $table = new UserRoleMapTable($dbAdapter);
                    return $table;
                },
                'GlobalTable' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $table = new GlobalTable($dbAdapter);
                    return $table;
                },
                'EventLogTable' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $table = new EventLogTable($dbAdapter);
                    return $table;
                },
                'ResourcesTable' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $table = new ResourcesTable($dbAdapter);
                    return $table;
                },
                'TempMailTable' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $table = new TempMailTable($dbAdapter);
                    return $table;
                }, 'UserTokenMapTable' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $table = new UserTokenMapTable($dbAdapter);
                    return $table;
                }, 'AuditMailTable' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $table = new AuditMailTable($dbAdapter);
                    return $table;
                }, 'SpiFormVer3DownloadTable' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $table = new SpiFormVer3DownloadTable($dbAdapter);
                    return $table;
                },
                'SpiFormVer5DownloadTable' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $table = new SpiFormVer5DownloadTable($dbAdapter);
                    return $table;
                },
                'SpiFormVer6DownloadTable' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $table = new SpiFormVer6DownloadTable($dbAdapter);
                    return $table;
                },
                'SpiFormVer3DuplicateTable' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $table = new SpiFormVer3DuplicateTable($dbAdapter);
                    return $table;
                },
                'SpiFormVer5DuplicateTable' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $table = new SpiFormVer5DuplicateTable($dbAdapter);
                    return $table;
                },
                'SpiFormVer6DuplicateTable' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $table = new SpiFormVer6DuplicateTable($dbAdapter);
                    return $table;
                },
                'SpiFormVer3TempTable' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $table = new SpiFormVer3TempTable($dbAdapter);
                    return $table;
                },
                'CountriesTable' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $table = new CountriesTable($dbAdapter);
                    return $table;
                },
                'UserCountryMapTable' => function ($sm) {
                    $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
                    $table = new UserCountryMapTable($dbAdapter);
                    return $table;
                },

                'OdkFormService' => function ($sm) {
                    return new OdkFormService($sm);
                },
                'CommonService' => function ($sm) {
                    return new CommonService($sm);
                },
                'UserService' => function ($sm) {
                    return new UserService($sm);
                },
                'FacilityService' => function ($sm) {
                    return new FacilityService($sm);
                },
                'CommonService' => function ($sm) {
                    return new CommonService($sm);
                },
                'RoleService' => function ($sm) {
                    return new RoleService($sm);
                },
                'UserLoginHistoryService' => function ($sm) {
                    return new UserLoginHistoryService($sm);
                },
                'TcpdfExtends' => function ($sm) {
                    return new TcpdfExtends($sm);
                }
            ),

        );
    }


    public function getViewHelperConfig()
    {
        return array(
            'invokables' => array(
                'humanDateFormat' => 'Application\View\Helper\HumanDateFormat',
            ), 'factories' => array(
                'GlobalConfigHelper' => function ($sm) {
                    $globalTable = $sm->get('GlobalTable');
                    return new \Application\View\Helper\GlobalConfigHelper($globalTable);
                },
                'GetCountryDetailsByIdHelper' => function ($sm) {
                    $countriesTable = $sm->get('CountriesTable');
                    return new \Application\View\Helper\GetCountryDetailsByIdHelper($countriesTable);
                },

            )
        );
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

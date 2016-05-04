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
use Application\Model\UsersTable;
use Application\Model\SpiFormLabelsTable;
use Application\Model\SpiRtFacilitiesTable;
use Application\Model\RolesTable;
use Application\Model\UserRoleMapTable;
use Application\Model\GlobalTable;
use Application\Model\EventLogTable;

use Application\Service\OdkFormService;
use Application\Service\UserService;
use Application\Service\FacilityService;
use Application\Service\CommonService;
use Application\Service\RoleService;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use Zend\Session\Container;
use Zend\View\Model\ViewModel;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        if (php_sapi_name() != 'cli') {
            $eventManager->attach('dispatch', array($this, 'preSetter'), 100);
            //$eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'dispatchError'), -999);
        }        
        
    }
    
    
    public function preSetter(MvcEvent $e) {
	if ($e->getRouteMatch()->getParam('controller') != 'Application\Controller\Login'
        && $e->getRouteMatch()->getParam('controller') != 'Application\Controller\Receiver'
        ) {
            $session = new Container('credo');
            if (!isset($session->userId) || $session->userId == "") {
                $url = $e->getRouter()->assemble(array(), array('name' => 'login'));
                $response = $e->getResponse();
                $response->getHeaders()->addHeaderLine('Location', $url);
                $response->setStatusCode(302);
                $response->sendHeaders();

                // To avoid additional processing
                // we can attach a listener for Event Route with a high priority
                $stopCallBack = function($event) use ($response) {
                                    $event->stopPropagation();
                                    return $response;
                                };
                //Attach the "break" as a listener with a high priority
                $e->getApplication()->getEventManager()->attach(MvcEvent::EVENT_ROUTE, $stopCallBack, -10000);
                return $response;
            }
        }
    }
    

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    
    public function getServiceConfig() {
        return array(
            'factories' => array(
                'SpiFormVer3Table' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new SpiFormVer3Table($dbAdapter);
                    return $table;
                },
                'UsersTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new UsersTable($dbAdapter);
                    return $table;
                },
                'SpiFormLabelsTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new SpiFormLabelsTable($dbAdapter);
                    return $table;
                },
				'SpiRtFacilitiesTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new SpiRtFacilitiesTable($dbAdapter);
                    return $table;
                },
				'RolesTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new RolesTable($dbAdapter);
                    return $table;
                },
				'UserRoleMapTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new UserRoleMapTable($dbAdapter);
                    return $table;
                },
				'GlobalTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new GlobalTable($dbAdapter);
                    return $table;
                },
				'EventLogTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new EventLogTable($dbAdapter);
                    return $table;
                },
				
                'OdkFormService' => function($sm) {
                    return new OdkFormService($sm);
                },
                'CommonService' => function($sm) {
                    return new CommonService($sm);
                },
                'UserService' => function($sm) {
                    return new UserService($sm);
                },
				'FacilityService' => function($sm) {
                    return new FacilityService($sm);
                },
				'CommonService' => function($sm) {
                    return new CommonService($sm);
                },
				'RoleService' => function($sm) {
                    return new RoleService($sm);
                }
            ),
          
        );
    }
    
	
    public function getViewHelperConfig(){
        return array(
           'invokables' => array(
              'humanDateFormat' => 'Application\View\Helper\HumanDateFormat',
           ),
        );
    }	

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}

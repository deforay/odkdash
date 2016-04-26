<?php

namespace Application\Service;

use Zend\Session\Container;

class RoleService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }

    public function addRoles($params) {
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $rolesDb = $this->sm->get('RolesTable');
            $rolesResult = $rolesDb->addRolesDetails($params);
            if ($rolesResult > 0) {
                $adapter->commit();
                $container = new Container('alert');
                $container->alertMsg = 'Roles added successfully';
            }
        } catch (Exception $exc) {
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    public function updateRoles($params) {
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $rolesDb = $this->sm->get('RolesTable');
            $rolesResult = $rolesDb->updateRolesDetails($params);
            if ($rolesResult > 0) {
                $adapter->commit();
                $container = new Container('alert');
                $container->alertMsg = 'Roles updated successfully';
            }
            
        } catch (Exception $exc) {
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getAllRoles($params) {
        $rolesDb = $this->sm->get('RolesTable');
        //$acl = $this->sm->get('AppAcl');
        return $rolesDb->fetchAllRoles($params);
    }
    
    public function getRole($id) {
        $rolesDb = $this->sm->get('RolesTable');
        return $rolesDb->getRolesDetails($id);
    }
    
    public function getAllActiveRoles(){
        $rolesDb = $this->sm->get('RolesTable');
        return $rolesDb->fecthAllActiveRoles();
    }
}

?>

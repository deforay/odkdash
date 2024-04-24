<?php

namespace Application\Service;

use Laminas\Session\Container;

class ProvinceService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }

    public function addProvince($params) {
        $adapter = $this->sm->get('Laminas\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            
            $db = $this->sm->get('GeographicalDivisionsTable');
            $result = $db->addProvinceDetails($params);
            if ($result > 0) {
                $adapter->commit();

                //<-- Event log
                $subject = $result;
                $eventType = 'province-add';
                $action = 'added a new province '.$params['provinceName'];
                $resourceName = 'Province';
                $eventLogDb = $this->sm->get('EventLogTable');
                $eventLogDb->addEventLog($subject,$eventType,$action,$resourceName);
                //-------->
                $container = new Container('alert');
                $container->alertMsg = 'Province added successfully';
            }
        } catch (\Exception $exc) {
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    public function updateProvince($params) {
        $adapter = $this->sm->get('Laminas\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $db = $this->sm->get('GeographicalDivisionsTable');
            $result = $db->updateProvinceDetails($params);
            if ($result > 0) {
                
                $adapter->commit();
                $subject = $result;
                //<-- Event log
                $eventType = 'province-update';
                $action = 'updated a province '.$params['provinceName'];
                $resourceName = 'Province';
                $eventLogDb = $this->sm->get('EventLogTable');
                $eventLogDb->addEventLog($subject,$eventType,$action,$resourceName);
                //-------->
                $container = new Container('alert');
                $container->alertMsg = 'Province updated successfully';
            }
            
        } catch (\Exception $exc) {
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    public function getAllProvinceDetails($params) {
        $db = $this->sm->get('GeographicalDivisionsTable');
        $acl = $this->sm->get('AppAcl');
        return $db->fetchAllProvinceDetails($params,$acl);
    }
    
    public function getProvince($id) {
        $db = $this->sm->get('GeographicalDivisionsTable');
        return $db->getProvinceDetails($id);
    }

    public function getAllActiveProvinces(){
        $db = $this->sm->get('GeographicalDivisionsTable');
        return $db->fetchAllActiveProvinces();
    }

    public function addDistrict($params) {
        $adapter = $this->sm->get('Laminas\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $db = $this->sm->get('GeographicalDivisionsTable');
            $result = $db->addDistrictDetails($params);
            if ($result > 0) {
                $adapter->commit();
                //<-- Event log
                $subject = $result;
                $eventType = 'district-add';
                $action = 'added a new district '.$params['districtName'];
                $resourceName = 'District';
                $eventLogDb = $this->sm->get('EventLogTable');
                $eventLogDb->addEventLog($subject,$eventType,$action,$resourceName);
                //-------->
                $container = new Container('alert');
                $container->alertMsg = 'District added successfully';
            }
        } catch (\Exception $exc) {
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    public function updateDistrict($params) {
        $adapter = $this->sm->get('Laminas\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $db = $this->sm->get('GeographicalDivisionsTable');
            $result = $db->updateDistrictDetails($params);
            if ($result > 0) {
                $adapter->commit();
                $subject = $result;
                //<-- Event log
                $eventType = 'district-update';
                $action = 'updated a district '.$params['districtName'];
                $resourceName = 'District';
                $eventLogDb = $this->sm->get('EventLogTable');
                $eventLogDb->addEventLog($subject,$eventType,$action,$resourceName);
                //-------->
                $container = new Container('alert');
                $container->alertMsg = 'District updated successfully';
            }
            
        } catch (\Exception $exc) {
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    public function getAllDistrictDetails($params) {
        $db = $this->sm->get('GeographicalDivisionsTable');
        $acl = $this->sm->get('AppAcl');
        return $db->fetchAllDistrictDetails($params,$acl);
    }

    public function getAllActiveDistricts(){
        $db = $this->sm->get('GeographicalDivisionsTable');
        return $db->fetchAllActiveDistricts();
    }

    public function getAllDistrictByProvince($provinceId){
        $db = $this->sm->get('GeographicalDivisionsTable');
        return $db->fetchAllDistrictByProvince($provinceId);
    }
}

?>

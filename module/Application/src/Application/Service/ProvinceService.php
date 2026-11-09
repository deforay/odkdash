<?php

namespace Application\Service;

use Application\Session\Container;
use Doctrine\DBAL\Connection;

class ProvinceService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function addProvince($params) {
        $db = $this->sm->get(Connection::class);
        $db->beginTransaction();
        try {
            
            $geoTable = $this->sm->get('GeographicalDivisionsTable');
            $result = $geoTable->addProvinceDetails($params);
            if ($result > 0) {
                $db->commit();

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
            } else {
                $db->rollBack();
            }
        } catch (\Exception $exc) {
            if ($db->isTransactionActive()) {
                $db->rollBack();
            }
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    public function updateProvince($params) {
        $db = $this->sm->get(Connection::class);
        $db->beginTransaction();
        try {
            $geoTable = $this->sm->get('GeographicalDivisionsTable');
            $result = $geoTable->updateProvinceDetails($params);
            if ($result > 0) {
                
                $db->commit();
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
            } else {
                $db->rollBack();
            }
        } catch (\Exception $exc) {
            if ($db->isTransactionActive()) {
                $db->rollBack();
            }
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

    public function getAllMappedLocations(){
        $db = $this->sm->get('GeographicalDivisionsTable');
        return $db->fetchAllMappedLocations();
    }

    public function getAllCountries()
    {
        $db = $this->sm->get('CountriesTable');
        return $db->fetchAllCountries();
    }

    public function getAllMapedCountries()
    {
        $db = $this->sm->get('CountriesTable');
        return $db->fetchAllMapedCountries();
    }

    public function getAllProvinces()
    {
        $db = $this->sm->get('GeographicalDivisionsTable');
        return $db->fetchAllProvinces();
    }

    public function getAllDistricts()
    {
        $db = $this->sm->get('GeographicalDivisionsTable');
        return $db->fetchAllDistricts();
    }

    public function addDistrict($params) {
        $db = $this->sm->get(Connection::class);
        $db->beginTransaction();
        try {
            $geoTable = $this->sm->get('GeographicalDivisionsTable');
            $result = $geoTable->addDistrictDetails($params);
            if ($result > 0) {
                $db->commit();
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
            } else {
                $db->rollBack();
            }
        } catch (\Exception $exc) {
            if ($db->isTransactionActive()) {
                $db->rollBack();
            }
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    public function updateDistrict($params) {
        $db = $this->sm->get(Connection::class);
        $db->beginTransaction();
        try {
            $geoTable = $this->sm->get('GeographicalDivisionsTable');
            $result = $geoTable->updateDistrictDetails($params);
            if ($result > 0) {
                $db->commit();
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
            } else {
                $db->rollBack();
            }
        } catch (\Exception $exc) {
            if ($db->isTransactionActive()) {
                $db->rollBack();
            }
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

    public function getAllDistrictByProvinceAsMuliple($provinceId){
        $db = $this->sm->get('GeographicalDivisionsTable');
        return $db->fetchAllDistrictByProvinceAsMultiple($provinceId);
    }

    public function checkProvinceDistrict($params) {
        $db = $this->sm->get('GeographicalDivisionsTable');
        return $db->getProvinceDistrict($params);
    }
}

?>

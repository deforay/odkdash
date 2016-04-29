<?php

namespace Application\Service;

use Zend\Session\Container;

class OdkFormService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }

    
    public function saveSpiFormVer3($params) {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->saveData($params);
    }
    
    public function getPerformance($params = null) {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->getPerformance($params);
    }
   
    
    public function getPerformanceLast30Days($params = null) {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->getPerformanceLast30Days();
    }
   
    
    public function getPerformanceLast180Days($params = null) {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->getPerformanceLast180Days();
    }
    
    public function getAllSubmissions($sortOrder = 'DESC') {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->getAllSubmissions($sortOrder);
    }
    public function getAllSubmissionsDetails($params) {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchAllSubmissionsDetails($params);
    }
    
    public function getAllSubmissionsDatas($params) {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchAllSubmissionsDatas($params);
    }
    //get pending facility names
    public function getPendingFacilityNames()
    {
     $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchPendingFacilityNames();   
    }
    //merge all facility name
    public function mergeAllFacilityName()
    {
        $db = $this->sm->get('SpiRtFacilitiesTable');
        return $db->mergeAllFacilityName();  
    }
    
    public function getAuditRoundWiseData($params) {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->getAuditRoundWiseData($params);
    }
    
    public function getFormData($id) {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->getFormData($id);
    }
    
    public function getSpiV3FormLabels() {
        $db = $this->sm->get('SpiFormLabelsTable');
        return $db->getAllLabels();
    }
    
    public function approveFormStatus($params){
        //\Zend\Debug\Debug::dump(count($params['idList']));die;
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
            $db = $this->sm->get('SpiFormVer3Table');
            if(isset($params['idList']) && $params['idList']!='')
            {
                for($i=0;$i<count($params['idList']);$i++){
                $result = $db->updateFormStatus($params['idList'][$i],'approved');
                $facilityDb->addFacilityBasedOnForm($params['idList'][$i]);
                }
            }
            if(isset($params['id'])){
            $result = $db->updateFormStatus($params['id'],'approved');
            $facilityDb->addFacilityBasedOnForm($params['id']);
            }
            if ($result > 0) {
                $adapter->commit();
                return $result;
            }
        } catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getAllApprovedSubmissions($sortOrder = 'DESC') {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchAllApprovedSubmissions($sortOrder);
    }
    public function getAllApprovedSubmissionLocation($params) {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchAllApprovedSubmissionLocation($params);
    }
    
    public function getZeroQuestionCounts($params) {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->getZeroQuestionCounts($params);
    }
    
    public function updateSpiForm($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $db = $this->sm->get('SpiFormVer3Table');
            $result = $db->updateSpiFormDetails($params);
            if ($result > 0) {
                $adapter->commit();
                $container = new Container('alert');
                $container->alertMsg = 'Form details updated successfully';
                return $result;
            }
        } catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    //get all audit round no
    public function getSpiV3FormAuditNo()
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchSpiV3FormAuditNo();
    }
}
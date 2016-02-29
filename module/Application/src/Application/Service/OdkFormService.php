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
    
    public function getAllSubmissions($params = null) {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->getAllSubmissions();
    }
    
    public function getFormData($id) {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->getFormData($id);
    }
   
}

?>

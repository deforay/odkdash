<?php

namespace Application\Service;


class AuditTrailService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }
    
    public function getSpiV3Details($params) {
        $auditV3Db = $this->sm->get('AuditSpiFormV3Table');
        return $auditV3Db->fetchAllDetails($params);
    }

    public function getSpiV6Details($params) {
        $auditV6Db = $this->sm->get('AuditSpiFormV6Table');
        return $auditV6Db->fetchAllDetails($params);
    }
}

?>

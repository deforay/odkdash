<?php

namespace Application\Service;

use Laminas\Session\Container;

class UserLoginHistoryService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }
    
    public function getAllDetails($params) {
        $rolesDb = $this->sm->get('UserLoginHistoryTable');
        return $rolesDb->fetchAllDetails($params);
    }
}

?>

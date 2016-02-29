<?php

namespace Application\Service;

use Zend\Session\Container;

class UserService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }

    
    public function login($params) {
        $db = $this->sm->get('UsersTable');
        return $db->login($params);
    }
    
   
}
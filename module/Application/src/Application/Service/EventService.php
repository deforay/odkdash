<?php

namespace Application\Service;

use Laminas\Session\Container;

class EventService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }
    
    public function getAllDetails($params) {
        $eventLogDb = $this->sm->get('EventLogTable');
        return $eventLogDb->fetchAllDetails($params);
    }
}

?>

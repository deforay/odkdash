<?php

namespace Application\Service;


class EventService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }
    
    public function getFeed(array $params): array
    {
        return $this->sm->get('EventLogTable')->fetchFeed($params);
    }

    public function getEventTypes(): array
    {
        return $this->sm->get('EventLogTable')->fetchEventTypes();
    }
}

?>

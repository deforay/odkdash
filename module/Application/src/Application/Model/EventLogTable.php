<?php

namespace Application\Model;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Sql\Expression;
use Laminas\Session\Container;
use Application\Service\CommonService;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Countries
 *
 * @author amit
 */
class EventLogTable extends AbstractTableGateway
{

    protected $table = 'event_log';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function addEventLog($subject, $eventType, $action, $resourceName)
    {
        $logincontainer = new Container('credo');
        $actorId = $logincontainer->userId;
        $currentDateTime = \Application\Service\CommonService::getDateTime();

        $data = array(
            'actor' => $actorId,
            'subject' => $subject,
            'event_type' => $eventType,
            'action' => $action,
            'resource_name' => $resourceName,
            'date_time' => $currentDateTime
        );
        $id = $this->insert($data);
    }
}

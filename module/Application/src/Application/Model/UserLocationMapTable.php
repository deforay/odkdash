<?php

namespace Application\Model;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;
use Laminas\Db\TableGateway\AbstractTableGateway;


class UserLocationMapTable extends AbstractTableGateway
{

    protected $table = 'user_location_map';
    protected $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function fetchSelectedLocation($id)
    {
        return $this->select(array('user_id' => $id))->toArray();
    }
}

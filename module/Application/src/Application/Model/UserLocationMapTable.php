<?php

namespace Application\Model;

use Laminas\Db\Sql\Sql;
use Laminas\Db\Adapter\Adapter;
use Application\Model\BaseTableGateway;


class UserLocationMapTable extends BaseTableGateway
{

    protected $table = 'user_location_map';
    protected $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function fetchSelectedLocation($id)
    {
        return $this->selectOne(['user_id' => $id]);
    }
}

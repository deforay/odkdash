<?php

namespace Application\Model;

use Application\Session\Container;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;
use Laminas\Db\TableGateway\AbstractTableGateway;




class UserTokenMapTable extends AbstractTableGateway
{

    protected $table = 'user_token_map';
    protected $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }
}

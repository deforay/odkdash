<?php

namespace Application\Model;

use Laminas\Db\Sql\Sql;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\TableGateway\AbstractTableGateway;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


class ResourcesTable extends AbstractTableGateway
{

    protected $table = 'resources';
    protected $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function fetchAllResourceMap()
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $resourceQuery = $sql->select()->from('resources')
            ->order('display_name');
        $resourceQueryStr = $sql->buildSqlString($resourceQuery);
        return $dbAdapter->query($resourceQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    }
}

<?php

namespace Application\Model;

use Doctrine\DBAL\Connection;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;

class ResourcesTable
{
    public function __construct(
        private readonly Adapter $adapter,
        private readonly Connection $db,
    ) {
    }

    public function fetchAllResourceMap()
    {
        $sql = new Sql($this->adapter);
        $resourceQuery = $sql->select()->from('resources')
            ->order('display_name');

        return $this->db->fetchAllAssociative($sql->buildSqlString($resourceQuery));
    }
}

<?php

namespace Application\Model;

use Doctrine\DBAL\Connection;

class ResourcesTable
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function fetchAllResourceMap()
    {
        return $this->db->fetchAllAssociative('SELECT * FROM resources ORDER BY display_name ASC');
    }
}

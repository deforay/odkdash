<?php

namespace Application\Model;

use App\Db\Dbal;
use Doctrine\DBAL\Connection;
use Laminas\Db\Adapter\Adapter;

class UserLocationMapTable
{
    private readonly Connection $db;

    public function __construct(Adapter $adapter)
    {
        $this->db = Dbal::connection($adapter);
    }

    /** Every location the user is mapped to. */
    public function fetchSelectedLocation($id): array
    {
        return $this->db->fetchAllAssociative('SELECT * FROM user_location_map WHERE user_id = ? ORDER BY id', [$id]);
    }
}

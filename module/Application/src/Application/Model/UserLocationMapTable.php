<?php

namespace Application\Model;

use Doctrine\DBAL\Connection;

class UserLocationMapTable
{
    private readonly Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /** Every location the user is mapped to. */
    public function fetchSelectedLocation($id): array
    {
        return $this->db->fetchAllAssociative('SELECT * FROM user_location_map WHERE user_id = ? ORDER BY id', [$id]);
    }
}

<?php

namespace Application\Model;

use App\Db\Dbal;
use Application\Session\Container;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Laminas\Db\Adapter\Adapter;

class CountriesTable
{
    private readonly Connection $db;

    public function __construct(Adapter $adapter)
    {
        $this->db = Dbal::connection($adapter);
    }

    public function fetchAllCountries()
    {
        return $this->db->fetchAllAssociative("SELECT * FROM countries WHERE status = 'active'");
    }

    public function fetchMapedCountries()
    {
        $response = [];
        foreach ($this->fetchCountries(activeOnly: true) as $row) {
            $response[$row['country_id']] = $row;
        }

        return $response;
    }

    public function fetchAllMapedCountries()
    {
        return $this->fetchCountries(activeOnly: false);
    }

    /** The countries the signed-in user is mapped to, or every country. */
    private function fetchCountries(bool $activeOnly): array
    {
        $where = $activeOnly ? ["status = 'active'"] : [];
        $params = [];
        $types = [];

        $loginContainer = new Container('credo');
        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType == 'country') {
            $where[] = 'country_id IN (?)';
            $params[] = array_values($loginContainer->userMappedIds);
            $types[] = ArrayParameterType::INTEGER;
        }

        $sql = 'SELECT * FROM countries' . ($where !== [] ? ' WHERE ' . implode(' AND ', $where) : '');

        return $this->db->fetchAllAssociative($sql, $params, $types);
    }
}

<?php

namespace Application\Model;

use App\Db\Dbal;
use Doctrine\DBAL\Connection;
use Laminas\Db\Adapter\Adapter;

/** V6 audits removed as duplicates, kept in spi_form_v_6_duplicate. */
class SpiFormVer6DuplicateTable
{
    private readonly Connection $db;

    public function __construct(Adapter $adapter)
    {
        $this->db = Dbal::connection($adapter);
    }

    //remove audit data
    public function removeAuditData($params)
    {
        // One audit at a time: an array would select one row to archive but
        // delete every id in it.
        $id = $params['id'] ?? null;
        if (!is_scalar($id) || trim((string) $id) === '') {
            return false;
        }
        $columns = $this->db->fetchFirstColumn("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'spi_form_v_6_duplicate'");
        if ($columns === []) {
            return false;
        }
        $audit = $this->db->fetchAssociative('SELECT * FROM spi_form_v_6 WHERE id = ?', [$id]);
        if (!$audit) {
            return false;
        }

        // DBAL's insert() does not quote column names, and these hold hyphens.
        $data = [];
        foreach ($columns as $column) {
            $data[$this->db->quoteSingleIdentifier($column)] = $audit[$column];
        }
        $this->db->insert('spi_form_v_6_duplicate', $data);
        $this->db->delete('spi_form_v_6', ['id' => $id]);

        return $id;
    }
}

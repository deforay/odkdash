<?php

namespace Application\Model;

use Doctrine\DBAL\Connection;

/** V3 audits removed as duplicates, kept in spi_form_v_3_duplicate. */
class SpiFormVer3DuplicateTable
{
    private readonly Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
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
        $columns = $this->db->fetchFirstColumn("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'spi_form_v_3_duplicate'");
        if ($columns === []) {
            return false;
        }
        $audit = $this->db->fetchAssociative('SELECT * FROM spi_form_v_3 WHERE id = ?', [$id]);
        if (!$audit) {
            return false;
        }

        // DBAL's insert() does not quote column names, and these hold hyphens.
        $data = [];
        foreach ($columns as $column) {
            $data[$this->db->quoteSingleIdentifier($column)] = $audit[$column];
        }
        $this->db->insert('spi_form_v_3_duplicate', $data);
        $this->db->delete('spi_form_v_3', ['id' => $id]);

        return $id;
    }
}

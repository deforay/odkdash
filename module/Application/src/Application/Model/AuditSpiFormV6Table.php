<?php

namespace Application\Model;

use Doctrine\DBAL\Connection;

/** The change history of V6 audits, kept in audit_spi_form_v_6. */
class AuditSpiFormV6Table
{
    private readonly Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function fetchAllDetails($parameters)
    {
        $columnsSql = 'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION';
        $response['auditColumns'] = $this->db->fetchAllAssociative($columnsSql, ['audit_spi_form_v_6']);
        $response['spiColumns'] = $this->db->fetchAllAssociative($columnsSql, ['spi_form_v_6']);

        $audits = new SpiFormVer6Table($this->db);
        $metaInstance = trim((string) ($parameters['metaInstance'] ?? ''));
        $response['currentRecord'] = $metaInstance !== '' ? $audits->fetchV6DetailsByMetaInstanceId($metaInstance) : false;
        // A mapped user sees the trail of an audit in their locations only.
        // A deleted audit has no location left, so only a user who sees
        // every audit sees its trail. Out of scope reads as not found.
        $inScope = $response['currentRecord']
            ? $audits->auditInScope($response['currentRecord']['id'])
            : SpiFormVer6Table::seesEveryAudit();
        if ($metaInstance !== '' && $inScope) {
            $response['auditInfo'] = $this->db->fetchAllAssociative(
                'SELECT * FROM audit_spi_form_v_6 WHERE `meta-instance-id` = ?',
                [$metaInstance],
            );
        } else {
            $response["status"] = "fail";
            $response["message"] = "Please select valid Sample Code!";
        }
        return $response;
    }
}

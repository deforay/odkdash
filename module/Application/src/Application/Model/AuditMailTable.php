<?php

namespace Application\Model;

use App\Db\Dbal;
use Doctrine\DBAL\Connection;
use Laminas\Db\Adapter\Adapter;

/**
 * The audit_mails queue that bin/console send-audit-mail sends from.
 */
class AuditMailTable
{
    private const string TABLE = 'audit_mails';

    private readonly Connection $db;

    public function __construct(Adapter $adapter)
    {
        $this->db = Dbal::connection($adapter);
    }

    public function insertAuditMailDetails($toEmailAddress, $cc, $subject, $message, $fromName, $fromEmailAddress, $auditIds)
    {
        $this->db->insert(self::TABLE, [
            'from_full_name' => $fromName,
            'from_mail' => $fromEmailAddress,
            'to_email' => $toEmailAddress,
            'cc' => $cc,
            'subject' => $subject,
            'message' => $message,
            'audit_ids' => $auditIds,
        ]);

        return $this->db->lastInsertId();
    }

    public function updateInitialAuditMailStatus($id)
    {
        return $this->db->update(self::TABLE, ['status' => 'not-sent'], ['mail_id' => $id]);
    }

    public function updateAuditMailStatus($id)
    {
        return $this->db->update(self::TABLE, ['status' => 'sent'], ['mail_id' => $id]);
    }
}

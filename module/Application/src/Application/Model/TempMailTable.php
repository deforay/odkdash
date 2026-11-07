<?php

namespace Application\Model;

use Doctrine\DBAL\Connection;

/**
 * The temp_mail queue that bin/console send-temp-mail sends from.
 */
class TempMailTable
{
    private const string TABLE = 'temp_mail';

    public function __construct(private readonly Connection $db)
    {
    }

    public function insertTempMailDetails($toEmailAddress, $cc, $subject, $message, $fromName, $fromEmailAddress, $bcc = null)
    {
        $this->db->insert(self::TABLE, [
            'from_full_name' => $fromName,
            'from_mail' => $fromEmailAddress,
            'to_email' => $toEmailAddress,
            'cc' => $cc,
            'bcc' => $bcc,
            'subject' => $subject,
            'message' => $message,
        ]);

        return $this->db->lastInsertId();
    }

    public function updateTempMailStatus($id)
    {
        return $this->db->update(self::TABLE, ['status' => 'not-sent'], ['temp_id' => $id]);
    }

    public function deleteTempMail($id)
    {
        $this->db->delete(self::TABLE, ['temp_id' => $id]);
    }
}

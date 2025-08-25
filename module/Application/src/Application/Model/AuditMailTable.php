<?php

namespace Application\Model;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Sql\Sql;



/**
 * Description of Countries
 *
 * @author amit
 */
class AuditMailTable extends AbstractTableGateway
{

    protected $table = 'audit_mails';
    protected $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function insertAuditMailDetails($toEmailAddress, $cc, $subject, $message, $fromName, $fromEmailAddress, $auditIds)
    {
        $data = array(
            'from_full_name' => $fromName,
            'from_mail' => $fromEmailAddress,
            'to_email' => $toEmailAddress,
            'cc' => $cc,
            'subject' => $subject,
            'message' => $message,
            'audit_ids' => $auditIds
        );
        $this->insert($data);
        return $this->lastInsertValue;
    }

    public function updateInitialAuditMailStatus($id)
    {
        return $this->update(array('status' => 'not-sent'), array('mail_id' => $id));
    }

    public function updateAuditMailStatus($id)
    {
        return $this->update(array('status' => 'sent'), array('mail_id' => $id));
    }
}

<?php

namespace Certification\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;

class RecertificationTable {

    protected $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll() {
        $sqlSelect = $this->tableGateway->getSql()->select();
        $sqlSelect->columns(array('recertification_id', 'due_date', 'reminder_type', 'reminder_sent_to', 'name_of_recipient', 'date_reminder_sent','provider_id'))
                  ->join('provider', 'provider.id=recertification.provider_id', array('certification_id','last_name','first_name','middle_name'), 'left');

        $sqlSelect->order('recertification_id desc');
        $resultSet = $this->tableGateway->selectWith($sqlSelect);
//        die(print_r($resultSet));
        return $resultSet;
    }

    public function getRecertification($recertification_id) {
        $recertification_id = (int) $recertification_id;
        $rowset = $this->tableGateway->select(array('recertification_id' => $recertification_id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $recertification_id");
        }
        return $row;
    }

    public function saveRecertification(Recertification $recertification) {
        $data = array(
            'due_date' => $recertification->due_date,
            'provider_id' => $recertification->provider_id,
            'reminder_type' => $recertification->reminder_type,
            'reminder_sent_to' => $recertification->reminder_sent_to,
            'name_of_recipient' => strtoupper($recertification->name_of_recipient),
            'date_reminder_sent' => $recertification->date_reminder_sent,
        );
//die((print_r($data)));
        $recertification_id = (int) $recertification->recertification_id;
        if ($recertification_id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getRecertification($recertification_id)) {
                $this->tableGateway->update($data, array('recertification_id' => $recertification_id));
            } else {
                throw new \Exception('Recertification id does not exist');
            }
        }
    }
    /**
     * select reminder witch must be  send
     * @return type
     */
     public function fetchAll2() {
        $db = $this->tableGateway->getAdapter();
        $sqlSelect="select  certification.id ,examination, final_decision, certification_issuer, date_certificate_issued, "
                . "date_certificate_sent, certification_type, provider,last_name, first_name, middle_name, certification_id,"
                . " certification_reg_no, professional_reg_no,email,date_end_validity,facility_in_charge_email from certification, examination, provider where "
                . "examination.id = certification.examination and provider.id = examination.provider and final_decision='certified' and certificate_sent = 'yes' and reminder_sent='no' and"
                . " datediff(now(),date_end_validity) >=-60 order by certification.id asc";
        $statement = $db->query($sqlSelect);
        $resultSet = $statement->execute();
        
        return $resultSet;
    }

}

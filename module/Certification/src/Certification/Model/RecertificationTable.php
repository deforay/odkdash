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
        $sqlSelect->columns(array('recertification_id', 'due_date', 'reminder_type', 'reminder_sent_to', 'name_of_recipient', 'date_reminder_sent', 'provider_id'))
                ->join('provider', 'provider.id=recertification.provider_id', array('certification_id', 'last_name', 'first_name', 'middle_name'), 'left');

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
        
        $due_date = $recertification->due_date;
        $date_explode = explode("-", $due_date);
        $newsdate = $date_explode[2] . '-' . $date_explode[1] . '-' . $date_explode[0];

        $date_reminder_sent = $recertification->date_reminder_sent;
        $date_explode2 = explode("-", $date_reminder_sent);
        $newsdate2 = $date_explode2[2] . '-' . $date_explode2[1] . '-' . $date_explode2[0];

        $data = array(
            'due_date' => $newsdate,
            'provider_id' => $recertification->provider_id,
            'reminder_type' => $recertification->reminder_type,
            'reminder_sent_to' => $recertification->reminder_sent_to,
            'name_of_recipient' => strtoupper($recertification->name_of_recipient),
            'date_reminder_sent' => $newsdate2,
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
        $sqlSelect = "select  certification.id ,examination, final_decision, certification_issuer, date_certificate_issued, "
                . "date_certificate_sent, certification_type, provider,last_name, first_name, middle_name, certification_id,"
                . " certification_reg_no, professional_reg_no,email,date_end_validity,facility_in_charge_email from certification, examination, provider where "
                . "examination.id = certification.examination and provider.id = examination.provider and final_decision='certified' and certificate_sent = 'yes' and reminder_sent='no' and"
                . " datediff(now(),date_end_validity) >=-60 order by certification.id asc";
        $statement = $db->query($sqlSelect);
        $resultSet = $statement->execute();

        return $resultSet;
    }

    public function ReminderSent($certification_id) {
        $db = $this->tableGateway->getAdapter();
        $sql = "UPDATE certification set reminder_sent='yes' where id=" . $certification_id;
        $db->getDriver()->getConnection()->execute($sql);
    }

    public function certificationInfo($certification_id) {
        $db = $this->tableGateway->getAdapter();
        $sql2 = 'SELECT provider.id as provider_id, last_name, first_name, middle_name, certification.date_end_validity as due_date from provider, examination , certification WHERE provider.id=examination.provider AND examination.id=certification.examination and certification.id='.$certification_id;
        $statement2 = $db->query($sql2);
        $result2 = $statement2->execute();

        $selectData = array();

        foreach ($result2 as $res2) {
            $selectData['name'] = $res2['last_name'] . ' ' . $res2['first_name'] . ' ' . $res2['middle_name'];
            $selectData['id'] = $res2['provider_id'];
            $selectData['due_date'] = $res2['due_date'];
        }
        
        return $selectData;
       
    }

}

<?php

namespace Certification\Model;

use Zend\Db\TableGateway\TableGateway;

class CertificationTable {

    protected $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    /**
     * select all certified tester
     * @return type
     */
    public function fetchAll() {
        $sqlSelect = $this->tableGateway->getSql()->select();
        $sqlSelect->columns(array('id', 'examination', 'final_decision', 'certification_issuer', 'date_certificate_issued', 'date_certificate_sent', 'certification_type'));
        $sqlSelect->join('examination', 'examination.id = certification.examination ', array('provider'), 'left')
                ->join('provider', 'provider.id = examination.provider ', array('last_name', 'first_name', 'middle_name', 'certification_id', 'certification_reg_no', 'professional_reg_no', 'email'), 'left')
                ->where(array('final_decision' => 'certified'));
        $sqlSelect->order('id desc');

        $resultSet = $this->tableGateway->selectWith($sqlSelect);
        return $resultSet;
    }

    /**
     * select tester who are pending or failed to certification
     * @return type
     */
    public function fetchAll2() {
        $sqlSelect = $this->tableGateway->getSql()->select();
        $sqlSelect->columns(array('id', 'examination', 'final_decision', 'certification_issuer', 'date_certificate_issued', 'date_certificate_sent', 'certification_type'));
        $sqlSelect->join('examination', 'examination.id = certification.examination ', array('provider'), 'left')
                ->join('provider', 'provider.id = examination.provider ', array('last_name', 'first_name', 'middle_name', 'certification_id', 'certification_reg_no', 'professional_reg_no'), 'left')
                ->where(array('final_decision' => 'failed'))
                ->where(array('final_decision' => 'pending'), \Zend\Db\Sql\Where::OP_OR);
        $sqlSelect->order('id desc');

        $resultSet = $this->tableGateway->selectWith($sqlSelect);
        return $resultSet;
    }

    public function getCertification($id) {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveCertification(Certification $certification) {
        $date_issued = $certification->date_certificate_issued;
        $date_explode = explode("-", $date_issued);
        $newsdate = $date_explode[2] . '-' . $date_explode[1] . '-' . $date_explode[0];

        $date_end = date("Y-m-d", strtotime($date_issued . "  + 2 year"));

        $data = array(
            'examination' => $certification->examination,
            'final_decision' => $certification->final_decision,
            'certification_issuer' => strtoupper($certification->certification_issuer),
            'date_certificate_issued' => $newsdate,
            'date_certificate_sent' => $certification->date_certificate_sent,
            'certification_type' => $certification->certification_type,
            'date_end_validity' => $date_end
        );
//        die(print_r($data));
        $id = (int) $certification->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getCertification($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('certification id does not exist');
            }
        }
    }

    public function last_id() {
        $last_id = $this->tableGateway->lastInsertValue;
//        die($last_id);
        return $last_id;
    }

    public function updateExamination($last_id) {
        $db = $this->tableGateway->getAdapter();
        $sql1 = 'select examination from certification where id=' . $last_id;
        $statement = $db->query($sql1);
        $result = $statement->execute();
        foreach ($result as $res) {
            $examination = $res['examination'];
        }

//        die ($examination);

        $sql = 'UPDATE examination SET add_to_certification= "yes" WHERE id=' . $examination;

        $db->getDriver()->getConnection()->execute($sql);
    }

    public function setToActive($last_id) {
        $db = $this->tableGateway->getAdapter();
        $sql1 = 'SELECT id_written_exam,practical_exam_id,final_decision FROM certification ,examination WHERE certification.examination=examination.id and certification.id=' . $last_id;
        $statement1 = $db->query($sql1);
        $result1 = $statement1->execute();

        foreach ($result1 as $res1) {
            $written = $res1['id_written_exam'];
            $practical = $res1['practical_exam_id'];
            $decision = $res1['final_decision'];
        }
        if ((strcasecmp($decision, 'Certified') == 0) || (strcasecmp($decision, 'failed') == 0)) {
// 
            $sql2 = "UPDATE written_exam SET display='no' WHERE id_written_exam=" . $written;
            $statement2 = $db->query($sql2);
            $result2 = $statement2->execute();

            $sql3 = "UPDATE practical_exam SET display='no' WHERE practice_exam_id=" . $practical;
            $statement3 = $db->query($sql3);
            $result3 = $statement3->execute();
        }
    }

    public function certificationType($provider) {
        $db = $this->tableGateway->getAdapter();
        $sql1 = 'SELECT certification_id FROM provider WHERE id =' . $provider;
//        die($sql1);
        $statement = $db->query($sql1);
        $result = $statement->execute();
        foreach ($result as $res) {
            $certification_id = $res['certification_id'];
        }

        return $certification_id;
    }

    public function certificationId($provider) {
        $db = $this->tableGateway->getAdapter();
        $sql = 'SELECT MAX(certification_id) as max FROM provider';
        $statement = $db->query($sql);
        $result = $statement->execute();
        foreach ($result as $res) {
            $max = $res['max'];
        }
        $array = explode("C", $max);
        $array2 = explode("-", $max);

        if (date('Y') > $array2[0]) {

            $certification_id = date('Y') . '-C' . substr_replace("0000", 1, -strlen(1));
        } else {

            $certification_id = $array2[0] . '-C' . substr_replace("0000", ($array[1] + 1), -strlen(($array[1] + 1)));
        }

        $sql2 = "UPDATE provider SET certification_id='" . $certification_id . "' WHERE id=" . $provider;

        $db->getDriver()->getConnection()->execute($sql2);
    }

    /**
     * select certified testers who certificate are not yet sent
     * @return type
     */
    public function fetchAll3() {
        $sqlSelect = $this->tableGateway->getSql()->select();
        $sqlSelect->columns(array('id', 'examination', 'final_decision', 'certification_issuer', 'date_certificate_issued', 'date_certificate_sent', 'certification_type', 'date_end_validity'));
        $sqlSelect->join('examination', 'examination.id = certification.examination ', array('provider'), 'left')
                ->join('provider', 'provider.id = examination.provider ', array('last_name', 'first_name', 'middle_name', 'certification_id', 'certification_reg_no', 'professional_reg_no', 'email', 'facility_in_charge_email'), 'left')
                ->where(array('final_decision' => 'certified'))
                ->where(array('certificate_sent' => 'no'));
        $sqlSelect->order('id desc');

        $resultSet = $this->tableGateway->selectWith($sqlSelect);
        return $resultSet;
    }

    public function countCertificate() {
        $db = $this->tableGateway->getAdapter();
        $sqlSelect = 'select COUNT(*)  as nb from  (select certification.id, examination, final_decision, certification_issuer, date_certificate_issued, date_certificate_sent, certification_type, date_end_validity,examination.provider, last_name, first_name, middle_name, certification_id, certification_reg_no, professional_reg_no, email, facility_in_charge_email from certification,examination,provider             where examination.id = certification.examination and provider.id = examination.provider and final_decision ="certified" and certificate_sent ="no") as tab';
        $statement = $db->query($sqlSelect);

        $result = $statement->execute();
        foreach ($result as $res) {
            $nb = $res['nb'];
        }
        return $nb;
    }

    public function countReminder() {
        $db = $this->tableGateway->getAdapter();
        $sqlSelect = 'select COUNT(*) as nb2 from (select  certification.id ,examination, final_decision, certification_issuer, date_certificate_issued, 
                date_certificate_sent, certification_type, provider,last_name, first_name, middle_name, certification_id,
                certification_reg_no, professional_reg_no,email,date_end_validity,facility_in_charge_email from certification, examination, provider where examination.id = certification.examination and provider.id = examination.provider and final_decision="certified" and certificate_sent = "yes" and reminder_sent="no" and datediff(now(),date_end_validity) >=-60 order by certification.id asc) as tab';
        $statement = $db->query($sqlSelect);

        $result = $statement->execute();
        foreach ($result as $res) {
            $nb2 = $res['nb2'];
        }
        return $nb2;
    }

    public function CertificateSent($provider) {
        $db = $this->tableGateway->getAdapter();
        $sql = "UPDATE certification set certificate_sent='yes' where id=" . $provider;
        $db->getDriver()->getConnection()->execute($sql);
    }

}

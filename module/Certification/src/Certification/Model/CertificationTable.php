<?php

namespace Certification\Model;

use Zend\Db\TableGateway\TableGateway;

class CertificationTable {

    protected $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll() {
        $sqlSelect = $this->tableGateway->getSql()->select();
        $sqlSelect->columns(array('id', 'examination', 'final_decision', 'certification_issuer_id', 'date_certificate_issued', 'date_certificate_sent', 'certification_type', 'issued'));
        $sqlSelect->join('certification_issuer', ' certification_issuer.certification_issuer_id = certification.certification_issuer_id ', array('issuer_last_name', 'issuer_first_name', 'issuer_middle_name'), 'left')
                ->join('examination', 'examination.id = certification.examination ', array('provider'), 'left')
                ->join('provider', 'provider.id = examination.provider ', array('last_name', 'first_name', 'middle_name', 'certification_id'), 'left');
        $sqlSelect->order('id desc');
//                   
//                  ->join('trainer', 'trainer.trainer_id = training.trainer_id ', array('trainer_last_name','trainer_first_name'), 'left')


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
        $data = array(
            'examination' => $certification->examination,
            'final_decision' => $certification->final_decision,
            'certification_issuer_id' => $certification->certification_issuer_id,
            'date_certificate_issued' => $certification->date_certificate_issued,
            'date_certificate_sent' => $certification->date_certificate_sent,
            'certification_type' => $certification->certification_type,
            'issued' => $certification->issued,
        );

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
        if ((strcasecmp($decision, 'Certified') == 0) || (strcasecmp($decision, 'failed')==0)) {
// 
            $sql2 = "UPDATE written_exam SET display='no' WHERE id_written_exam=" . $written;
            $statement2 = $db->query($sql2);
            $result2 = $statement2->execute();

            $sql3 = "UPDATE practical_exam SET display='no' WHERE practice_exam_id=" . $practical;
            $statement3 = $db->query($sql3);
            $result3 = $statement3->execute();
        }
    }

}

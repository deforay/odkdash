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
        $sqlSelect->columns(array('id', 'certification_id','final_decision', 'certification_issuer_id', 'date_certificate_issued', 'date_certificate_sent', 'certification_type', 'issued'));
        $sqlSelect->join('provider', 'provider.certification_id = certification.certification_id', array('last_name', 'first_name', 'middle_name'), 'left')
                  ->join('certification_issuer', 'certification_issuer.certification_issuer_id = certification.certification_issuer_id', array('issuer_last_name', 'issuer_first_name', 'issuer_middle_name'), 'left');
        $sqlSelect->order('last_name ASC');
        

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
            'certification_id' => $certification->certification_id,
            'final_decision' => $certification->final_decision,
            'certification_issuer_id' => $certification->certification_issuer_id,
            'date_certificate_issued' => $certification->date_certificate_issued,
            'date_certificate_sent' => $certification->date_certificate_sent,
            'certification_type' => $certification->certification_type,
            'issued' => $certification->issued
        );
       print_r($data);
        $id = (int) $certification->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getCertification($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('certificaton id does not exist');
            }
        }
        
        
    }

}

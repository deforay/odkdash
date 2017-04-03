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
        $sqlSelect->columns(array('certification_id', 'provider_id', 'score', 'final_decision', 'certificate_issued_by_id', 'date_certificate_issued', 'date_certificate_sent', 'certification_type', 'issued'));
        $sqlSelect->join('provider', 'provider.certification_id = certification.provider_id' , array('last_name','first_name','middle_name'), 'left');
        $sqlSelect->order('last_name ASC');

        $resultSet = $this->tableGateway->selectWith($sqlSelect);
        return $resultSet;
    }
    
    public function getCertification($certification_id)
     {
         $certification_id  = (int) $certification_id;
         $rowset = $this->tableGateway->select(array('certification_id' => $certification_id));
         $row = $rowset->current();
         if (!$row) {
             throw new \Exception("Could not find row $certification_id");
         }
         return $row;
     }

     public function saveCertification(Certification $certification)
     {
         $data = array(
             'provider_id' => $certification->provider_id,
             'score'  => $certification->score,
             'final_decision'  => $certification->final_decision,
             'certificate_issued_by_id'  => $certification->certificate_issued_by_id,
             'date_certificate_issued'  => $certification->date_certificate_issued,
             'date_certificate_sent'  => $certification->date_certificate_sent,
             'certification_type'  => $certification->certification_type,
         );

         $certification_id = (int) $certification->certification_id;
         if ($certification_id == 0) {
             $this->tableGateway->insert($data);
         } else {
             if ($this->getCertification($certification_id)) {
                 $this->tableGateway->update($data, array('certification_id' => $certification_id));
             } else {
                 throw new \Exception('Certification id does not exist');
             }
         }
     }

}

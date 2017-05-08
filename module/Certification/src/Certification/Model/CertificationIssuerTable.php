<?php

namespace Certification\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class CertificationIssuerTable {

    protected $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll($paginated=false) {
        if ($paginated) {
             $select = new Select('certification_issuer');
              $select->order('issuer_last_name asc');
            $resultSetPrototype = new ResultSet();
             $resultSetPrototype->setArrayObjectPrototype(new CertificationIssuer());
             // create a new pagination adapter object
             $paginatorAdapter = new DbSelect(
                 // our configured select object
                 $select,
                 // the adapter to run it against
                 $this->tableGateway->getAdapter(),
                 // the result set to hydrate
                 $resultSetPrototype
             );
             $paginator = new Paginator($paginatorAdapter);
             return $paginator;
         }
        
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function getCertificationIssuer($certification_issuer_id) {
        $certification_issuer_id = (int) $certification_issuer_id;
        $rowset = $this->tableGateway->select(array('certification_issuer_id' => $certification_issuer_id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $certification_issuer_id");
        }
        return $row;
    }

    public function saveCertificationIssuer(CertificationIssuer $certification_issuer) {
        
        $last_name = strtoupper($certification_issuer->issuer_last_name);
        $first_name = ucfirst($certification_issuer->issuer_first_name);
        $middle_name = ucfirst($certification_issuer->issuer_middle_name);
        $region = ucfirst($certification_issuer->region);
        $district = ucfirst($certification_issuer->district);
        $current_job = ucfirst($certification_issuer->current_job);
        
        $data = array(
            'issuer_last_name' => $last_name,
            'issuer_first_name' => $first_name,
            'issuer_middle_name' => $middle_name,
            'region' => $region,
            'district' => $district,
            'phone' => $certification_issuer->phone,
            'email' => $certification_issuer->email,
            'prefered_contact_method' => $certification_issuer->prefered_contact_method,
            'current_job' => $current_job,
        );
        print_r($data);
        $certification_issuer_id = (int) $certification_issuer->certification_issuer_id;
        if ($certification_issuer_id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getCertificationIssuer($certification_issuer_id)) {
                $this->tableGateway->update($data, array('certification_issuer_id' => $certification_issuer_id));
            } else {
                throw new \Exception('certification Issuer id does not exist');
            }
        }
    }
    
     public function search($motCle) {
        $sqlSelect = $this->tableGateway->getSql()->select();
            $sqlSelect->columns(array( 'certification_issuer_id', 'issuer_last_name', 'issuer_first_name', 'issuer_middle_name', 'region', 'district', 'phone', 'email', 'prefered_contact_method', 'current_job'));
         
          $sqlSelect->where->like('issuer_last_name', '%'. $motCle .'%');
          $sqlSelect->where->OR->like('issuer_middle_name', '%'. $motCle .'%');
          $sqlSelect->where->OR->like('issuer_first_name', '%'. $motCle .'%');
         
        $sqlSelect->order('issuer_last_name ASC');
        ?> 
        <pre><?php // print_r($sqlSelect) ; ?></pre> <?php
        $resultSet = $this->tableGateway->selectWith($sqlSelect);
        return $resultSet;
    }

}

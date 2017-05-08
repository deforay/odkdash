<?php

namespace Certification\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Certification\Model\Trainer;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class TrainerTable extends AbstractTableGateway {

    private $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll($paginated = false) {
        if ($paginated) {
            
            $sqlSelect = $this->tableGateway->getSql()->select();
            $sqlSelect->columns(array('trainer_id', 'trainer_last_name', 'trainer_first_name', 'trainer_middle_name', 'district', 'region', 'phone', 'email', 'job_address', 'prefered_contact_method', 'current_jod'));
            $sqlSelect->order('trainer_last_name asc');
            

            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Trainer());

            $paginatorAdapter = new DbSelect(
                    // our configured select object
                    $sqlSelect,
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

    public function getTrainer($trainer_id) {
        $trainer_id = (int) $trainer_id;
        $rowset = $this->tableGateway->select(array('trainer_id' => $trainer_id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $trainer_id");
        }
        return $row;
    }

    public function saveTrainer(Trainer $trainer) {
        
        $last_name = strtoupper($trainer->trainer_last_name);
        $first_name = ucfirst($trainer->trainer_first_name);
        $middle_name = ucfirst($trainer->trainer_middle_name);
        $region = ucfirst($trainer->region);
        $district = ucfirst($trainer->district);
        $job_address= ucfirst($trainer->job_address);
         $current_job = ucfirst($trainer->current_jod);
        
        $data = array(
            'trainer_last_name' => $last_name,
            'trainer_first_name' => $first_name,
            'trainer_middle_name' => $middle_name,
            'region' => $region,
            'district' => $district,
            'phone' => $trainer->phone,
            'email' => $trainer->email,
            'job_address' => $job_address,
            'prefered_contact_method' => $trainer->prefered_contact_method,
            'current_jod' => $current_job,
        );
//        print_r($data);

        $trainer_id = (int) $trainer->trainer_id;
        if ($trainer_id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getTrainer($trainer_id)) {
                $this->tableGateway->update($data, array('trainer_id' => $trainer_id));
            } else {
                throw new \Exception('Trainer id does not exist');
            }
        }
    }

    public function search($motCle) {
        $sqlSelect = $this->tableGateway->getSql()->select();
            $sqlSelect->columns(array( 'trainer_id', 'trainer_last_name', 'trainer_first_name', 'trainer_middle_name', 'district', 'region', 'phone', 'email', 'job_address', 'prefered_contact_method', 'current_jod'));
         
          $sqlSelect->where->like('trainer_last_name', '%'. $motCle .'%');
          $sqlSelect->where->OR->like('trainer_middle_name', '%'. $motCle .'%');
          $sqlSelect->where->OR->like('trainer_first_name', '%'. $motCle .'%');
         
        $sqlSelect->order('trainer_last_name ASC');
        ?> 
        <pre><?php // print_r($sqlSelect) ; ?></pre> <?php
        $resultSet = $this->tableGateway->selectWith($sqlSelect);
        return $resultSet;
    }
    
}

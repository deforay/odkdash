<?php

namespace Certification\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class TrainingOrganizationTable extends AbstractTableGateway {

    private $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll($paginated = false) {

        if ($paginated) {
           $select = new Select('training_organization');
           $select->order('training_organization_name asc');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new TrainingOrganization());
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

    public function getTraining_organization($training_organization_id) {
        $training_organization_id = (int) $training_organization_id;
        $rowset = $this->tableGateway->select(array('training_organization_id' => $training_organization_id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $training_organization_id");
        }
        return $row;
    }

    public function saveTraining_Organization(TrainingOrganization $training_organization) {
        $data = array(
            'training_organization_name' => $training_organization->training_organization_name,
            'type_organization' => $training_organization->type_organization
        );
//        var_dump($training_organization);
        $training_organization_id = (int) $training_organization->training_organization_id;
        if ($training_organization_id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getTraining_organization($training_organization_id)) {
                $this->tableGateway->update($data, array('training_organization_id' => $training_organization_id));
            } else {
                throw new \Exception('Training organization id does not exist');
            }
        }
    }

    public function search($motCle) {
        $sqlSelect = $this->tableGateway->getSql()->select();
            $sqlSelect->columns(array('training_organization_id', 'training_organization_name', 'type_organization'));
         
          $sqlSelect->where->like('training_organization_name', '%'. $motCle .'%');
          $sqlSelect->where->OR->like('type_organization', '%'. $motCle .'%');
          
         
        $sqlSelect->order('training_organization_name');
        ?> 
        <pre><?php // print_r($sqlSelect) ; ?></pre> <?php
        $resultSet = $this->tableGateway->selectWith($sqlSelect);
        return $resultSet;
    }
}

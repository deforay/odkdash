<?php

namespace Certification\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Certification\Model\Training;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class TrainingTable extends AbstractTableGateway {

    private $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll($paginated = false) {
        if ($paginated) {
            $sqlSelect = $this->tableGateway->getSql()->select();
            $sqlSelect->columns(array('training_id', 'Start_date', 'end_date', 'type_of_training', 'training_organization_id', 'trainer_id', 'score', 'Pass_fail', 'training_certificate', 'Comments'));
            $sqlSelect->join('provider', 'provider.id = training.Provider_id', array('last_name', 'first_name', 'middle_name', 'professional_reg_no', 'certification_id','certification_reg_no'), 'left')
                    ->join('training_organization', 'training_organization.training_organization_id = training.training_organization_id ', array('training_organization_name', 'type_organization'), 'left')
                    ->join('trainer', 'trainer.trainer_id = training.trainer_id ', array('trainer_last_name', 'trainer_first_name'), 'left');
            $sqlSelect->order('training_id desc');

            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Training());
            $paginatorAdapter = new DbSelect(
                    $sqlSelect, $this->tableGateway->getAdapter(), $resultSetPrototype
            );
            $paginator = new Paginator($paginatorAdapter);
            return $paginator;
        }
        $resultSet = $this->tableGateway->selectWith($sqlSelect);
        return $resultSet;
    }

    public function getTraining($training_id) {
        $training_id = (int) $training_id;
        $rowset = $this->tableGateway->select(array('training_id' => $training_id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $training_id");
        }
        return $row;
    }

    public function saveTraining(Training $Training) {
        $data = array(
            'Provider_id' => $Training->Provider_id,
            'Start_date' => $Training->Start_date,
            'end_date' => $Training->end_date,
            'type_of_training' => $Training->type_of_training,
            'training_organization_id' => $Training->training_organization_id,
            'trainer_id' => $Training->trainer_id,
            'score' => $Training->score,
            'Pass_fail' => $Training->Pass_fail,
            'training_certificate' => $Training->training_certificate,
            'Comments' => $Training->Comments,
        );
        print_r($data);
        $training_id = (int) $Training->training_id;

        if ($training_id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getTraining($training_id)) {
                $this->tableGateway->update($data, array('training_id' => $training_id));
            } else {
                throw new \Exception('Training  id does not exist');
            }
        }
    }

    public function search($motCle) {
        $sqlSelect = $this->tableGateway->getSql()->select();
        $sqlSelect->columns(array('training_id', 'Start_date', 'end_date', 'type_of_training', 'training_organization_id', 'trainer_id', 'score', 'Pass_fail', 'training_certificate', 'Comments'));
        $sqlSelect->join('provider', 'provider.id = training.Provider_id', array('last_name', 'first_name', 'middle_name', 'provider_id', 'certification_id'), 'left')
                ->join('training_organization', 'training_organization.training_organization_id = training.training_organization_id ', array('training_organization_name', 'type_organization'), 'left')
                ->join('trainer', 'trainer.trainer_id = training.trainer_id ', array('trainer_last_name', 'trainer_first_name'), 'left');

        $sqlSelect->where->like('last_name', '%' . $motCle . '%');
        $sqlSelect->where->OR->like('first_name', '%' . $motCle . '%');
        $sqlSelect->where->OR->like('middle_name', '%' . $motCle . '%');
        $sqlSelect->where->OR->like('training_organization_name', '%' . $motCle . '%');
        $sqlSelect->where->OR->like('trainer_last_name', '%' . $motCle . '%');
        $sqlSelect->where->OR->like('trainer_middle_name', '%' . $motCle . '%');
        $sqlSelect->where->OR->like('trainer_first_name', '%' . $motCle . '%');
        $sqlSelect->where->OR->like('certification_id', '%' . $motCle . '%');
        $sqlSelect->order('last_name ASC');
        ?> 
        <pre><?php // print_r($sqlSelect) ; ?></pre> <?php
        $resultSet = $this->tableGateway->selectWith($sqlSelect);
        return $resultSet;
    }

}

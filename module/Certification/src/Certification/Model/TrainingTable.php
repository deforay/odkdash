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

    public function fetchAll() {
       
            $sqlSelect = $this->tableGateway->getSql()->select();
            $sqlSelect->columns(array('training_id', 'Provider_id', 'type_of_competency', 'last_training_date', 'type_of_training', 'length_of_training', 'training_organization_id', 'facilitator', 'training_certificate', 'date_certificate_issued', 'Comments'));
            $sqlSelect->join('provider', 'provider.id = training.Provider_id', array('last_name', 'first_name', 'middle_name', 'professional_reg_no', 'certification_id', 'certification_reg_no'), 'left')
                    ->join('training_organization', 'training_organization.training_organization_id = training.training_organization_id ', array('training_organization_name', 'type_organization'), 'left');
            $sqlSelect->order('training_id desc');

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
            'type_of_competency' => $Training->type_of_competency,
            'last_training_date' => $Training->last_training_date,
            'type_of_training' => $Training->type_of_training,
            'length_of_training' => $Training->length_of_training,
            'training_organization_id' => $Training->training_organization_id,
            'facilitator' => strtoupper($Training->facilitator),
            'training_certificate' => $Training->training_certificate,
            'date_certificate_issued' => $Training->date_certificate_issued,
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

}

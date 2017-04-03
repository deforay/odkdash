<?php

namespace Certification\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;


class TrainingOrganizationTable extends AbstractTableGateway {

    private $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll() {
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
            'training_organization_name'=>$training_organization->training_organization_name,
             'type_organization'=>$training_organization->type_organization
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

}

<?php

namespace Certification\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Certification\Model\Training;

class TrainingTable extends AbstractTableGateway {

    private $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll() {
         $sqlSelect = $this->tableGateway->getSql()->select();
        $sqlSelect->columns(array('training_id','Provider_id','Start_date','end_date','type_of_training',
        'training_organization_id','trainer_id','score','Pass_fail','training_certificate','Comments'));
        $sqlSelect->join('provider', ' provider.certification_id = training.provider_id ', array('last_name','first_name'), 'left')
                  ->join('training_organization', 'training_organization.training_organization_id = training.training_organization_id ', array('training_organization_name'), 'left')
                  ->join('trainer', 'trainer.trainer_id = training.trainer_id ', array('trainer_last_name','trainer_first_name'), 'left');
                  
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

}

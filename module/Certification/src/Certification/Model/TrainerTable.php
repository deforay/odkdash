<?php

namespace Certification\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Certification\Model\Trainer;

class TrainerTable extends AbstractTableGateway {

    private $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll() {
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
        $data = array(
            'trainer_last_name' => $trainer->trainer_last_name,
            'trainer_first_name' => $trainer->trainer_first_name,
            'trainer_middle_name' => $trainer->trainer_middle_name,
            'region' => $trainer->region,
            'district' => $trainer->district,
            'phone' => $trainer->phone,
            'email' => $trainer->email,
            'job_address' => $trainer->job_address,
            'prefered_contact_method' => $trainer->prefered_contact_method,
            'current_jod' => $trainer->current_jod,
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

}

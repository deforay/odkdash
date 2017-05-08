<?php

namespace Certification\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class RecertificationTable {

    protected $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll($paginated=false) {
        if ($paginated) {
             $select = new Select('recertification');
             $select->order('recertification_id desc');
           $resultSetPrototype = new ResultSet();
             $resultSetPrototype->setArrayObjectPrototype(new Recertification());
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

    public function getRecertification($recertification_id) {
        $recertification_id = (int) $recertification_id;
        $rowset = $this->tableGateway->select(array('recertification_id' => $recertification_id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $recertification_id");
        }
        return $row;
    }

    public function saveRecertification(Recertification $recertification) {
        $data = array(
            'due_date' => $recertification->due_date,
            'certification_id' => $recertification->certification_id,
            'reminder_type' => $recertification->reminder_type,
            'reminder_sent_to' => $recertification->reminder_sent_to,
            'name_of_recipient' => $recertification->name_of_recipient,
        );

        $recertification_id = (int) $recertification->recertification_id;
        if ($recertification_id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getRecertification($recertification_id)) {
                $this->tableGateway->update($data, array('recertification_id' => $recertification_id));
            } else {
                throw new \Exception('Recertification id does not exist');
            }
        }
    }

}

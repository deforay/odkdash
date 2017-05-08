<?php

namespace Certification\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class ExamAdminTable extends AbstractTableGateway {

    private $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll($paginated = false) {
        if ($paginated) {
            $select = new Select('exam_admin_by');
            $select->order('admin_last_name asc');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new ExamAdmin());
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

    public function getExamAdmin($exam_admin_by_id) {
        $exam_admin_by_id = (int) $exam_admin_by_id;
        $rowset = $this->tableGateway->select(array('exam_admin_by_id' => $exam_admin_by_id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $exam_admin_by_id");
        }
        return $row;
    }

    public function saveExamAdmin(ExamAdmin $examAdmin) {
        $last_name = strtoupper($examAdmin->admin_last_name);
        $first_name = ucfirst($examAdmin->admin_first_name);
        $middle_name = ucfirst($examAdmin->admin_middle_name);
        $region = ucfirst($examAdmin->region);
        $district = ucfirst($examAdmin->district);
        $job_address = ucfirst($examAdmin->job_address);
         $current_job = ucfirst($examAdmin->current_job);

        $data = array(
            'admin_last_name' => $last_name,
            'admin_first_name' => $first_name,
            'admin_middle_name' => $middle_name,
            'district' => $district,
            'region' => $region,
            'email' => $examAdmin->email,
            'phone' => $examAdmin->phone,
            'prefered_contact_method' => $examAdmin->prefered_contact_method,
            'current_job' => $current_job,
            'job_address' => $job_address,
        );
//         var_dump($examAdmin);
        $exam_admin_by_id = (int) $examAdmin->exam_admin_by_id;
        if ($exam_admin_by_id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getExamAdmin($exam_admin_by_id)) {
                $this->tableGateway->update($data, array('exam_admin_by_id' => $exam_admin_by_id));
            } else {
                throw new \Exception('Exam Administrator id does not exist');
            }
        }
    }

    public function search($motCle) {
        $sqlSelect = $this->tableGateway->getSql()->select();
        $sqlSelect->columns(array('exam_admin_by_id', 'admin_last_name', 'admin_first_name', 'admin_middle_name', 'district', 'region', 'email', 'phone', 'prefered_contact_method', 'current_job', 'job_address'));

        $sqlSelect->where->like('admin_last_name', '%' . $motCle . '%');
        $sqlSelect->where->OR->like('admin_middle_name', '%' . $motCle . '%');
        $sqlSelect->where->OR->like('admin_first_name', '%' . $motCle . '%');

        $sqlSelect->order('admin_last_name ASC');
        ?> 
        <pre><?php // print_r($sqlSelect) ;  ?></pre> <?php
        $resultSet = $this->tableGateway->selectWith($sqlSelect);
        return $resultSet;
    }

}

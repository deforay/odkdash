<?php

namespace Certification\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Adapter;

class WrittenExamTable extends AbstractTableGateway {

    private $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll($paginated = false) {
        if ($paginated) {
            $sqlSelect = $this->tableGateway->getSql()->select();
            $sqlSelect->columns(array('id_written_exam', 'exam_type', 'provider_id', 'exam_admin_by_id', 'date', 'qa_point', 'rt_point',
                'safety_point', 'specimen_point', 'testing_algo_point', 'report_keeping_point', 'EQA_PT_points', 'ethics_point', 'total_points', 'final_score'));
            $sqlSelect->join('provider', ' provider.id= written_exam.provider_id ', array('last_name', 'first_name', 'middle_name'), 'left')
                    ->join('exam_admin_by', ' exam_admin_by.exam_admin_by_id= written_exam.exam_admin_by_id ', array('admin_last_name', 'admin_first_name', 'admin_middle_name'), 'left')
                    ->where(array('active'=>'no'));
            $sqlSelect->order('id_written_exam desc');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new WrittenExam());
            // create a new pagination adapter object
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
        $resultSet = $this->tableGateway->selectWith($sqlSelect);
        return $resultSet;
    }

    public function getWrittenExam($id_written_exam) {
        $id_written_exam = (int) $id_written_exam;
        $rowset = $this->tableGateway->select(array('id_written_exam' => $id_written_exam));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id_written_exam");
        }
        return $row;
    }

    public function saveWrittenExam(WrittenExam $written_exam) {
        $data = array(
            'exam_type' => $written_exam->exam_type,
            'provider_id' => $written_exam->provider_id,
            'exam_admin_by_id' => $written_exam->exam_admin_by_id,
            'date' => $written_exam->date,
            'qa_point' => $written_exam->qa_point,
            'rt_point' => $written_exam->rt_point,
            'safety_point' => $written_exam->safety_point,
            'specimen_point' => $written_exam->specimen_point,
            'testing_algo_point' => $written_exam->testing_algo_point,
            'report_keeping_point' => $written_exam->report_keeping_point,
            'EQA_PT_points' => $written_exam->EQA_PT_points,
            'ethics_point' => $written_exam->ethics_point,
            'total_points' => $written_exam->qa_point + $written_exam->rt_point + $written_exam->safety_point + $written_exam->specimen_point + $written_exam->testing_algo_point + $written_exam->report_keeping_point + $written_exam->EQA_PT_points + $written_exam->ethics_point,
            'final_score' => (($written_exam->qa_point + $written_exam->rt_point + $written_exam->safety_point + $written_exam->specimen_point + $written_exam->testing_algo_point + $written_exam->report_keeping_point + $written_exam->EQA_PT_points + $written_exam->ethics_point) * 100) / 25
        );
        ?>  <?php // print_r($data);          ?>   
        <?php
        $id_written_exam = (int) $written_exam->id_written_exam;
        if ($id_written_exam == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getWrittenExam($id_written_exam)) {
                $this->tableGateway->update($data, array('id_written_exam' => $id_written_exam));
            } else {
                throw new \Exception('Written Exam id does not exist');
            }
        }
    }

    public function search($motCle) {
        $sqlSelect = $this->tableGateway->getSql()->select();
        $sqlSelect->columns(array('id_written_exam', 'exam_type', 'provider_id', 'exam_admin_by_id', 'date', 'qa_point', 'rt_point',
            'safety_point', 'specimen_point', 'testing_algo_point', 'report_keeping_point', 'EQA_PT_points', 'ethics_point', 'total_points', 'final_score'));
        $sqlSelect->join('provider', ' provider.id= written_exam.provider_id ', array('last_name', 'first_name', 'middle_name'), 'left')
                ->join('exam_admin_by', ' exam_admin_by.exam_admin_by_id= written_exam.exam_admin_by_id ', array('admin_last_name', 'admin_first_name', 'admin_middle_name'), 'left');

        $sqlSelect->where->like('last_name', '%' . $motCle . '%');
        $sqlSelect->where->OR->like('first_name', '%' . $motCle . '%');
        $sqlSelect->where->OR->like('middle_name', '%' . $motCle . '%');
        $sqlSelect->where->OR->like('admin_last_name', '%' . $motCle . '%');
        $sqlSelect->where->OR->like('admin_first_name', '%' . $motCle . '%');
        $sqlSelect->where->OR->like('admin_middle_name', '%' . $motCle . '%');
        $sqlSelect->order('id_written_exam desc');
        ?> 
        <pre><?php // print_r($sqlSelect) ;        ?></pre> <?php
        $resultSet = $this->tableGateway->selectWith($sqlSelect);
        return $resultSet;
    }

    public function last_id() {
        $last_id = $this->tableGateway->lastInsertValue;
//        die($last_id);
        return $last_id;
    }

    /**
     * insert written exam id to  examination
     * @param type $last_id
     */
    public function insertToExamination($last_id) {
        $db = $this->tableGateway->getAdapter();
        $sql1 = 'select provider_id from written_exam where id_written_exam=' . $last_id;
        $statement = $db->query($sql1);
        $result = $statement->execute();
        foreach ($result as $res) {
            $provider = $res['provider_id'];
        }

        $sql2 = 'SELECT count(*) as nombre FROM examination WHERE provider=' . $provider . ' and id_written_exam is null and practical_exam_id is not null';
        $statement2 = $db->query($sql2);
        $result2 = $statement2->execute();
        foreach ($result2 as $res2) {
            $nombre = $res2['nombre'];
        }

        if ($nombre == 0) {
            $sql2 = 'insert into examination (id_written_exam,provider) values (' . $last_id . ',' . $provider . ')';
            $statement2 = $db->query($sql2);
            $result2 = $statement2->execute();
        } else {
            $sql = 'UPDATE examination SET id_written_exam=' . $last_id . ' WHERE provider=' . $provider;
            $db->getDriver()->getConnection()->execute($sql);
        }
    }

    /**
     * insert written and practical exam id to examination
     * @param type $written
     * @param type $last_id
     */
    public function examination($last_id, $practical) {

        $db = $this->tableGateway->getAdapter();
        $sql1 = 'select provider_id from written_exam where id_written_exam=' . $last_id;
        $statement = $db->query($sql1);
        $result = $statement->execute();
        foreach ($result as $res) {
            $provider = $res['provider_id'];
        }

        $sql2 = 'insert into examination (provider,id_written_exam,practical_exam_id) values (' . $provider . ',' . $last_id . ',' . $practical . ')';
        $statement2 = $db->query($sql2);
        $result2 = $statement2->execute();
    }

    /**
     * count the number of practical exam with the same id ad set the number of attempt for another attempt
     * @return type $nombre integer
     */
    public function countPractical($practical) {
        $db = $this->tableGateway->getAdapter();
        $sql3 = 'SELECT count(*) as nombre FROM examination WHERE practical_exam_id=' . $practical;
//        die($sql3);
        $statement3 = $db->query($sql3);
        $result3 = $statement3->execute();
        foreach ($result3 as $res3) {
            $nombre = $res3['nombre'];
        }
//        die($nombre);
        return $nombre;
    }

    public function getProviderName($practical) {
        $db = $this->tableGateway->getAdapter();
        $sql1 = 'select id, last_name, first_name, middle_name from provider , practical_exam where provider.id=practical_exam.provider_id and practice_exam_id='.$practical;
        $statement = $db->query($sql1);
        $result = $statement->execute();
        $selectData = array();

        foreach ($result as $res) {
            $selectData[$res['id']] = $res['last_name'] . ' ' . $res['first_name'] . ' ' . $res['middle_name'];
        }
        return $selectData;
    }

}

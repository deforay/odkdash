<?php

namespace Certification\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class PracticalExamTable extends AbstractTableGateway {

    private $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll($paginated = false) {
        if ($paginated) {

            $sqlSelect = $this->tableGateway->getSql()->select();
            $sqlSelect->columns(array('practice_exam_id', 'exam_type', 'exam_admin_by_id', 'provider_id', 'pre_analytic', 'analytic', 'post_analytic', 'Sample_testing_score', 'direct_observation_score', 'practical_total_score', 'date'));
            $sqlSelect->join('provider', ' provider.id = practical_exam.provider_id ', array('last_name', 'first_name', 'middle_name'), 'left')
                    ->join('exam_admin_by', ' exam_admin_by.exam_admin_by_id = practical_exam.exam_admin_by_id ', array('admin_last_name', 'admin_first_name', 'admin_middle_name',), 'left');
            $sqlSelect->order('practice_exam_id desc');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new PracticalExam());
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

    public function getPracticalExam($practice_exam_id) {
        $practice_exam_id = (int) $practice_exam_id;
        $rowset = $this->tableGateway->select(array('practice_exam_id' => $practice_exam_id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $practice_exam_id");
        }
        return $row;
    }

    public function savePracticalExam(PracticalExam $practicalExam) {
        $sample = $practicalExam->Sample_testing_score;
        $direct = $direct_observation_score = (($practicalExam->pre_analytic + $practicalExam->analytic + $practicalExam->post_analytic) / 3);
        if ($sample == 100) {

            $result = ($direct / $sample) * 100;
        } elseif ($direct == 100) {

            $result = ($sample / $direct) * 100;
        } else {
            $result = ($sample / 100) * $direct;
        }

        $data = array(
            'exam_type' => $practicalExam->exam_type,
            'exam_admin_by_id' => $practicalExam->exam_admin_by_id,
            'provider_id' => $practicalExam->provider_id,
            'pre_analytic' => $practicalExam->pre_analytic,
            'analytic' => $practicalExam->analytic,
            'post_analytic' => $practicalExam->post_analytic,
            'Sample_testing_score' => $practicalExam->Sample_testing_score,
            'direct_observation_score' => $direct,
            'practical_total_score' => $result,
            'date' => $practicalExam->date
        );
//        print_r($data);
        $practice_exam_id = (int) $practicalExam->practice_exam_id;
        if ($practice_exam_id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getPracticalExam($practice_exam_id)) {
                $this->tableGateway->update($data, array('practice_exam_id' => $practice_exam_id));
            } else {
                throw new \Exception('Practical Exam id does not exist');
            }
        }
    }

    public function search($motCle) {
        $sqlSelect = $this->tableGateway->getSql()->select();
        $sqlSelect->columns(array('practice_exam_id', 'exam_type', 'exam_admin_by_id', 'provider_id', 'pre_analytic', 'analytic', 'post_analytic', 'Sample_testing_score', 'direct_observation_score', 'practical_total_score', 'date'));
        $sqlSelect->join('provider', 'provider.id = practical_exam.provider_id ', array('last_name', 'first_name', 'middle_name'), 'left')
                ->join('exam_admin_by', ' exam_admin_by.exam_admin_by_id = practical_exam.exam_admin_by_id ', array('admin_last_name', 'admin_first_name', 'admin_middle_name',), 'left');
        $sqlSelect->where->like('last_name', '%' . $motCle . '%');
        $sqlSelect->where->OR->like('first_name', '%' . $motCle . '%');
        $sqlSelect->where->OR->like('middle_name', '%' . $motCle . '%');
        $sqlSelect->where->OR->like('admin_last_name', '%' . $motCle . '%');
        $sqlSelect->where->OR->like('admin_first_name', '%' . $motCle . '%');
        $sqlSelect->where->OR->like('admin_middle_name', '%' . $motCle . '%');
        $sqlSelect->order('practice_exam_id desc');
        ?> 
        <pre><?php // print_r($sqlSelect) ;        ?></pre> <?php
        $resultSet = $this->tableGateway->selectWith($sqlSelect);
        return $resultSet;
    }

    /**
     * get the last practical exam last id insert
     * @return $last_id
     */
    public function last_id() {
        $last_id = $this->tableGateway->lastInsertValue;
        return $last_id;
    }

    /**
     * insert practical_exam id to examination table
     * @param type $last_id
     */
    public function insertToExamination($last_id) {
        $db = $this->tableGateway->getAdapter();

        $sql1 = 'select provider_id from practical_exam where practice_exam_id=' . $last_id;
        $statement = $db->query($sql1);
        $result = $statement->execute();
        foreach ($result as $res) {
            $provider = $res['provider_id'];
        }

        $sql2 = 'SELECT count(*) as nombre FROM examination WHERE provider=' . $provider . ' and id_written_exam is not null and practical_exam_id is null';
        $statement2 = $db->query($sql2);
//        die($sql2);
        $result2 = $statement2->execute();
        foreach ($result2 as $res2) {
            $nombre = $res2['nombre'];
        }

        if ($nombre == 0) {
            $sql2 = 'insert into examination (practical_exam_id,provider) values (' . $last_id . ',' . $provider . ')';
            $statement2 = $db->query($sql2);
            $result2 = $statement2->execute();
        } else {
            $sql = 'UPDATE examination SET practical_exam_id=' . $last_id . ' WHERE provider=' . $provider;
            $db->getDriver()->getConnection()->execute($sql);
        }
    }
    
    /**
     * insert written and practical exam id to examination
     * @param type $written
     * @param type $last_id
     */
    public function examination($written,$last_id){
        $db = $this->tableGateway->getAdapter();
        $sql1 = 'select provider_id from practical_exam where practice_exam_id=' . $last_id;
        $statement = $db->query($sql1);
        $result = $statement->execute();
        foreach ($result as $res) {
            $provider = $res['provider_id'];
        }
        
        $sql2 = 'insert into examination (provider,id_written_exam,practical_exam_id) values (' .  $provider .','.$written . ',' .$last_id. ')';
        $statement2 = $db->query($sql2);
        $result2 = $statement2->execute();
    }
    
    

}

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
            $sqlSelect->columns(array('practice_exam_id', 'exam_type', 'exam_admin', 'provider_id', 'Sample_testing_score', 'direct_observation_score', 'practical_total_score', 'date'));
            $sqlSelect->join('provider', ' provider.id = practical_exam.provider_id ', array('last_name', 'first_name', 'middle_name'), 'left')
                    ->where(array('display' => 'yes'));
            $sqlSelect->order('practice_exam_id desc');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new PracticalExam());
            $paginatorAdapter = new DbSelect(
                    $sqlSelect, $this->tableGateway->getAdapter(), $resultSetPrototype
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
        $direct = $practicalExam->direct_observation_score;
        if ($sample == 100) {

            $result = ($direct / $sample) * 100;
        } elseif ($direct == 100) {

            $result = ($sample / $direct) * 100;
        } else {
            $result = ($sample / 100) * $direct;
        }

        $data = array(
            'exam_type' => $practicalExam->exam_type,
            'exam_admin' => $practicalExam->exam_admin,
            'provider_id' => $practicalExam->provider_id,
            'Sample_testing_score' => $sample,
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

    public function search($motCle, $paginated = false) {
        if ($paginated) {

            $sqlSelect = $this->tableGateway->getSql()->select();
            $sqlSelect->columns(array('practice_exam_id', 'exam_type', 'exam_admin', 'provider_id', 'Sample_testing_score', 'direct_observation_score', 'practical_total_score', 'date'));
            $sqlSelect->join('provider', 'provider.id = practical_exam.provider_id ', array('last_name', 'first_name', 'middle_name'), 'left');
            $sqlSelect->where->like('last_name', '%' . $motCle . '%');
            $sqlSelect->where->OR->like('first_name', '%' . $motCle . '%');
            $sqlSelect->where->OR->like('middle_name', '%' . $motCle . '%');
            $sqlSelect->order('practice_exam_id desc');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new PracticalExam());
            $paginatorAdapter = new DbSelect(
                    $sqlSelect, $this->tableGateway->getAdapter(), $resultSetPrototype
            );
            $paginator = new Paginator($paginatorAdapter);
            return $paginator;
        }


        $resultSet = $this->tableGateway->selectWith($sqlSelect);
        return $resultSet;
    }

    /**
     * get the last practical exam last id insert
     * @return $last_id integer
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
        $statement1 = $db->query($sql1);
        $result1 = $statement1->execute();
        foreach ($result1 as $res1) {
            $provider = $res1['provider_id'];
        }

        $sql2 = 'SELECT count(*) as nombre FROM examination WHERE provider=' . $provider . ' and id_written_exam is not null and practical_exam_id is null';
        $statement2 = $db->query($sql2);
//        die($sql2);
        $result2 = $statement2->execute();
        foreach ($result2 as $res2) {
            $nombre = $res2['nombre'];
        }

        if ($nombre == 0) {
            $sql3 = 'insert into examination (practical_exam_id,provider) values (' . $last_id . ',' . $provider . ')';
            $statement3 = $db->query($sql3);
            $result3 = $statement3->execute();
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
    public function examination($written, $last_id) {
        $db = $this->tableGateway->getAdapter();
        $sql1 = 'select provider_id from practical_exam where practice_exam_id=' . $last_id;
        $statement = $db->query($sql1);
        $result = $statement->execute();
        foreach ($result as $res) {
            $provider = $res['provider_id'];
        }

        $sql2 = 'insert into examination (provider,id_written_exam,practical_exam_id) values (' . $provider . ',' . $written . ',' . $last_id . ')';
        $statement2 = $db->query($sql2);
        $result2 = $statement2->execute();
    }

    /**
     * count the number of written exam with tha same id ad set the number of attempt for another attempt
     * @param type $written
     * @return type integer
     */
    public function countWritten($written) {
        $db = $this->tableGateway->getAdapter();
        $sql3 = 'SELECT count(*) as nombre FROM examination WHERE id_written_exam=' . $written;
//        die($sql3);
        $statement3 = $db->query($sql3);
        $result3 = $statement3->execute();
        foreach ($result3 as $res3) {
            $nombre = $res3['nombre'];
        }
//        die($nombre);
        return $nombre;
    }

    public function getProviderName($written) {
        $db = $this->tableGateway->getAdapter();
        $sql1 = 'select id, last_name, first_name, middle_name from provider , written_exam where provider.id=written_exam.provider_id and id_written_exam=' . $written;
        $statement = $db->query($sql1);
        $result = $statement->execute();
        $selectData = array();

        foreach ($result as $res) {
            $selectData[$res['id']] = $res['last_name'] . ' ' . $res['first_name'] . ' ' . $res['middle_name'];
        }
        return $selectData;
    }

}

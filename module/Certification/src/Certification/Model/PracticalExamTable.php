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
        $sqlSelect->join('provider', ' provider.certification_id = practical_exam.provider_id ', array('last_name', 'first_name', 'middle_name'), 'left')
                ->join('exam_admin_by', ' exam_admin_by.exam_admin_by_id = practical_exam.exam_admin_by_id ', array('admin_last_name', 'admin_first_name', 'admin_middle_name',), 'left');
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
        $data = array(
            'exam_type' => $practicalExam->exam_type,
            'exam_admin_by_id' => $practicalExam->exam_admin_by_id,
            'provider_id' => $practicalExam->provider_id,
            'pre_analytic' => $practicalExam->pre_analytic,
            'analytic' => $practicalExam->analytic,
            'post_analytic' => $practicalExam->post_analytic,
            'Sample_testing_score' => $practicalExam->Sample_testing_score,
            'direct_observation_score' => $direct_observation_score = (($practicalExam->pre_analytic + $practicalExam->analytic + $practicalExam->post_analytic) / 3),
            'practical_total_score' => (($direct_observation_score / $practicalExam->Sample_testing_score) * $practicalExam->Sample_testing_score),
            'date' => $practicalExam->date
        );
        print_r($data);
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

}

<?php

namespace Certification\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

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
            $sqlSelect->join('provider', ' provider.certification_id= written_exam.provider_id ', array('last_name', 'first_name'), 'left')
                    ->join('exam_admin_by', ' exam_admin_by.exam_admin_by_id= written_exam.exam_admin_by_id ', array('admin_last_name', 'admin_first_name'), 'left');

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
?>  <?php // print_r($data);   ?>   
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

}

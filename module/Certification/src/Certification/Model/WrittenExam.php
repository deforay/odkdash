<?php

namespace Certification\Model;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilterAwareInterface;


class WrittenExam implements InputFilterAwareInterface {

    public $id_written_exam;
    public $exam_type;
    public $provider_id;
    public $exam_admin_by_id;
    public $date;
    public $qa_point;
    public $rt_point;
    public $safety_point;
    public $specimen_point;
    public $testing_algo_point;
    public $report_keeping_point;
    public $EQA_PT_points;
    public $ethics_point;
    public $total_points;
    public $final_score;


    protected $inputFilter;

    public function exchangeArray($data) {
        $this->id_written_exam = (!empty($data['id_written_exam'])) ? $data['id_written_exam'] : null;
        $this->exam_type = (!empty($data['exam_type'])) ? $data['exam_type'] : null;
        $this->provider_id = (!empty($data['provider_id'])) ? $data['provider_id'] : null;
        $this->exam_admin_by_id = (!empty($data['exam_admin_by_id'])) ? $data['exam_admin_by_id'] : null;
        $this->date = (!empty($data['date'])) ? $data['date'] : null;
        $this->qa_point = (!empty($data['qa_point'])) ? $data['qa_point'] : null;
        $this->rt_point = (!empty($data['rt_point'])) ? $data['rt_point'] : null;
        $this->safety_point = (!empty($data['safety_point'])) ? $data['safety_point'] : null;
        $this->specimen_point = (!empty($data['specimen_point'])) ? $data['specimen_point'] : null;
        $this->testing_algo_point = (!empty($data['testing_algo_point'])) ? $data['testing_algo_point'] : null;
        $this->report_keeping_point = (!empty($data['report_keeping_point'])) ? $data['report_keeping_point'] : null;
        $this->EQA_PT_points = (!empty($data['EQA_PT_points'])) ? $data['EQA_PT_points'] : null;
        $this->ethics_point = (!empty($data['ethics_point'])) ? $data['ethics_point'] : null;
        $this->last_name = (!empty($data['last_name'])) ? $data['last_name'] : null;
        $this->first_name = (!empty($data['first_name'])) ? $data['first_name'] : null;
         $this->middle_name = (!empty($data['middle_name'])) ? $data['middle_name'] : null;
        $this->name_exam_type = (!empty($data['name_exam_type'])) ? $data['name_exam_type'] : null;
        $this->admin_last_name = (!empty($data['admin_last_name'])) ? $data['admin_last_name'] : null;
        $this->admin_first_name = (!empty($data['admin_first_name'])) ? $data['admin_first_name'] : null;
        $this->admin_middle_name = (!empty($data['admin_middle_name'])) ? $data['admin_middle_name'] : null;
         $this->total_points = (!empty($data['total_points'])) ? $data['total_points'] : null;
          $this->final_score = (!empty($data['final_score'])) ? $data['final_score'] : null;
    }

    public function getArrayCopy() {
        return get_object_vars($this);
    }

    public function setInputFilter(InputFilterInterface $inputFilter) {
        throw new \Exception("Not used");
    }

    public function getInputFilter() {

        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $inputFilter->add(array(
                'name' => 'id_written_exam',
                'required' => FALSE,
                'filters' => array(
                    array('name' => 'Int'),
                ),
            ));

            $inputFilter->add(array(
                'name' => 'exam_type',
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                        ),
                    ),
                ),
            ));
            $inputFilter->add(array(
                'name' => 'provider_id',
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                        ),
                    ),
                ),
            ));
            $inputFilter->add(array(
                'name' => 'exam_admin_by_id',
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                        ),
                    ),
                ),
            ));
            $inputFilter->add(array(
                'name' => 'date',
                'required' => false,
            ));
            $inputFilter->add(array(
                'name' => 'qa_point',
                'required' => false,
                'filters' => array(
                    array('name' => 'int'),
                ),
            ));
            $inputFilter->add(array(
                'name' => 'rt_point',
                'required' => false,
                'filters' => array(
                    array('name' => 'int'),
                ),
            ));
            $inputFilter->add(array(
                'name' => 'safety_point',
                'required' => false,
                'filters' => array(
                    array('name' => 'int'),
                ),
            ));
            $inputFilter->add(array(
                'name' => 'specimen_point',
                'required' => false,
                'filters' => array(
                    array('name' => 'int'),
                ),
            ));
            $inputFilter->add(array(
                'name' => 'testing_algo_point',
                'required' => false,
                'filters' => array(
                    array('name' => 'int'),
                ),
            ));
            $inputFilter->add(array(
                'name' => 'report_keeping_point',
                'required' => false,
                'filters' => array(
                    array('name' => 'int'),
                ),
            ));
            $inputFilter->add(array(
                'name' => 'EQA_PT_points',
                'required' => false,
                'filters' => array(
                    array('name' => 'int'),
                ),
            ));
            $inputFilter->add(array(
                'name' => 'ethics_point',
                'required' => false,
                'filters' => array(
                    array('name' => 'int'),
                ),
            ));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

}

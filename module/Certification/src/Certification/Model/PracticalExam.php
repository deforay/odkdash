<?php

namespace Certification\Model;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilterAwareInterface;

class PracticalExam implements InputFilterAwareInterface {

    public $practice_exam_id;
    public $exam_type;
    public $exam_admin_by_id;
    public $provider_id;
    public $pre_analytic;
    public $analytic;
    public $post_analytic;
    public $Sample_testing_score;
    public $date;
    protected $inputFilter;

    public function exchangeArray($data) {

        $this->practice_exam_id = (!empty($data['practice_exam_id'])) ? $data['practice_exam_id'] : null;
        $this->exam_type = (!empty($data['exam_type'])) ? $data['exam_type'] : null;
        $this->exam_admin_by_id = (!empty($data['exam_admin_by_id'])) ? $data['exam_admin_by_id'] : null;
        $this->provider_id = (!empty($data['provider_id'])) ? $data['provider_id'] : null;
        $this->pre_analytic = (!empty($data['pre_analytic'])) ? $data['pre_analytic'] : null;
        $this->analytic = (!empty($data['analytic'])) ? $data['analytic'] : null;
        $this->post_analytic = (!empty($data['post_analytic'])) ? $data['post_analytic'] : null;
        $this->Sample_testing_score = (!empty($data['Sample_testing_score'])) ? $data['Sample_testing_score'] : null;
        $this->date = (!empty($data['date'])) ? $data['date'] : null;
        $this->last_name = (!empty($data['last_name'])) ? $data['last_name'] : null;
        $this->first_name = (!empty($data['first_name'])) ? $data['first_name'] : null;
        $this->middle_name = (!empty($data['middle_name'])) ? $data['middle_name'] : null;
        $this->admin_last_name = (!empty($data['admin_last_name'])) ? $data['admin_last_name'] : null;
        $this->admin_first_name = (!empty($data['admin_first_name'])) ? $data['admin_first_name'] : null;
        $this->admin_middle_name = (!empty($data['admin_middle_name'])) ? $data['admin_middle_name'] : null;
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
                'name' => 'practice_exam_id',
                'required' => false,
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
                'name' => 'pre_analytic',
                'required' => false,
                'filters' => array(
                    array('name' => 'Int'),
                ),
            ));

            $inputFilter->add(array(
                'name' => 'analytic',
                'required' => false,
                'filters' => array(
                    array('name' => 'Int'),
                ),
            ));


            $inputFilter->add(array(
                'name' => 'post_analytic',
                'required' => false,
                'filters' => array(
                    array('name' => 'Int'),
                ),
            ));


            $inputFilter->add(array(
                'name' => 'Sample_testing_score',
                'required' => false,
                'filters' => array(
                    array('name' => 'int'),
                ),
            ));

            $inputFilter->add(array(
                'name' => 'date',
                'required' => false,
            ));


            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

}

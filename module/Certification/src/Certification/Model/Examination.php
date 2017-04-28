<?php

namespace Certification\Model;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Examination {

    public $id;
    public $provider;
    public $id_written_exam;
    public $practical_exam_id;
    protected $inputFilter;

    public function exchangeArray($data) {
        $this->id = (!empty($data['id'])) ? $data['id'] : null;
        $this->provider = (!empty($data['provider'])) ? $data['provider'] : null;
        $this->practical_exam_id = (!empty($data['practical_exam_id'])) ? $data['practical_exam_id'] : null;
        $this->id_written_exam = (!empty($data['id_written_exam'])) ? $data['id_written_exam'] : null;

        $this->provider_id = (!empty($data['provider_id'])) ? $data['provider_id'] : null;
        $this->certification_id = (!empty($data['certification_id'])) ? $data['certification_id'] : null;
        $this->last_name = (!empty($data['last_name'])) ? $data['last_name'] : null;
        $this->first_name = (!empty($data['first_name'])) ? $data['first_name'] : null;
        $this->middle_name = (!empty($data['middle_name'])) ? $data['middle_name'] : null;

        $this->exam_type = (!empty($data['exam_type'])) ? $data['exam_type'] : null;
        $this->final_score = (!empty($data['final_score'])) ? $data['final_score'] : null;

        $this->practical_total_score = (!empty($data['practical_total_score'])) ? $data['practical_total_score'] : null;
    }

    public function setInputFilter(InputFilterInterface $inputFilter) {
        throw new \Exception("Not used");
    }

    public function getInputFilter() {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $inputFilter->add(array(
                'name' => 'id',
                'required' => FALSE,
                'filters' => array(
                    array('name' => 'Int'),
                ),
            ));
            $inputFilter->add(array(
                'name' => 'provider',
                'required' => FALSE,
                'filters' => array(
                    array('name' => 'Int'),
                    
                ),
                
            ));

            $inputFilter->add(array(
                'name' => 'id_written_exam',
                'required' => FALSE,
                'filters' => array(
                    array('name' => 'Int'),
                    
                ),
            ));

            $inputFilter->add(array(
                'name' => 'practical_exam_id',
                'required' => FALSE,
                'filters' => array(
                    array('name' => 'Int'),
                    
                ),
            ));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

}

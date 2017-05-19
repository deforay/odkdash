<?php

namespace Certification\Model;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;

class Training {

    public $training_id;
    public $Provider_id;
    public $Start_date;
    public $end_date;
    public $type_of_training;
    public $training_organization_id;
    public $trainer_id;
    public $score;
    public $Pass_fail;
    public $training_certificate;
    public $Comments;
    protected $inputFilter;

    public function exchangeArray($data) {
        $this->training_id = (!empty($data['training_id'])) ? $data['training_id'] : null;
        $this->Provider_id = (!empty($data['Provider_id'])) ? $data['Provider_id'] : null;
        $this->Start_date = (!empty($data['Start_date'])) ? $data['Start_date'] : null;
        $this->end_date = (!empty($data['end_date'])) ? $data['end_date'] : null;
        $this->type_of_training = (!empty($data['type_of_training'])) ? $data['type_of_training'] : null;
        $this->training_organization_id = (!empty($data['training_organization_id'])) ? $data['training_organization_id'] : null;
        $this->trainer_id = (!empty($data['trainer_id'])) ? $data['trainer_id'] : null;
        $this->score = (!empty($data['score'])) ? $data['score'] : null;
        $this->Pass_fail = (!empty($data['Pass_fail'])) ? $data['Pass_fail'] : null;
        $this->training_certificate = (!empty($data['training_certificate'])) ? $data['training_certificate'] : null;
        $this->Comments = (!empty($data['Comments'])) ? $data['Comments'] : null;
        
        $this->last_name = (!empty($data['last_name'])) ? $data['last_name'] : null;
        $this->first_name = (!empty($data['first_name'])) ? $data['first_name'] : null;
        $this->middle_name = (!empty($data['middle_name'])) ? $data['middle_name'] : null;
//        provider_id of provider table
        $this->professional_reg_no = (!empty($data['professional_reg_no'])) ? $data['professional_reg_no'] : null;
        $this->certification_id = (!empty($data['certification_id'])) ? $data['certification_id'] : null;
        $this->certification_reg_no = (!empty($data['certification_reg_no'])) ? $data['certification_reg_no'] : null;

        $this->training_organization_name = (!empty($data['training_organization_name'])) ? $data['training_organization_name'] : null;
        $this->trainer_last_name = (!empty($data['trainer_last_name'])) ? $data['trainer_last_name'] : null;
        $this->trainer_first_name = (!empty($data['trainer_first_name'])) ? $data['trainer_first_name'] : null;
        $this->type_organization = (!empty($data['type_organization'])) ? $data['type_organization'] : null;
        
    }

    public function getArrayCopy() {
        return get_object_vars($this);
    }

//
    public function setInputFilter(InputFilterInterface $inputFilter) {
        throw new \Exception("Not used");
    }

    public function getInputFilter() {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $inputFilter->add(array(
                'name' => 'training_id',
                'required' => FALSE,
                'filters' => array(
                    array('name' => 'Int'),
                ),
            ));

            $inputFilter->add(array(
                'name' => 'Provider_id',
                'required' => FALSE,
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
                'name' => 'Start_date',
                'required' => FALSE,
            ));

            $inputFilter->add(array(
                'name' => 'end_date',
                'required' => FALSE,
            ));

            $inputFilter->add(array(
                'name' => 'type_of_training',
                'required' => FALSE,
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
                'name' => 'training_organization_id',
                'required' => FALSE,
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
                'name' => 'trainer_id',
                'required' => FALSE,
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
                'name' => 'score',
                'required' => FALSE,
                'filters' => array(
                    array('name' => 'Int'),
                ),
            ));

            $inputFilter->add(array(
                'name' => 'Pass_fail',
                'required' => FALSE,
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
                'name' => 'training_certificate',
                'required' => FALSE,
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
                'name' => 'Comments',
                'required' => FALSE,
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
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

}

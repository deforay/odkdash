<?php

namespace Certification\Model;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilterAwareInterface;

class Certification implements InputFilterAwareInterface {

    public $id;
    public $certification_id;
    public $final_decision;
    public $certification_issuer_id;
    public $date_certificate_issued;
    public $date_certificate_sent;
    public $certification_type;
    public $issued;
    protected $inputFilter;

    public function exchangeArray($data) {

        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->certification_id = (isset($data['certification_id'])) ? $data['certification_id'] : null;
        $this->final_decision = (isset($data['final_decision'])) ? $data['final_decision'] : null;
        $this->certification_issuer_id = (isset($data['certification_issuer_id'])) ? $data['certification_issuer_id'] : null;
        $this->date_certificate_issued = (isset($data['date_certificate_issued'])) ? $data['date_certificate_issued'] : null;
        $this->date_certificate_sent = (isset($data['date_certificate_sent'])) ? $data['date_certificate_sent'] : null;
        $this->certification_type = (isset($data['certification_type'])) ? $data['certification_type'] : null;
        $this->issued = (isset($data['issued'])) ? $data['issued'] : null;
        $this->last_name = (isset($data['last_name'])) ? $data['last_name'] : null;
        $this->first_name = (isset($data['first_name'])) ? $data['first_name'] : null;
        $this->middle_name = (isset($data['middle_name'])) ? $data['middle_name'] : null;
        $this->issuer_last_name = (isset($data['issuer_last_name'])) ? $data['issuer_last_name'] : null;
        $this->issuer_first_name = (isset($data['issuer_first_name'])) ? $data['issuer_first_name'] : null;
        $this->issuer_middle_name = (isset($data['issuer_middle_name'])) ? $data['issuer_middle_name'] : null;
        
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
                'name' => 'id',
                'required' => FALSE,
                'filters' => array(
                    array('name' => 'Int'),
                ),
            ));

            $inputFilter->add(array(
                'name' => 'certification_id',
                'required' => FALSE,
               
            ));
            
            
            $inputFilter->add(array(
                'name' => 'final_decision',
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
                'name' => 'certification_issuer_id',
                'required' => FALSE,
                'allow-empty'=>TRUE,
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
                'name' => 'date_certificate_issued',
                'required' => FALSE,
            ));

            $inputFilter->add(array(
                'name' => 'date_certificate_sent',
                'required' => FALSE,
            ));

            
            
            $inputFilter->add(array(
                'name' => 'certification_type',
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
                'name' => 'issued',
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
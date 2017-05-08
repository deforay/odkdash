<?php

namespace Certification\Model;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Certification {

    public $id;
    public $examination;
    public $final_decision;
    public $certification_issuer_id;
    public $date_certificate_issued;
    public $date_certificate_sent;
    public $certification_type;
    public $issued;
    protected $inputFilter;

    public function exchangeArray($data) {
        $this->id = (!empty($data['id'])) ? $data['id'] : null;
        $this->examination = (!empty($data['examination'])) ? $data['examination'] : null;
        $this->final_decision = (!empty($data['final_decision'])) ? $data['final_decision'] : null;
        $this->certification_issuer_id = (!empty($data['certification_issuer_id'])) ? $data['certification_issuer_id'] : null;
        $this->date_certificate_issued = (!empty($data['date_certificate_issued'])) ? $data['date_certificate_issued'] : null;
        $this->date_certificate_sent = (!empty($data['date_certificate_sent'])) ? $data['date_certificate_sent'] : null;
        $this->certification_type = (!empty($data['certification_type'])) ? $data['certification_type'] : null;
        $this->issued = (!empty($data['issued'])) ? $data['issued'] : 'no';
        $this->issuer_last_name = (!empty($data['issuer_last_name'])) ? $data['issuer_last_name'] : null;
        $this->issuer_first_name = (!empty($data['issuer_first_name'])) ? $data['issuer_first_name'] : null;
        $this->issuer_middle_name = (!empty($data['issuer_middle_name'])) ? $data['issuer_middle_name'] : null;


        $this->certification_id = (!empty($data['certification_id'])) ? $data['certification_id'] : null;
        $this->last_name = (!empty($data['last_name'])) ? $data['last_name'] : null;
        $this->first_name = (!empty($data['first_name'])) ? $data['first_name'] : null;
         $this->middle_name = (!empty($data['middle_name'])) ? $data['middle_name'] : null;
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
                'required' => false,
                'filters' => array(
                    array('name' => 'Int'),
                ),
            ));

            $inputFilter->add(array(
                'name' => 'examination',
                'required' => false,
                'filters' => array(
                    array('name' => 'int'),
                ),
            ));

            $inputFilter->add(array(
                'name' => 'final_decision',
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
                'name' => 'certification_issuer_id',
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
                'name' => 'date_certificate_issued',
                'required' => false,
            ));

            $inputFilter->add(array(
                'name' => 'date_certificate_sent',
                'required' => false,
            ));

            $inputFilter->add(array(
                'name' => 'certification_type',
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
                'name' => 'issued',
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

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

}

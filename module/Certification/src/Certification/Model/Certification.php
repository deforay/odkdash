<?php
namespace Certification\Model;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilterAwareInterface;

class Certification implements InputFilterAwareInterface {

public $certification_id;
public $provider_id;
public $score;
public $final_decision;
public $certificate_issued_by_id;
public $date_certificate_issued;
public $date_certificate_sent;
public $certification_type;
public $issued;
protected $inputFilter;

    public function exchangeArray($data) {
       
$this->certification_id = (isset($data['certification_id'])) ? $data['certification_id'] : null;
$this->provider_id = (isset($data['provider_id'])) ? $data['provider_id'] : null;
$this->score = (isset($data['score'])) ? $data['score'] : null;
$this->final_decision = (isset($data['final_decision'])) ? $data['final_decision'] : null;
$this->certificate_issued_by_id = (isset($data['certificate_issued_by_id'])) ? $data['certificate_issued_by_id'] : null;
$this->date_certificate_issued = (isset($data['date_certificate_issued'])) ? $data['date_certificate_issued'] : null;
$this->date_certificate_sent = (isset($data['date_certificate_sent'])) ? $data['date_certificate_sent'] : null;
$this->certification_type = (isset($data['certification_type'])) ? $data['certification_type'] : null;
$this->issued = (isset($data['issued'])) ? $data['issued'] : null;
$this->last_name = (isset($data['last_name'])) ? $data['last_name'] : null;
$this->first_name = (isset($data['first_name'])) ? $data['first_name'] : null;
$this->middle_name = (isset($data['middle_name'])) ? $data['middle_name'] : null;
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
                'name' => 'certification_id',
                'required' => false,
                'filters' => array(
                    array('name' => 'Int'),
                ),
            ));

            $inputFilter->add(array(
                'name' => 'provider_id',
                'required' => false,
                'filters' => array(
                    array('name' => 'int'),
                ),
            ));
            
            $inputFilter->add(array(
                'name' => 'score',
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
                        'encoding' => 'UTF-8',
                        
                    ),
                ),
            ));

            
             $inputFilter->add(array(
                'name' => 'certificate_issued_by_id',
                'required' => false,
                'filters' => array(
                    array('name' => 'int'),
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
                        'encoding' => 'UTF-8',
                        
                    ),
                ),
            ));




            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
        
    }



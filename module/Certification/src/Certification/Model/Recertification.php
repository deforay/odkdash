<?php

namespace Certification\Model;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Recertification {

    public $recertification_id;
    public $due_date;
    public $certification_id;
    public $reminder_type;
    public $reminder_sent_to;
    public $name_of_recipient;
    public $date_reminder_sent;
    protected $inputFilter;

    public function exchangeArray($data) {
        $this->recertification_id = (!empty($data['recertification_id'])) ? $data['recertification_id'] : null;
        $this->due_date = (!empty($data['due_date'])) ? $data['due_date'] : null;
        $this->certification_id = (!empty($data['certification_id'])) ? $data['certification_id'] : null;
        $this->reminder_type = (!empty($data['reminder_type'])) ? $data['reminder_type'] : null;
        $this->reminder_sent_to = (!empty($data['reminder_sent_to'])) ? $data['reminder_sent_to'] : null;
        $this->name_of_recipient = (!empty($data['name_of_recipient'])) ? $data['name_of_recipient'] : null;
        $this->date_reminder_sent = (!empty($data['date_reminder_sent'])) ? $data['date_reminder_sent'] : null;
    }

    public function getArrayCopy()
     {
         return get_object_vars($this);
     }

    public function setInputFilter(InputFilterInterface $inputFilter) {
        throw new \Exception("Not used");
    }

    public function getInputFilter() {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $inputFilter->add(array(
                'name' => 'recertification_id',
                'required' => FALSE,
                'filters' => array(
                    array('name' => 'Int'),
                ),
            ));

            $inputFilter->add(array(
                'name' => 'due_date',
                'required' => false,
            ));

            $inputFilter->add(array(
                'name' => 'certification_id',
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
                'name' => 'reminder_type',
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
                'name' => 'reminder_sent_to',
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
                'name' => 'name_of_recipient',
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
                'name' => 'date_reminder_sent',
                'required' => FALSE,
                
            ));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

}

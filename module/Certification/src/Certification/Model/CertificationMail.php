<?php

namespace Certification\Model;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class CertificationMail {

    public $mail_id;
    public $from_full_name;
    public $from_mail;
    public $to_email;
    public $cc;
    public $bcc;
    public $subject;
    public $message;
    public $status;
    protected $inputFilter;

    public function exchangeArray($data) {
        $this->mail_id = (!empty($data['mail_id'])) ? $data['mail_id'] : null;
        $this->from_full_name = (!empty($data['from_full_name'])) ? $data['from_full_name'] : null;
        $this->from_mail = (!empty($data['from_mail'])) ? $data['from_mail'] : null;
        $this->to_email = (!empty($data['to_email'])) ? $data['to_email'] : null;
        $this->cc = (!empty($data['cc'])) ? $data['cc'] : null;
        $this->bcc = (!empty($data['bcc'])) ? $data['bcc'] : null;
        $this->subject = (!empty($data['subject'])) ? $data['subject'] : null;
        $this->message = (!empty($data['message'])) ? $data['message'] : null;
        $this->status = (!empty($data['status'])) ? $data['status'] : null;
    }

    public function setInputFilter(InputFilterInterface $inputFilter) {
        throw new \Exception("Not used");
    }

    public function getInputFilter() {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $inputFilter->add(array(
                'name' => 'mail_id',
                'required' => FALSE,
                'filters' => array(
                    array('name' => 'Int'),
                ),
            ));

            $inputFilter->add(array(
                'name' => 'from_full_name',
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
                'name' => 'from_mail',
                'required' => FALSE,
                'validators' => array(
                    array(
                        'name' => 'EmailAddress'
                    ),
                ),
            ));

            $inputFilter->add(array(
                'name' => 'to_email',
                'required'=>FALSE,
                'validators' => array(
                    array(
                        'name' => 'EmailAddress'
                    ),
                ),
            ));

            $inputFilter->add(array(
                'name' => 'cc',
                'required' => FALSE,
                'validators' => array(
                    array(
                        'name' => 'EmailAddress'
                    ),
                ),
            ));
            $inputFilter->add(array(
                'name' => 'bcc',
                'required' => FALSE,
                'validators' => array(
                    array(
                        'name' => 'EmailAddress'
                    ),
                ),
            ));

            $inputFilter->add(array(
                'name' => 'subject',
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
                'name' => 'message',
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
                'name' => 'status',
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

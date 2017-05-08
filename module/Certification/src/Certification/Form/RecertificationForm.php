<?php

namespace Certification\Form;

use Zend\Form\Form;

class RecertificationForm extends Form {

    public function __construct($name = null) {
        // we want to ignore the name passed
        parent::__construct('recertification');

        $this->add(array(
            'name' => 'recertification_id',
            'type' => 'Hidden',
        ));
        $this->add(array(
            'name' => 'due_date',
            'type' => 'Text',
            'attributes' => [
                'id' => 'date',
                'type' => 'text'
            ],
            'options' => array(
                'label' => 'Due Date'
            )
        ));
        $this->add(array(
            'name' => 'certification_id',
            'type' => 'Text',
            'options' => array(
                'label' => 'Certification ID',
            ),
        ));
        $this->add(array(
            'name' => 'reminder_type',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Type of Reminder',
                'empty_option' => 'Please choose a Type',
                'value_options' => array(
                    'Phone' => 'Phone',
                    'Email' => 'Email'
                ),
            ),
        ));
        $this->add(array(
            'name' => 'reminder_sent_to',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Reminder Sent To',
                'empty_option' => 'Please choose a Reminder',
                'value_options' => array(
                    'District focal person' => 'District focal person',
                    'HTS focal person' => 'HTS focal person',
                    'Implementing partner' => 'Implementing partner',
                    'In charge' => 'In charge',
                    'Provider' => 'Provider',
                    'QA/QI focal person' => 'QA/QI focal person'
                ),
            ),
        ));
        $this->add(array(
            'name' => 'name_of_recipient',
            'type' => 'Text',
            'options' => array(
                'label' => 'Name of Recipient',
            ),
        ));
        $this->add(array(
            'name' => 'date_reminder_sent',
            'type' => 'Text',
            'attributes' => [
                'id' => 'date',
                'type' => 'text'
            ],
            'options' => array(
                'label' => 'Date Reminder Sent'
            )
        ));
        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Go',
                'id' => 'submitbutton',
            ),
        ));
    }

}

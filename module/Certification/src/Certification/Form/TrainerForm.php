<?php

namespace Certification\Form;

use Zend\Form\Form;

class TrainerForm extends Form {

    public function __construct($name = null) {
        parent::__construct('trainer');

        $this->add(array(
            'name' => 'trainer_id',
            'type' => 'Hidden',
        ));
        $this->add(array(
            'name' => 'trainer_last_name',
            'type' => 'Text',
            'options' => array(
                'label' => 'Last name',
            ),
        ));
        $this->add(array(
            'name' => 'trainer_first_name',
            'type' => 'Text',
            'options' => array(
                'label' => 'First Name',
            ),
        ));
        
        $this->add(array(
            'name' => 'trainer_middle_name',
            'type' => 'Text',
            'options' => array(
                'label' => 'First Name',
            ),
        ));
        
        $this->add(array(
            'name' => 'region',
            'type' => 'Text',
            'options' => array(
                'label' => 'Region',
            ),
        ));
        $this->add(array(
            'name' => 'district',
            'type' => 'Text',
            'options' => array(
                'label' => 'District',
            ),
        ));
        $this->add(array(
            'name' => 'phone',
            'type' => 'Number',
            'options' => array(
                'label' => 'Phone',
            ),
            'attributes' => array(
             'min' => '0',
                )
        ));
        $this->add(array(
            'name' => 'email',
            'type' => 'Text',
            'options' => array(
                'label' => 'Email',
            ),
        ));
        $this->add(array(
            'name' => 'job_address',
            'type' => 'Text',
            'options' => array(
                'label' => 'Job address',
            ),
        ));

        $this->add(array(
            'name' => 'prefered_contact_method',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Prefered Contact Methode',
                'empty_option' => 'Please choose a methode',
                'value_options' => array(
                   'Phone' => 'Phone',
                    'Email' => 'Email'
                )
            ),
        ));

        $this->add(array(
            'name' => 'current_jod',
            'type' => 'Text',
            'options' => array(
                'label' => 'Current job',
            ),
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

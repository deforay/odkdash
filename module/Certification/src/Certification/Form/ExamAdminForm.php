<?php
namespace Certification\Form;

use Zend\Form\Form;

class ExamAdminForm extends Form {

    public function __construct($name = null) {


        parent::__construct('exam_admin_by');

        $this->add(array(
            'name' => 'exam_admin_by_id',
            'type' => 'Hidden',
        ));
        $this->add(array(
            'name' => 'admin_last_name',
            'type' => 'Text',
            'options' => array(
                'label' => 'Admin Last Name',
            ),
        ));
        $this->add(array(
            'name' => 'admin_first_name',
            'type' => 'Text',
            'options' => array(
                'label' => 'Admin First Name',
            ),
        ));
        $this->add(array(
            'name' => 'admin_middle_name',
            'type' => 'Text',
            'options' => array(
                'label' => 'Admin Middle Name',
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
            'name' => 'region',
            'type' => 'Text',
            'options' => array(
                'label' => 'Region',
            ),
        ));
        $this->add(array(
            'name' => 'email',
            'type' => 'Text',
            'options' => array(
                'label' => 'E-mail',
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
            'name' => 'prefered_contact_method',
            'type' => 'Zend\Form\Element\Select',
           'options' => array(
                'label' => 'prefered_contact_method',
                'empty_option' => 'Please choose a methode',
                'value_options' => array(
                    'Phone' => 'Phone',
                    'Email' => 'Email'
                )
            ),
        ));

        $this->add(array(
            'name' => 'current_job',
            'type' => 'Text',
            'options' => array(
                'label' => 'Current Job',
            ),
        ));
        $this->add(array(
            'name' => 'job_address',
            'type' => 'Text',
            'options' => array(
                'label' => 'Job Address',
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

<?php

namespace Certification\Form;

use Zend\Form\Form;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Adapter;

class WrittenExamForm extends Form {

    protected $adapter;

    public function __construct(AdapterInterface $dbAdapter) {

        $this->adapter = $dbAdapter;

        parent::__construct("written_exam");
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'id_written_exam',
            'type' => 'Hidden',
        ));
        $this->add(array(
            'name' => 'exam_type',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Number of Attempts',
                'empty_option' => 'Please choose a Number of Attempts',
                'value_options' => array(
                    '1st attempt' => '1st attempt',
                    '2nd attempt' => '2nd attempt',
                    '3rd attempt' => '3rd attempt'
                )
            ),
        ));
        $this->add(array(
            'name' => 'provider_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Provider',
                'empty_option' => 'Please choose a Provider',
                'value_options' => $this->getListProvider(),
            ),
        ));

        $this->add(array(
            'name' => 'exam_admin_by_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Exam administered by',
                'empty_option' => 'Please choose an exam administrator',
                'value_options' => $this->getListExamAdmin()
            ),
        ));
        $this->add(array(
            'name' => 'date',
            'type' => 'Text',
            'attributes' => [
                'id' => 'date',
                'type' => 'text',
            ],
            'options' => array(
                'label' => 'Date',
            ),
        ));

        $this->add(array(
            'name' => 'qa_point',
            'type' => 'Number',
            'options' => array(
                'label' => '1.QA (points)',
            ),
            'attributes' => array(
             'min' => '0',
                )
        ));
        $this->add(array(
            'name' => 'rt_point',
            'type' => 'Number',
            'options' => array(
                'label' => '2.RT (points)',
            ),
            'attributes' => array(
             'min' => '0',
                )
        ));
        $this->add(array(
            'name' => 'safety_point',
            'type' => 'Number',
            'options' => array(
                'label' => '3.Safety (points)',
            ),
            'attributes' => array(
             'min' => '0',
                )
        ));

        $this->add(array(
            'name' => 'specimen_point',
            'type' => 'Number',
            'options' => array(
                'label' => '4.Specimen collection (points)',
            ),
            'attributes' => array(
             'min' => '0',
                )
        ));

        $this->add(array(
            'name' => 'testing_algo_point',
            'type' => 'Number',
            'options' => array(
                'label' => '5.Testing algorithm (points)',
            ),
            'attributes' => array(
             'min' => '0',
                )
        ));
        $this->add(array(
            'name' => 'report_keeping_point',
            'type' => 'Number',
            'options' => array(
                'label' => '6.Record keeping (points)',
            ),
            'attributes' => array(
             'min' => '0',
                )
        ));
        $this->add(array(
            'name' => 'EQA_PT_points',
            'type' => 'Number',
            'options' => array(
                'label' => '7. EQA/PT (points)',
            ),
            'attributes' => array(
             'min' => '0',
                )
        ));

        $this->add(array(
            'name' => 'ethics_point',
            'type' => 'Number',
            'options' => array(
                'label' => '8.Ethics (points)',
            ),
            'attributes' => array(
             'min' => '0',
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
        
        $this->add(array(
            'name' => 'practical',
            'type' => 'hidden',
            ));
    }
    

    public function getListProvider() {
        $dbAdapter = $this->adapter;
        $sql = 'SELECT id,certification_id,last_name,first_name,middle_name FROM provider order by last_name asc ';
        $statement = $dbAdapter->query($sql);
        $result = $statement->execute();

        $selectData = array();

        foreach ($result as $res) {
            $selectData[$res['id']] = $res['last_name'] . ' ' . $res['first_name']. ' ' . $res['middle_name'];
        }
        return $selectData;
    }
    
     public function getListExamAdmin() {
        $dbAdapter = $this->adapter;
        $sql = 'SELECT exam_admin_by_id, admin_last_name, admin_first_name, admin_middle_name, district, region, email, phone, prefered_contact_method, current_job, job_address FROM exam_admin_by order by admin_last_name asc ';
        $statement = $dbAdapter->query($sql);
        $result = $statement->execute();

        $selectData = array();

        foreach ($result as $res) {
            $selectData[$res['exam_admin_by_id']] = $res['admin_last_name'] . ' ' . $res['admin_first_name']. ' ' . $res['admin_middle_name'];
        }
        return $selectData;
    }


    
        }

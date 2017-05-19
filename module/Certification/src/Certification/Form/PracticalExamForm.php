<?php

namespace Certification\Form;

use Zend\Form\Form;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Adapter;

class PracticalExamForm extends Form {

    protected $adapter;

    public function __construct(AdapterInterface $dbAdapter) {

        $this->adapter = $dbAdapter;

        parent::__construct("practical_exam");
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'practice_exam_id',
            'type' => 'Hidden',
        ));

        $this->add(array(
            'name' => 'exam_type',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Type of Exam',
                'value_options' => array(
                    '1st attempt' => '1st attempt',
                    '2nd attempt' => '2nd attempt',
                    '3rd attempt' => '3rd attempt'
                )
            ),
        ));

        $this->add(array(
            'name' => 'exam_admin_by_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Exam Administrator',
                'empty_option' => 'Please choose an administrator',
                'value_options' => $this->getListExamAdmin()
            ),
        ));

        $this->add(array(
            'name' => 'provider_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Provider',
                'empty_option' => 'Please choose a provider',
                'value_options' => $this->getListProvider()
            ),
        ));
        $this->add(array(
            'name' => 'pre_analytic',
            'type' => 'text',
            'options' => array(
                'label' => 'Pre-analytic',
            ),
            'attributes' => array(
                'min' => '0',
            )
        ));

        $this->add(array(
            'name' => 'analytic',
            'type' => 'text',
            'options' => array(
                'label' => 'Analytic',
            ),
            'attributes' => array(
                'min' => '0',
            )
        ));
        $this->add(array(
            'name' => 'post_analytic',
            'type' => 'text',
            'options' => array(
                'label' => 'Post-analytic',
            ),
            'attributes' => array(
                'min' => '0',
            )
        ));

        $this->add(array(
            'name' => 'Sample_testing_score',
            'type' => 'text',
            'options' => array(
                'label' => 'Sample Testing Score',
            ),
            'attributes' => array(
                'min' => '0',
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'date',
            'attributes' => [
                'id' => 'date',
                'type' => 'text'
            ],
            'options' => array(
                'label' => 'Date'
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
            'name' => 'written',
            'type' => 'hidden',
        ));
    }

    public function getListProvider() {
        $dbAdapter = $this->adapter;
        $sql = 'SELECT id, certification_id,last_name,first_name,middle_name FROM provider order by last_name asc ';
        $statement = $dbAdapter->query($sql);
        $result = $statement->execute();

        $selectData = array();

        foreach ($result as $res) {
            $selectData[$res['id']] = $res['last_name'] . ' ' . $res['first_name'] . ' ' . $res['middle_name'];
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
            $selectData[$res['exam_admin_by_id']] = $res['admin_last_name'] . ' ' . $res['admin_first_name'] . ' ' . $res['admin_middle_name'];
        }
        return $selectData;
    }

}

<?php

namespace Certification\Form;

use Zend\Form\Form;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Adapter;

class TrainingForm extends Form {

    protected $adapter;

    public function __construct(AdapterInterface $dbAdapter) {

        $this->adapter = $dbAdapter;

        parent::__construct("training");
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'training_id',
            'type' => 'Hidden',
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'Provider_id',
            'options' => array(
                'label' => 'Provider',
                'empty_option' => 'Please choose a Provider',
                'value_options' => $this->getListProvider(),
            ),
            
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'Start_date',
            'attributes' => [
                'id' => 'date',
               'type' => 'text'
            ],
            'options' => array(
                'label' => 'Start date'
            )
        ));


        $this->add(array(
            'type' => 'text',
            'name' => 'end_date',
            'attributes' => [
                'id' => 'date2',
               'type' => 'text',
            ],
            'options' => array(
                'label' => 'end date',
               
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'type_of_training',
            'options' => array(
                'label' => 'Type of training',
                'empty_option' => 'Please choose a Type of Training',
                'value_options' => array(
                   'Initial (Nationally approved RT training)' => 'Initial (Nationally approved RT training)',
                    'Initial (on the job training by peer)' => 'Initial (on the job training by peer)',
                    'Refresher (Nationally approved RT training)' => 'Refresher (Nationally approved RT training)',
                    'Refresher (Topic specific training)' => 'Refresher (Topic specific training)',
                    'Mentoring (on job by supervisor)' => 'Mentoring (on job by supervisor)',
                    'Mentoring (Distance learning, i.e., ECHO)' => 'Mentoring (Distance learning, i.e., ECHO',
                    ),
                ),
            ) );
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'training_organization_id',
            'options' => array(
                'label' => 'Training Organization',
                'empty_option' => 'Please choose an organization',
                'value_options' => $this->getListTrainingOrganization(),
                ),
            
           
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'trainer_id',
            'options' => array(
                'label' => 'Trainer',
                'empty_option' => 'Please choose a Trainer',
                'value_options' => $this->getListTrainer(),
            ),
            
        ));
        $this->add(array(
            'name' => 'score',
            'type' => 'Number',
            'options' => array(
                'label' => 'Score',
            ),
            'attributes' => array(
             'min' => '0',
                )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'Pass_fail',
            'options' => array(
                'label' => 'Performance',
                'empty_option' => 'Please choose an option',
                'value_options' => array(
                   'Satisfactory' => 'Satisfactory',
                    'Unsatisfactory' => 'Unsatisfactory',
                ),
            ),
            
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'training_certificate',
            'options' => array(
                'label' => 'Training certificate ',
                'empty_option' => 'Please choose a training certificate',
                'value_options' => array(
                   'Yes' => 'Yes',
                    'No' => 'No',
                ),
            ),
            
        ));
        $this->add(array(
            'name' => 'Comments',
            'attributes' => array(
                'type' => 'textarea'
            ),
            'options' => array(
                'label' => 'Comments',
            ),
        ));



        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Add',
                'id' => 'submitbutton',
            ),
        ));
    }

    public function getListProvider() {
        $dbAdapter = $this->adapter;
        $sql = 'SELECT id,certification_id,last_name,first_name,middle_name FROM provider order by last_name asc ';
        $statement = $dbAdapter->query($sql);
        $result = $statement->execute();

        $selectData = array();

        foreach ($result as $res) {
            $selectData[$res['id']] = $res['last_name'].' '. $res['first_name'].' '. $res['middle_name'];
           
        }
        return $selectData;
    }
  
     public function getListTrainingOrganization() {
        $dbAdapter = $this->adapter;
        $sql = 'SELECT training_organization_id,training_organization_name FROM training_organization order by training_organization_name asc ';
        $statement = $dbAdapter->query($sql);
        $result = $statement->execute();

        $selectData = array();

        foreach ($result as $res) {
            $selectData[$res['training_organization_id']] = $res['training_organization_name'];
           
        }
        return $selectData;
    }
    
     public function getListTrainer() {
        $dbAdapter = $this->adapter;
        $sql = 'SELECT trainer_id,trainer_last_name,trainer_first_name FROM trainer order by trainer_last_name asc ';
        $statement = $dbAdapter->query($sql);
        $result = $statement->execute();

        $selectData = array();

        foreach ($result as $res) {
            $selectData[$res['trainer_id']] = $res['trainer_last_name'].' '. $res['trainer_first_name'];
           
        }
        return $selectData;
    }
    
}

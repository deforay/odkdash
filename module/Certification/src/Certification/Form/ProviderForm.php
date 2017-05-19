<?php

namespace Certification\Form;

use Zend\Form\Form;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Adapter;

class ProviderForm extends Form {

    protected $adapter;

    public function __construct(AdapterInterface $dbAdapter) {

        $this->adapter = $dbAdapter;

        parent::__construct("provider");
        $this->setAttributes(array('method'=> 'post',
            ));

        $this->add(array(
            'name' => 'id',
            'type' => 'Hidden',
        ));
        
        $this->add(array(
            'name' => 'certification_reg_no',
            'type' => 'Text',
            'options' => array(
                'label' => 'Certification ID',
            ),
        ));
        
        $this->add(array(
            'name' => 'certification_id',
            'type' => 'Text',
            'options' => array(
                'label' => 'Certification ID',
            ),
        ));
        
        $this->add(array(
            'name' => 'professional_reg_no',
            'type' => 'Text',
            'options' => array(
                'label' => 'Professional Registration No (if available)',
            ),
        ));
        $this->add(array(
            'name' => 'last_name',
            'type' => 'Text',
            'options' => array(
                'label' => 'Last Name (Surname)',
            ),
        ));
        $this->add(array(
            'name' => 'first_name',
            'type' => 'Text',
            'options' => array(
                'label' => 'First Name',
            ),
        ));

        $this->add(array(
            'name' => 'middle_name',
            'type' => 'Text',
            'options' => array(
                'label' => 'Middle Name (3rd name)',
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
            'name' => 'type_vih_test',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Type VIH Test Modality/Point',
                'empty_option' => 'Please choose an Type',
                'value_options' => array(
                    'ART clinic' => 'ART clinic',
                    'Community' => 'Community',
                    'IPD' => 'IPD',
                    'LAB' => 'LAB',
                    'OPD' => 'OPD',
                    'PITC' => 'PITC',
                    'PMTCT' => 'PMTCT',
                    'STI clinic' => 'STI clinic',
                    'TB clinic' => 'TB clinic',
                    'VCT/HTC' => 'VCT/HTC',
                )
            ),
        ));
        $this->add(array(
            'name' => 'phone',
            'type' => 'text',
            'options' => array(
                'label' => 'Phone',
            ),
            
        ));
        $this->add(array(
            'name' => 'email',
            'type' => 'email',
            'options' => array(
                'label' => 'Email',
            ),
        ));
        $this->add(array(
            'name' => 'prefered_contact_method',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Prefered Contact Method',
                'empty_option' => 'Please choose a methode',
                'value_options' => array(
                    'Phone' => 'Phone',
                    'Email' => 'Email'
                )
            ),
        ));
        $this->add(array(
            'name' => 'current_jod',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Current Job Title',
                'empty_option' => 'Please choose a Job Title',
                'value_options' => array(
                    'Assistant Medical Officer'=>'Assistant Medical Officer',
                    'Counselor' => 'Counselor',
                    'Health assistant' => 'Health assistant',
                    'Health attendant' => 'Health attendant',
                    'Lab Assistant' => 'Lab Assistant',
                    'Lab Scientist' => 'Lab Scientist',
                    'Lab technician' => 'Lab technician',
                    'Lab technologist' => 'Lab technologist',
                    'Medical doctor' => 'Medical doctor',
                    'Midwife' => 'Midwife',
                    'Nurse' => 'Nurse',
                    'Nurse assistant' => 'Nurse assistant'
                ),
            ),
        ));
           
        $this->add(array(
            'name' => 'time_worked',
            'type' => 'Text',
            'options' => array(
                'label' => 'Time worked as tester',
            ),
        ));
        
        $this->add(array(
            'name' => 'test_site_in_charge_name',
            'type' => 'Text',
            'options' => array(
                'label' => 'Full Name',
            ),
        ));
        $this->add(array(
            'name' => 'test_site_in_charge_phone',
            'type' => 'Text',
            'options' => array(
                'label' => 'Phone',
            ),
        ));
        $this->add(array(
            'name' => 'test_site_in_charge_email',
            'type' => 'email',
            'options' => array(
                'label' => 'Email',
            ),
        ));
        $this->add(array(
            'name' => 'facility_in_charge_name',
            'type' => 'Text',
            'options' => array(
                'label' => 'Full Name',
            ),
        ));
        $this->add(array(
            'name' => 'facility_in_charge_phone',
            'type' => 'Text',
            'options' => array(
                'label' => 'Phone',
            ),
        ));
        $this->add(array(
            'name' => 'facility_in_charge_email',
            'type' => 'email',
            'options' => array(
                'label' => 'Email',
            ),
        ));
        
        
        
        $this->add(array(
            'name' => 'facility_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'facility Name',
                'empty_option' => 'Please choose a facility',
                'value_options' => $this->getOptionsForSelect(),
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

    public function getOptionsForSelect() {
        $dbAdapter = $this->adapter;
        $sql = 'SELECT id,facility_name FROM certification_facilities ORDER by facility_name asc ';
        $statement = $dbAdapter->query($sql);
        $result = $statement->execute();
        
        foreach ($result as $res) {
            $selectData[$res['id']] = $res['facility_name'];
        }
        return $selectData;
    }

}

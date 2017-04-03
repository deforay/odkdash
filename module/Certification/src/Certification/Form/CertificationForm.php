<?php

namespace Certification\Form;

use Zend\Form\Form;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Adapter;

class CertificationForm extends Form {

    protected $adapter;

    public function __construct($name = null) {

        parent::__construct("certification");
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'certification_id',
            'type' => 'Hidden',
        ));
        $this->add(array(
            'name' => 'provider_id',
            'type' => 'Number',
            'options' => array(
                'label' => 'Provider',
            ),
        ));
        $this->add(array(
            'name' => 'score',
            'type' => 'Number',
            'options' => array(
                'label' => 'Score',
            ),
        ));

         $this->add(array(
            'name' => 'final_decision',
            'type' => 'text',
            'options' => array(
                'label' => 'Final Decision',
            ),
        ));

        
        $this->add(array(
            'name' => 'certificate_issued_by_id',
            'type' => 'Number',
            'options' => array(
                'label' => 'Certificate Issued by',
            ),
        ));

        $this->add(array(
            'name' => 'date_certificate_issued',
            'type' => 'Text',
            'attributes' => [
                'id' => 'date',
               'type' => 'text'
            ],
            'options' => array(
                'label' => 'Date Certificate Issued',
            ),
        ));
        $this->add(array(
            'name' => 'date_certificate_sent',
            'type' => 'Text',
            'attributes' => [
                'id' => 'date',
               'type' => 'text'
            ],
            'options' => array(
                'label' => 'Date Certificate Sent',
            ),
        ));

        $this->add(array(
            'name' => 'certification_type',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Type Of Certification',
                'empty_option' => 'Please choose an Type',
                'value_options' => array(
                    'Initial' => 'Initial',
                    'Re-certification' => 'Re-certification',
              )
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
        $sql = 'SELECT id,facility_name FROM spi_rt_3_facilities ORDER by facility_name asc ';
        $statement = $dbAdapter->query($sql);
        $result = $statement->execute();

        $selectData = array();

        foreach ($result as $res) {
            $selectData[$res['id']] = $res['facility_name'];
        }
        return $selectData;
    }

}

<?php

namespace Certification\Form;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Form\Form;

class CertificationForm extends Form {

    protected $adapter;

    public function __construct(AdapterInterface $dbAdapter) {
        $this->adapter = $dbAdapter;
        // we want to ignore the name passed
        parent::__construct('certification');

        $this->add(array(
            'name' => 'id',
            'type' => 'Hidden',
        ));
        $this->add(array(
            'name' => 'examination',
            'type' => 'hidden',
        ));
        $this->add(array(
            'name' => 'final_decision',
            'type' => 'text',
            'options' => array(
                'label' => 'Final Decision',
            ),
        ));

        $this->add(array(
            'name' => 'certification_issuer_id',
            'type' => 'text',
            'options' => array(
                'label' => 'Certificate Issued By',
            ),
        ));

        $this->add(array(
            'name' => 'date_certificate_issued',
            'type' => 'text',
            'options' => array(
                'label' => 'Date Certificate Issued',
                'attributes' => [
                    'id' => 'date2',
                    'type' => 'text',
                ],
            ),
        ));

        $this->add(array(
            'name' => 'date_certificate_sent',
            'type' => 'Text',
            'options' => array(
                'label' => 'Date Certificate Sent',
                'attributes' => [
                    'id' => 'date2',
                    'type' => 'text',
                ],
            ),
        ));

        $this->add(array(
            'name' => 'certification_type',
            'type' => 'text',
            'options' => array(
                'label' => 'Type of Certificate',
            ),
        ));

        $this->add(array(
            'name' => 'issued',
            'type' => 'Text',
            'options' => array(
                'label' => 'Approved',
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
        $sql = 'SELECT certification_issuer_id, issuer_last_name, issuer_first_name, issuer_middle_name FROM certification_issuer ORDER by issuer_last_name';
        $statement = $dbAdapter->query($sql);
        $result = $statement->execute();

        $selectData = array();

        foreach ($result as $res) {
            $selectData[$res['certification_issuer_id']] = $res['issuer_last_name'] . ' ' . $res['issuer_first_name'] . ' ' . $res['issuer_middle_name'];
        }
        return $selectData;
    }

}

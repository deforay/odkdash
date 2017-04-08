<?php

namespace Certification\Form;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Form\Form;

class CertificationForm extends Form {

    
   
    public function __construct(AdapterInterface $dbAdapter) {
        // we want to ignore the name passed
        $this->adapter = $dbAdapter;
        
        parent::__construct("certification");
        $this->setAttribute('method', 'post');

        
         $this->add(array(
             'name' => 'id',
             'type' => 'Hidden',
         ));
         $this->add(array(
             'name' => 'certification_id',
             'type' => 'Text',
             'options' => array(
                 'label' => 'Certification ID',
             ),
         ));
         $this->add(array(
             'name' => 'final_decision',
             'type' => 'Zend\Form\Element\Select',
             'options' => array(
                 'label' => 'Certificate Issued By',
                 'empty_option' => 'Please choose an option',
                'value_options' => array(
                   'Certified' => 'Certified',
                    'Pending' => 'Pending',
                    'Failed' => 'Failed',
                ),
             ),
         ));
         
         $this->add(array(
             'name' => 'certification_issuer_id',
             'type' => 'Zend\Form\Element\Select',
             'options' => array(
                 'label' => 'Final Decision',
                 'empty_option' => 'Please choose an option',
                'value_options' => $this->getListIssuer()
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
                'id' => 'date2',
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
                'label' => 'Type of Certificate',
                'empty_option' => 'Please choose an option',
                'value_options' => array(
                   'Initial' => 'Initial',
                    'Recertification' => 'Re-certification',
                ),
            ),
        ));

        $this->add(array(
            'name' => 'issued',
            'type' => 'Text',
            'options' => array(
                'label' => 'Issued',
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
     
    
//       
//       
//       
//        
//        $this->add(array(
//            'name' => 'certification_type',
//            'type' => 'Zend\Form\Element\Select',
//            'options' => array(
//                'label' => 'Type of Certificate',
//                'empty_option' => 'Please choose an option',
//                'value_options' => array(
//                   'Initial' => 'Initial',
//                    'Recertification' => 'Re-certification',
//                ),
//            ),
//        ));
//
//        $this->add(array(
//            'name' => 'issued',
//            'type' => 'Text',
//            'options' => array(
//                'label' => 'Issued',
//            ),
//        ));
//
//
//        $this->add(array(
//            'name' => 'submit',
//            'type' => 'Submit',
//            'attributes' => array(
//                'value' => 'Go',
//                'id' => 'submitbutton',
//            ),
//        ));
    }
    
     public function getListIssuer() {
        $dbAdapter = $this->adapter;
        $sql = 'SELECT certification_issuer_id,issuer_last_name,issuer_first_name,issuer_middle_name FROM certification_issuer order by issuer_last_name asc ';
        $statement = $dbAdapter->query($sql);
        $result = $statement->execute();

        $selectData = array();

        foreach ($result as $res) {
            $selectData[$res['certification_issuer_id']] = $res['issuer_last_name'].' '. $res['issuer_first_name'].' '. $res['issuer_middle_name'];
           
        }
        return $selectData;
    }

}

<?php

namespace Certification\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use DOMPDFModule\View\Model\PdfModel;
use Zend\Db\Sql\Sql;

class CertificationController extends AbstractActionController {

    protected $certificationTable;

    public function getCertificationTable() {
        if (!$this->certificationTable) {
            $sm = $this->getServiceLocator();
            $this->certificationTable = $sm->get('Certification\Model\CertificationTable');
        }
        return $this->certificationTable;
    }

    public function indexAction() {
        return new ViewModel(array(
            'certifications' => $this->getCertificationTable()->fetchAll(),
        ));
    }


    public function pdfAction() {

    }

    public function addAction() {
        
        $certification_id = (int) $this->params()->fromRoute('certification_id', 0);

        if (!$certification_id) {
            return $this->redirect()->toRoute('provider', array(
                        'action' => 'certification'
            ));
        }
         $form = new \Certification\Form\CertificationForm();
         $form->get('submit')->setValue('Add');

         
         $request = $this->getRequest();
         if ($request->isPost()) {
             $certification = new \Certification\Model\Certification();
             $form->setInputFilter($certification->getInputFilter());
             $form->setData($request->getPost());

             if ($form->isValid()) {
                 $certification->exchangeArray($form->getData());
                 $this->getCertificationTable()->saveCertification($certification);

                 // Redirect to list of albums
                 return $this->redirect()->toRoute('certification');
             }
         }
         return array('form' => $form);

    }

    public function editAction() {
        
    }

    public function deleteAction() {
        
    }

}

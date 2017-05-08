<?php

namespace Certification\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Certification\Model\Certification;
use Certification\Form\CertificationForm;

class CertificationIssuerController extends AbstractActionController {

    protected $certificationIssuerTable;

    public function getCertificationIssuerTable() {
        if (!$this->certificationIssuerTable) {
            $sm = $this->getServiceLocator();
            $this->certificationIssuerTable = $sm->get('Certification\Model\CertificationIssuerTable');
        }
        return $this->certificationIssuerTable;
    }

    public function indexAction() {
       
     $paginator = $this->getCertificationIssuerTable()->fetchAll(true);
     // set the current page to what has been passed in query string, or to 1 if none set
     $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
     // set the number of items per page to 10
     $paginator->setItemCountPerPage(10);

     return new ViewModel(array(
         'paginator' => $paginator
     ));
    }

    public function addAction() {
        $form = new \Certification\Form\CertificationIssuerForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $issuer = new \Certification\Model\CertificationIssuer();
            $form->setInputFilter($issuer->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $issuer->exchangeArray($form->getData());
                $this->getCertificationIssuerTable()->saveCertificationIssuer($issuer);

                return $this->redirect()->toRoute('certification-issuer');
            }
        }
        return array('form' => $form);
    }

    public function editAction() {

        $certification_issuer_id = (int) $this->params()->fromRoute('certification_issuer_id', 0);
        if (!$certification_issuer_id) {
            return $this->redirect()->toRoute('certification-issuer', array(
                        'action' => 'add'
            ));
        }

        try {
            $issuer = $this->getCertificationIssuerTable()->getCertificationIssuer($certification_issuer_id);
        } catch (\Exception $ex) {
            return $this->redirect()->toRoute('certification-issuer', array(
                        'action' => 'index'
            ));
        }

        $form = new \Certification\Form\CertificationIssuerForm();
        $form->bind($issuer);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($issuer->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getCertificationIssuerTable()->saveCertificationIssuer($issuer);

                
                return $this->redirect()->toRoute('certification-issuer');
            }
        }

        return array(
            'certification_issuer_id' => $certification_issuer_id,
            'form' => $form,
        );
    }
    
    public function searchAction() {
        
         $request = $this->getRequest();
        if ($request->isPost()) {
            $motCle = $request->getPost('motCle',null);
        }
//        die($motCle);
        return new ViewModel(array(
             'issuers' => $this->getCertificationIssuerTable()->search($motCle),
         ));
        
    }

}

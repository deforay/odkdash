<?php

namespace Certification\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

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

    

    public function addAction() {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

        $id = (int) $this->params()->fromRoute('id', 0);

        $form = new \Certification\Form\CertificationForm($dbAdapter);
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $certification = new \Certification\Model\Certification();
            $form->setInputFilter($certification->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $certification->exchangeArray($form->getData());
                $this->getCertificationTable()->saveCertification($certification);

                return $this->redirect()->toRoute('certification');
            }
        }

        return array('form' => $form,
            'id' => $id);
    }

    public function editAction() {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('certification', array(
                        'action' => 'add'
            ));
        }

        try {
            $certification = $this->getCertificationTable()->getCertification($id);
        } catch (\Exception $ex) {
            return $this->redirect()->toRoute('certification', array(
                        'action' => 'index'
            ));
        }

        $form = new \Certification\Form\CertificationForm($dbAdapter);
        $form->bind($certification);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($certification->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getCertificationTable()->saveCertification($certification);

                return $this->redirect()->toRoute('certification');
            }
        }

        return array(
            'id' => $id,
            'form' => $form,
        );
    }

    
    public function pdfAction() {
     
    }
}

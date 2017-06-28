<?php

namespace Certification\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Certification\Model\Recertification;
use Certification\Form\RecertificationForm;
use Zend\Session\Container;

class RecertificationController extends AbstractActionController {

    protected $recertificationTable;

    public function getRecertificationTable() {
        if (!$this->recertificationTable) {
            $sm = $this->getServiceLocator();
            $this->recertificationTable = $sm->get('Certification\Model\RecertificationTable');
        }
        return $this->recertificationTable;
    }

    public function indexAction() {

        $reminder = $this->getRecertificationTable()->fetchAll2();
        return new ViewModel(array(
            'recertifications' => $this->getRecertificationTable()->fetchAll(),
            'reminders' => $this->getRecertificationTable()->fetchAll2()
        ));
    }

    public function addAction() {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $provider = (int) base64_decode($this->params()->fromQuery(base64_encode('provider')));
        $form = new RecertificationForm($dbAdapter);
        $form->get('submit')->setValue('SUBMIT');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $recertification = new Recertification();
            $form->setInputFilter($recertification->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $recertification->exchangeArray($form->getData());
                $this->getRecertificationTable()->saveRecertification($recertification);
                $container = new Container('alert');
                $container->alertMsg = 'Re-certification added successfully';
                return $this->redirect()->toRoute('recertification');
            }
        }
        return array('form' => $form,
            'provider'=>$provider);
    }

    public function editAction() {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $recertification_id = (int) base64_decode($this->params()->fromRoute('recertification_id', 0));
        if (!$recertification_id) {
            return $this->redirect()->toRoute('recertification', array(
                        'action' => 'add'
            ));
        }

        try {
            $recertification = $this->getRecertificationTable()->getRecertification($recertification_id);
        } catch (\Exception $ex) {
            return $this->redirect()->toRoute('recertification', array(
                        'action' => 'index'
            ));
        }

        $form = new RecertificationForm($dbAdapter);
        $form->bind($recertification);
        $form->get('submit')->setAttribute('value', 'UPDATE');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($recertification->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getRecertificationTable()->saveRecertification($recertification);
                $container = new Container('alert');
                $container->alertMsg = 'Re-certification updated successfully';
                return $this->redirect()->toRoute('recertification');
            }
        }

        return array(
            'recertification_id' => $recertification_id,
            'form' => $form,
            
        );
    }

    

}

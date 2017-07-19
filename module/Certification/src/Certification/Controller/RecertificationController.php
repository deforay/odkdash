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
        $this->forward()->dispatch('Certification\Controller\Certification', array('action' => 'index'));
//        $reminder = $this->getRecertificationTable()->fetchAll2();
        $certification_id = (int) base64_decode($this->params()->fromQuery(base64_encode('certification_id'), null));
        $key = base64_decode($this->params()->fromQuery(base64_encode('key'), null));
        if (!empty($certification_id) && !empty($key)) {
            $this->getRecertificationTable()->ReminderSent($certification_id);
            $container = new Container('alert');
            $container->alertMsg = 'Perform successfully';
            return $this->redirect()->toRoute('recertification', array(
                        'action' => 'add'),
                    array('query'=>array(base64_encode('certification_id') => base64_encode($certification_id))));
        } else {

            return new ViewModel(array(
                'recertifications' => $this->getRecertificationTable()->fetchAll(),
                'reminders' => $this->getRecertificationTable()->fetchAll2()
            ));
        }
    }

    public function addAction() {
        $this->forward()->dispatch('Certification\Controller\Certification', array('action' => 'index'));
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $certification_id = (int) base64_decode($this->params()->fromQuery(base64_encode('certification_id'), null));
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
                return $this->redirect()->toRoute('recertification', array('action'=>'add'));
            }
        }
        if(isset($certification_id)){
            $provider = $this->getRecertificationTable()->certificationInfo($certification_id);
        return array('form' => $form,
            'provider' => $provider);
        } else {
            return array('form' => $form);
        }
        
        
    }

    public function editAction() {
        $this->forward()->dispatch('Certification\Controller\Certification', array('action' => 'index'));
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

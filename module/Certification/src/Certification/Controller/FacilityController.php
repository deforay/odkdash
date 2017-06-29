<?php

namespace Certification\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Certification\Model\Facility;
use Certification\Form\FacilityForm;
use Zend\Session\Container;

class FacilityController extends AbstractActionController {

    protected $facilityTable;

    public function getFacilityTable() {
        if (!$this->facilityTable) {
            $sm = $this->getServiceLocator();
            $this->facilityTable = $sm->get('Certification\Model\FacilityTable');
        }
        return $this->facilityTable;
    }

    public function indexAction() {
        $this->forward()->dispatch('Certification\Controller\Certification', array('action' => 'index'));
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new FacilityForm($dbAdapter);
        $form->get('submit')->setValue('Submit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $facility = new Facility();
            $form->setInputFilter($facility->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $facility->exchangeArray($form->getData());
                $this->getFacilityTable()->saveFacility($facility);
                $container = new Container('alert');
                $container->alertMsg = 'Facility added successfully';
                return $this->redirect()->toRoute('facility');
            }
        }

        return new ViewModel(array(
            'facilities' => $this->getFacilityTable()->fetchAll(),
            'form' => $form,
        ));
    }

    public function editAction() {
        $this->forward()->dispatch('Certification\Controller\Certification', array('action' => 'index'));
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $id = (int) base64_decode($this->params()->fromRoute('id', 0));
        if (!$id) {
            return $this->redirect()->toRoute('facility', array(
                        'action' => 'index'
            ));
        }

        try {
            $facility = $this->getFacilityTable()->getFacility($id);
//            die(print_r($facility));
        } catch (\Exception $ex) {
            return $this->redirect()->toRoute('facility', array(
                        'action' => 'index'
            ));
        }
        $form = new FacilityForm($dbAdapter);
        $form->bind($facility);
        $form->get('submit')->setAttribute('value', 'Update');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($facility->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getFacilityTable()->saveFacility($facility);
                $container = new Container('alert');
                $container->alertMsg = 'Facility updated successfully';
                return $this->redirect()->toRoute('facility');
            }
        }

        return array(
            'id' => $id,
            'form' => $form,
        );
    }

}

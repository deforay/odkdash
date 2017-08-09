<?php

namespace Certification\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Certification\Model\Provider;
use Certification\Form\ProviderForm;
use Zend\Session\Container;

class ProviderController extends AbstractActionController {

    protected $providerTable;

    public function getProviderTable() {
        if (!$this->providerTable) {
            $sm = $this->getServiceLocator();
            $this->providerTable = $sm->get('Certification\Model\ProviderTable');
        }
        return $this->providerTable;
    }

    public function indexAction() {
        $this->forward()->dispatch('Certification\Controller\Certification', array('action' => 'index'));
        return new ViewModel(array(
            'providers' => $this->getProviderTable()->fetchAll(),
        ));
    }

    public function addAction() {
        $this->forward()->dispatch('Certification\Controller\Certification', array('action' => 'index'));
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new ProviderForm($dbAdapter);
        $form->get('submit')->setValue('SUBMIT');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $provider = new Provider();
            $form->setInputFilter($provider->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $provider->exchangeArray($form->getData());
                ?>
                <pre> <?php // print_r($provider)                  ?></pre>

                <?php
                $this->getProviderTable()->saveProvider($provider);
                $container = new Container('alert');
                $container->alertMsg = 'New tester added successfully';
                return $this->redirect()->toRoute('provider', array('action' => 'add'));
            }
        }
        return array('form' => $form,
            'providers' => $this->getProviderTable()->fetchAll(),
        );
    }

    public function editAction() {
        $this->forward()->dispatch('Certification\Controller\Certification', array('action' => 'index'));
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

        $id = (int) base64_decode($this->params()->fromRoute('id', 0));

        if (!$id) {
            return $this->redirect()->toRoute('provider', array(
                        'action' => 'add'
            ));
        }

        try {
            $provider = $this->getProviderTable()->getProvider($id);
//            die(print_r($provider));
        } catch (\Exception $ex) {
            return $this->redirect()->toRoute('provider', array(
                        'action' => 'index'
            ));
        }

        $form = new ProviderForm($dbAdapter);

        $form->bind($provider);
        $form->get('submit')->setAttribute('value', 'UPDATE');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($provider->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getProviderTable()->saveProvider($provider);
                $container = new Container('alert');
                $container->alertMsg = 'Tester updated successfully';
                return $this->redirect()->toRoute('provider');
            }
        }
        $district = $this->getProviderTable()->DistrictName($provider->district);
        $facility = $this->getProviderTable()->FacilityName($provider->facility_id);

        return array(
            'id' => $id,
            'form' => $form,
            'district_id' => $district['district_id'],
            'district_name' => $district['district_name'],
            'facility_id' => $facility['facility_id'],
            'facility_name' => $facility['facility_name'],
        );
    }

    public function districtAction() {

        $q = (int) $_GET['q'];
        $result = $this->getProviderTable()->getDistrict($q);
        return array(
            'result' => $result,
        );
    }

    public function facilityAction() {

        $q = (int) $_GET['q'];
        $result = $this->getProviderTable()->getFacility($q);
        return array(
            'result' => $result,
        );
    }
    
    public function  deleteAction(){
        $id = (int) $this->params()->fromRoute('id', 0);

        if (!$id) {
            return $this->redirect()->toRoute('provider');
        } else {
            $forein_key1= $this->getProviderTable()->foreigne_key1($id);
            $forein_key2= $this->getProviderTable()->foreigne_key2($id);
            if($forein_key1==0 || $forein_key2==0){
            $this->getProviderTable()->deleteProvider($id);
            $container = new Container('alert');
            $container->alertMsg = 'Deleted successfully';
             return $this->redirect()->toRoute('provider');
        } else {
            $container = new Container('alert');
            $container->alertMsg = 'Unable to remove this provider because he has already completed an exam.';
             return $this->redirect()->toRoute('provider');
        }
        }
    }

}

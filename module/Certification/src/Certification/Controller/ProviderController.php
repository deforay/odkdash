<?php

namespace Certification\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Certification\Model\Provider;
use Certification\Form\ProviderForm;

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



        return new ViewModel(array(
            'providers' => $this->getProviderTable()->fetchAll(),
        ));
    }

    public function addAction() {

        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

        $form = new ProviderForm($dbAdapter);
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $provider = new Provider();
            $form->setInputFilter($provider->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $provider->exchangeArray($form->getData());
                ?>
                <pre> <?php // print_r($provider)             ?></pre>

                <?php
                $this->getProviderTable()->saveProvider($provider);

                return $this->redirect()->toRoute('provider');
            }
        }
        return array('form' => $form);
    }

    public function editAction() {

        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

        $id = (int) $this->params()->fromRoute('id', 0);

        if (!$id) {
            return $this->redirect()->toRoute('provider', array(
                        'action' => 'add'
            ));
        }

        try {
            $provider = $this->getProviderTable()->getProvider($id);
        } catch (\Exception $ex) {
            return $this->redirect()->toRoute('provider', array(
                        'action' => 'index'
            ));
        }

        $form = new ProviderForm($dbAdapter);
        $form->bind($provider);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($provider->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getProviderTable()->saveProvider($provider);

                return $this->redirect()->toRoute('provider');
            }
        }

        return array(
            'id' => $id,
            'form' => $form,
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

}

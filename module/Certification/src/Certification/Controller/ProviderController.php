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
        $paginator = $this->getProviderTable()->fetchAll(true);
        $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(10);

        return new ViewModel(array(
            'paginator' => $paginator
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
                <pre> <?php // print_r($provider)           ?></pre>

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

    public function searchAction() {

        $request = $this->getRequest();
        if ($request->isPost()) {
            $motCle = $request->getPost('motCle', null);
        }
 
        $page = (int) $this->params()->fromQuery('page', 1);
// set the current page to what has been passed in query string, or to 1 if none set
        $paginator = $this->getProviderTable()->search($motCle, true);
        $paginator->setCurrentPageNumber($page);
        // set the number of items per page to 10
        $paginator->setItemCountPerPage(10);

        return new ViewModel(array(
            'paginator' => $paginator,
            
        ));
    }

}

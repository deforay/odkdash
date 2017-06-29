<?php

namespace Certification\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Certification\Model\Region;
use Certification\Form\RegionForm;
use Zend\Session\Container;

class RegionController extends AbstractActionController {

    protected $regionTable;

    public function getRegionTable() {
        if (!$this->regionTable) {
            $sm = $this->getServiceLocator();
            $this->regionTable = $sm->get('Certification\Model\RegionTable');
        }
        return $this->regionTable;
    }

    public function indexAction() {
        $this->forward()->dispatch('Certification\Controller\Certification', array('action' => 'index'));
        $form = new RegionForm();
        $form->get('submit')->setValue('Submit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $region = new Region();
            $form->setInputFilter($region->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $region->exchangeArray($form->getData());
                $this->getRegionTable()->saveRegion($region);
                $container = new Container('alert');
                $container->alertMsg = 'Region added successfully';

                return $this->redirect()->toRoute('region');
            }
        }

        return new ViewModel(array(
            'regions' => $this->getRegionTable()->fetchAll(),
            'form' => $form,
        ));
    }

    public function editAction() {
        $this->forward()->dispatch('Certification\Controller\Certification', array('action' => 'index'));
        $id = (int) base64_decode($this->params()->fromRoute('id', 0));
             
        if (!$id) {
            return $this->redirect()->toRoute('region', array(
                        'action' => 'index'
            ));
        }
        
        try {
            $region = $this->getRegionTable()->getRegion($id);
//            die(print_r($region));
        } catch (\Exception $ex) {
            return $this->redirect()->toRoute('region', array(
                        'action' => 'index'
            ));
        }
        $form = new RegionForm();
         $form->bind($region);
        $form->get('submit')->setAttribute('value', 'Update');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($region->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getRegionTable()->saveRegion($region);
                $container = new Container('alert');
                $container->alertMsg = 'Region updated successfully';
                return $this->redirect()->toRoute('region');
            }
        }

        return array(
            'id' => $id,
            'form' => $form,
            'regions' => $this->getRegionTable()->fetchAll(),
        );
    }

}

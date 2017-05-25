<?php

namespace Certification\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Certification\Model\Training;
use Certification\Form\TrainingForm;

class TrainingController extends AbstractActionController {

    protected $TrainingTable;

    public function getTrainingTable() {
        if (!$this->TrainingTable) {
            $sm = $this->getServiceLocator();
            $this->TrainingTable = $sm->get('Certification\Model\TrainingTable');
        }
        return $this->TrainingTable;
    }

    public function indexAction() {
        $paginator = $this->getTrainingTable()->fetchAll(true);
        $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(10);

        return new ViewModel(array(
            'paginator' => $paginator
        ));
    }

    public function addAction() {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new TrainingForm($dbAdapter);
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $training = new Training();
            $form->setInputFilter($training->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $training->exchangeArray($form->getData());
                $this->getTrainingTable()->saveTraining($training);
                return $this->redirect()->toRoute('training');
            }
        }

        return array('form' => $form);
    }

//
    public function editAction() {

        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $training_id = (int) $this->params()->fromRoute('training_id', 0);
        if (!$training_id) {
            return $this->redirect()->toRoute('training', array(
                        'action' => 'add'
            ));
        }

        try {
            $training = $this->getTrainingTable()->getTraining($training_id);
        } catch (\Exception $ex) {
            return $this->redirect()->toRoute('training', array(
                        'action' => 'index'
            ));
        }

        $form = new TrainingForm($dbAdapter);
        $form->bind($training);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($training->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getTrainingTable()->saveTraining($training);


                return $this->redirect()->toRoute('training');
            }
        }

        return array(
            'training_id' => $training_id,
            'form' => $form,
        );
    }

    public function searchAction() {

        $request = $this->getRequest();
        if ($request->isPost()) {
            $motCle = $request->getPost('motCle', null);
        }
       $paginator = $this->getTrainingTable()->search($motCle,true);
        $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(10);

        return new ViewModel(array(
            'paginator' => $paginator
        ));
        
   
        }

}

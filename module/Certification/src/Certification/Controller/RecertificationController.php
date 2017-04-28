<?php

namespace Certification\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Certification\Model\Recertification;
use Certification\Form\RecertificationForm;

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
        
     $paginator = $this->getRecertificationTable()->fetchAll(true);
     // set the current page to what has been passed in query string, or to 1 if none set
     $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
     // set the number of items per page to 10
     $paginator->setItemCountPerPage(10);

     return new ViewModel(array(
         'paginator' => $paginator
     ));
    }

    public function addAction() {
        $form = new RecertificationForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $recertification = new Recertification();
            $form->setInputFilter($recertification->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $recertification->exchangeArray($form->getData());
                $this->getRecertificationTable()->saveRecertification($recertification);

                return $this->redirect()->toRoute('recertification');
            }
        }
        return array('form' => $form);
    }

    public function editAction() {
        $recertification_id = (int) $this->params()->fromRoute('recertification_id', 0);
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

        $form = new RecertificationForm();
        $form->bind($recertification);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($recertification->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getRecertificationTable()->saveRecertification($recertification);

                return $this->redirect()->toRoute('recertification');
            }
        }

        return array(
            'recertification_id' => $recertification_id,
            'form' => $form,
        );
    }

    

}

<?php

namespace Certification\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Certification\Form\WrittenExamForm;
use Zend\View\Model\ViewModel;

class WrittenExamController extends AbstractActionController {

    protected $writtenExamTable;

    public function getWrittenExamTable() {
        if (!$this->writtenExamTable) {
            $sm = $this->getServiceLocator();
            $this->writtenExamTable = $sm->get('Certification\Model\WrittenExamTable');
        }
        return $this->writtenExamTable;
    }

    public function indexAction() {
        
        
     $paginator = $this->getWrittenExamTable()->fetchAll(true);
     // set the current page to what has been passed in query string, or to 1 if none set
     $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
     // set the number of items per page to 10
     $paginator->setItemCountPerPage(10);

     return new ViewModel(array(
         'paginator' => $paginator
     ));
        
            }

    public function addAction() {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new WrittenExamForm($dbAdapter);
        $form->get('submit')->setValue('Add');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $writtenExam = new \Certification\Model\WrittenExam();
            $form->setInputFilter($writtenExam->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $writtenExam->exchangeArray($form->getData());
                ?> <pre> <?php // print_r($writtenExam);?>    </pre>
                <?php
                $this->getWrittenExamTable()->saveWrittenExam($writtenExam);
                $last_id = $this->getWrittenExamTable()->last_id();
                $this->getWrittenExamTable()->insertToExamination($last_id);
                return $this->redirect()->toRoute('written-exam');
            }
        }

        return array('form' => $form,
            );
    }

    public function editAction() {
        
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $id_written_exam= (int) $this->params()->fromRoute('id_written_exam', 0);
        if (!$id_written_exam) {
            return $this->redirect()->toRoute('written-exam', array(
                        'action' => 'add'
            ));
        }

        try {
            $writtenExam = $this->getWrittenExamTable()->getWrittenExam($id_written_exam);
        } catch (\Exception $ex) {
            return $this->redirect()->toRoute('written-exam', array(
                        'action' => 'index'
            ));
        }

        $form = new WrittenExamForm($dbAdapter);
        $form->bind($writtenExam);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($writtenExam->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getWrittenExamTable()->saveWrittenExam($writtenExam);

                return $this->redirect()->toRoute('written-exam');
            }
        }

        return array(
            'id_written_exam' => $id_written_exam,
            'form' => $form,
        );
    }

    public function searchAction() {
         $request = $this->getRequest();
        if ($request->isPost()) {
            $motCle = $request->getPost('motCle',null);
        }
//        die($motCle);
        return new ViewModel(array(
             'writtens' => $this->getWrittenExamTable()->search($motCle),
         ));
        
    }
}

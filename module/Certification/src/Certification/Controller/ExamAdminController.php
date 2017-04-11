<?php

namespace Certification\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ExamAdminController extends AbstractActionController {

    protected $examAdminTable;

    public function getExamAdminTable() {
        if (!$this->examAdminTable) {
            $sm = $this->getServiceLocator();
            $this->examAdminTable = $sm->get('Certification\Model\ExamAdminTable');
        }
        return $this->examAdminTable;
    }

    public function indexAction() {

     $paginator = $this->getExamAdminTable()->fetchAll(true);
     // set the current page to what has been passed in query string, or to 1 if none set
     $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
     // set the number of items per page to 10
     $paginator->setItemCountPerPage(10);

     return new ViewModel(array(
         'paginator' => $paginator
     ));
    }

    public function addAction() {

        $form = new \Certification\Form\ExamAdminForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $examAdmin = new \Certification\Model\ExamAdmin();
            $form->setInputFilter($examAdmin->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $examAdmin->exchangeArray($form->getData());
                $this->getExamAdminTable()->saveExamAdmin($examAdmin);

                return $this->redirect()->toRoute('exam-admin');
            }
        }
        return array('form' => $form);
    }

    public function editAction() {
         $exam_admin_by_id = (int) $this->params()->fromRoute('exam_admin_by_id', 0);
         if (!$exam_admin_by_id) {
             return $this->redirect()->toRoute('exam-admin', array(
                 'action' => 'add'
             ));
         }

         // Get the Album with the specified id.  An exception is thrown
         // if it cannot be found, in which case go to the index page.
         try {
             $examAdmin = $this->getExamAdminTable()->getExamAdmin($exam_admin_by_id);
         }
         catch (\Exception $ex) {
             return $this->redirect()->toRoute('exam-admin', array(
                 'action' => 'index'
             ));
         }

         $form  = new \Certification\Form\ExamAdminForm();
         $form->bind($examAdmin);
         $form->get('submit')->setAttribute('value', 'Edit');

         $request = $this->getRequest();
         if ($request->isPost()) {
             $form->setInputFilter($examAdmin->getInputFilter());
             $form->setData($request->getPost());

             if ($form->isValid()) {
                 $this->getExamAdminTable()->saveExamAdmin($examAdmin);
                 return $this->redirect()->toRoute('exam-admin');
             }
         }

         return array(
             'exam_admin_by_id' => $exam_admin_by_id,
             'form' => $form,
         );
        
    }

   }

<?php
namespace Certification\Controller;
 use Zend\Mvc\Controller\AbstractActionController;
 use Zend\View\Model\ViewModel;
 use Certification\Model\PracticalExam;

 class PracticalExamController extends AbstractActionController {

     protected $practicalExamTable;
     public function getPracticalExamTable()
     {
         if (!$this->practicalExamTable) {
             $sm = $this->getServiceLocator();
             $this->practicalExamTable = $sm->get('Certification\Model\PracticalExamTable');
         }
         return $this->practicalExamTable;
     }
     public function indexAction()
     {
         return new ViewModel(array(
             'exams' => $this->getPracticalExamTable()->fetchAll(),
         ));
     }

     public function addAction()
     {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new \Certification\Form\PracticalExamForm($dbAdapter);
        $form->get('submit')->setValue('Add');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $practicalExam = new PracticalExam();
            $form->setInputFilter($practicalExam->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $practicalExam->exchangeArray($form->getData());
//               
                $this->getPracticalExamTable()->savePracticalExam($practicalExam);

                return $this->redirect()->toRoute('practical-exam');
            }
        }

        return array('form' => $form);
    }
     


     public function editAction()
             
     {
          $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
         $practice_exam_id = (int) $this->params()->fromRoute('practice_exam_id', 0);
         if (!$practice_exam_id) {
             return $this->redirect()->toRoute('practical-exam', array(
                 'action' => 'add'
             ));
         }

         try {
             $practicalExam = $this->getPracticalExamTable()->getPracticalExam($practice_exam_id);
         }
         catch (\Exception $ex) {
             return $this->redirect()->toRoute('practical-exam', array(
                 'action' => 'index'
             ));
         }

         $form  = new \Certification\Form\PracticalExamForm($dbAdapter);
         $form->bind($practicalExam);
         $form->get('submit')->setAttribute('value', 'Edit');

         $request = $this->getRequest();
         if ($request->isPost()) {
             $form->setInputFilter($practicalExam->getInputFilter());
             $form->setData($request->getPost());

             if ($form->isValid()) {
                 $this->getPracticalExamTable()->savePracticalExam($practicalExam);

                 
                 return $this->redirect()->toRoute('practical-exam');
             }
         }

         return array(
             'practice_exam_id' => $practice_exam_id,
             'form' => $form,
         );
     }

     
 }

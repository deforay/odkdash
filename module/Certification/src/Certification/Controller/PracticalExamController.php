<?php

namespace Certification\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Certification\Model\PracticalExam;

class PracticalExamController extends AbstractActionController {

    protected $practicalExamTable;

    public function getPracticalExamTable() {
        if (!$this->practicalExamTable) {
            $sm = $this->getServiceLocator();
            $this->practicalExamTable = $sm->get('Certification\Model\PracticalExamTable');
        }
        return $this->practicalExamTable;
    }

    public function indexAction() {
        $paginator = $this->getPracticalExamTable()->fetchAll(true);
        $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(10);

        return new ViewModel(array(
            'paginator' => $paginator
        ));
    }

    public function addAction() {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $id_written = (int) $this->params()->fromQuery('id_written_exam', 0);
 $provider=$this->getPracticalExamTable()->getProviderName($id_written);
        $form = new \Certification\Form\PracticalExamForm($dbAdapter);
        $form->get('submit')->setValue('Add');
        $request = $this->getRequest();

        if ($request->isPost()) {
            $practicalExam = new PracticalExam();
            $form->setInputFilter($practicalExam->getInputFilter());
            $form->setData($request->getPost());
            $written = $request->getPost('written', null);
//            $written=$_POST['written'];
            if ($form->isValid() && empty($written)) {
                $practicalExam->exchangeArray($form->getData());

                $this->getPracticalExamTable()->savePracticalExam($practicalExam);
                $last_id = $this->getPracticalExamTable()->last_id();
                $this->getPracticalExamTable()->insertToExamination($last_id);

                return $this->redirect()->toRoute('practical-exam');
            } else if ($form->isValid() && !empty($written)){
                $practicalExam->exchangeArray($form->getData());
                $this->getPracticalExamTable()->savePracticalExam($practicalExam);
                $last_id = $this->getPracticalExamTable()->last_id();
                $this->getPracticalExamTable()->examination($written, $last_id);
                return $this->redirect()->toRoute('practical-exam');
            }
        }
        $nombre = $this->getPracticalExamTable()->countWritten($id_written);
        return array('form' => $form,
            'written' => $id_written,
            'nombre' => $nombre,
             'provider' => $provider,
            
        );
    }

    public function editAction() {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $practice_exam_id = (int) $this->params()->fromRoute('practice_exam_id', 0);
        if (!$practice_exam_id) {
            return $this->redirect()->toRoute('practical-exam', array(
                        'action' => 'add'
            ));
        }

        try {
            $practicalExam = $this->getPracticalExamTable()->getPracticalExam($practice_exam_id);
        } catch (\Exception $ex) {
            return $this->redirect()->toRoute('practical-exam', array(
                        'action' => 'index'
            ));
        }

        $form = new \Certification\Form\PracticalExamForm($dbAdapter);
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

    public function searchAction() {

        $request = $this->getRequest();
        if ($request->isPost()) {
            $motCle = $request->getPost('motCle', null);
        }
     
        $paginator = $this->getPracticalExamTable()->search($motCle,true);
        $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(10);

        return new ViewModel(array(
            'paginator' => $paginator
        ));
    }

}

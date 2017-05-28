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

        return new ViewModel(array(
            'writtens' => $this->getWrittenExamTable()->fetchAll()
        ));
    }

    public function addAction() {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $practical = (int) $this->params()->fromQuery('practice_exam_id', 0);
        $provider = $this->getWrittenExamTable()->getProviderName($practical);
        $form = new WrittenExamForm($dbAdapter);
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {

            $writtenExam = new \Certification\Model\WrittenExam();
            $form->setInputFilter($writtenExam->getInputFilter());
            $form->setData($request->getPost());
            $practical = $request->getPost('practical', null);
            $provider = $this->getRequest()->getPost('provider_id');
            $written = $this->getWrittenExamTable()->counWritten($provider);

            if ($written == 0) {
                if ($form->isValid() && empty($practical)) {
                    $writtenExam->exchangeArray($form->getData());
                    $this->getWrittenExamTable()->saveWrittenExam($writtenExam);
                    $last_id = $this->getWrittenExamTable()->last_id();
                    $this->getWrittenExamTable()->insertToExamination($last_id);
                    return $this->redirect()->toRoute('written-exam');
                } else if ($form->isValid() && !empty($practical)) {
                    $writtenExam->exchangeArray($form->getData());
                    ?> <pre> <?php // print_r($writtenExam);?></pre>
                    <?php
                    $this->getWrittenExamTable()->saveWrittenExam($writtenExam);
                    $last_id = $this->getWrittenExamTable()->last_id();
                    $this->getWrittenExamTable()->examination($last_id, $practical);
                    return $this->redirect()->toRoute('written-exam');
                }
            } else {
                return array('form' => $form,
                    'message' => 'Impossible to add !!!! Because this tester has already passed a written exam, he is waiting to add the practical exam',);
            }
        }
        $nombre = $this->getWrittenExamTable()->countPractical($practical);


        return array('form' => $form,
            'practical' => $practical,
            'nombre' => $nombre,
            'provider' => $provider,
        );
    }

    public function editAction() {

        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $id_written_exam = (int) $this->params()->fromRoute('id_written_exam', 0);
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

}

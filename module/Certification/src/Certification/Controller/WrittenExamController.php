<?php

namespace Certification\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Certification\Form\WrittenExamForm;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

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
$this->forward()->dispatch('Certification\Controller\Certification', array('action' => 'index'));
        return new ViewModel(array(
            'writtens' => $this->getWrittenExamTable()->fetchAll()
        ));
    }

    public function addAction() {
        $this->forward()->dispatch('Certification\Controller\Certification', array('action' => 'index'));
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

        $practical = (int) base64_decode($this->params()->fromQuery(base64_encode('practice_exam_id'), 0));
        $provider = $this->getWrittenExamTable()->getProviderName($practical);
        $form = new WrittenExamForm($dbAdapter);
        $form->get('submit')->setValue('SUBMIT');
        $container = new Container('alert');
        $request = $this->getRequest();
        if ($request->isPost()) {

            $writtenExam = new \Certification\Model\WrittenExam();
            $form->setInputFilter($writtenExam->getInputFilter());
            $form->setData($request->getPost());
            $practical = $request->getPost('practical', null);
            $provider_id = $this->getRequest()->getPost('provider_id');
            $written = $this->getWrittenExamTable()->counWritten($provider_id);

            $nb_days = $this->getWrittenExamTable()->numberOfDays($provider_id);
            if (isset($nb_days) && $nb_days <= 30) {

                $container->alertMsg = 'la derniere tentative de ce provider remonte a ' . $nb_days . ' jours. vous devez attendre au moin 30 jours pour une autre tentative.';
                return array(
                    'form' => $form,);
            } else {

                if ($written == 0) {
                    if ($form->isValid() && empty($practical)) {
                        $writtenExam->exchangeArray($form->getData());
                        $this->getWrittenExamTable()->saveWrittenExam($writtenExam);
                        $last_id = $this->getWrittenExamTable()->last_id();
                        $this->getWrittenExamTable()->insertToExamination($last_id);
                        $container->alertMsg = 'Written exam added successfully';
                        return $this->redirect()->toRoute('written-exam', array('action'=> 'add'));
                    } else if ($form->isValid() && !empty($practical)) {
                        $writtenExam->exchangeArray($form->getData());
                        $this->getWrittenExamTable()->saveWrittenExam($writtenExam);
                        $last_id = $this->getWrittenExamTable()->last_id();
                        $nombre2 = $this->getWrittenExamTable()->countPractical2($practical);
                        if ($nombre2 == 0) {
                            $this->getWrittenExamTable()->examination($last_id, $practical);
                        } else {
                            $this->getWrittenExamTable()->insertToExamination($last_id);
                        }
                        $container->alertMsg = 'written exam added successfully';
                        return $this->redirect()->toRoute('written-exam', array('action' => 'add'));
                    }
                } else {
                    $container->alertMsg = 'Impossible to add !!!! Because this tester has already passed a written exam, he is waiting to add the practical exam';
                    return array('form' => $form);
                }
            }
        }
        $nombre = null;
        if (isset($provider['id'])) {
            $nombre = $this->getWrittenExamTable()->countPractical($practical, $provider['id']);
        }

        return array('form' => $form,
            'practical' => $practical,
            'nombre' => $nombre,
            'provider' => $provider,
        );
    }

    public function editAction() {
        $this->forward()->dispatch('Certification\Controller\Certification', array('action' => 'index'));

        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $id_written_exam = (int) base64_decode($this->params()->fromRoute('id_written_exam', 0));
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
        $form->get('submit')->setAttribute('value', 'UPDATE');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($writtenExam->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getWrittenExamTable()->saveWrittenExam($writtenExam);
                $container = new Container('alert');
                $container->alertMsg = 'Written exam updated successfully';

                return $this->redirect()->toRoute('written-exam');
            }
        }

        return array(
            'id_written_exam' => $id_written_exam,
            'form' => $form,
        );
    }

}

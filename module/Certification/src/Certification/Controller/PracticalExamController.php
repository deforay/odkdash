<?php

namespace Certification\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Certification\Model\PracticalExam;
use Zend\Session\Container;

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
        $this->forward()->dispatch('Certification\Controller\Certification', array('action' => 'index'));

        return new ViewModel(array(
            'practicals' => $this->getPracticalExamTable()->fetchAll(),
        ));
    }

    public function addAction() {
        $this->forward()->dispatch('Certification\Controller\Certification', array('action' => 'index'));
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $id_written = (int) base64_decode($this->params()->fromQuery(base64_encode('id_written_exam'), 0));
        $provider = $this->getPracticalExamTable()->getProviderName($id_written);
        $form = new \Certification\Form\PracticalExamForm($dbAdapter);
        $form->get('submit')->setValue('SUBMIT');
        $container = new Container('alert');
        $request = $this->getRequest();

        if ($request->isPost()) {
            $practicalExam = new PracticalExam();
            $form->setInputFilter($practicalExam->getInputFilter());
            $form->setData($request->getPost());
            $written = $request->getPost('written', null);
//            $written=$_POST['written'];
            $provider_id = $this->getRequest()->getPost('provider_id');
            $practical_nb = $this->getPracticalExamTable()->counPractical($provider_id);
            $nb_days = $this->getPracticalExamTable()->numberOfDays($provider_id);
            if (isset($nb_days) && $nb_days <= 30) {
                $container->alertMsg = 'la derniere tentative de ce provider remonte a ' . $nb_days . ' jours. vous devez attendre au moin 30 jours pour une autre tentative.';
                return array(
                    'form' => $form);
            } else {
                if ($practical_nb == 0) {
                    if ($form->isValid() && empty($written)) {
                        $practicalExam->exchangeArray($form->getData());
                        $this->getPracticalExamTable()->savePracticalExam($practicalExam);
                        $last_id = $this->getPracticalExamTable()->last_id();
                        $this->getPracticalExamTable()->insertToExamination($last_id);
                        $container->alertMsg = 'Practical exam added successfully';
                        return $this->redirect()->toRoute('practical-exam', array('action' => 'add'));
                    } else if ($form->isValid() && !empty($written)) {
                        $practicalExam->exchangeArray($form->getData());
                        $this->getPracticalExamTable()->savePracticalExam($practicalExam);
                        $last_id = $this->getPracticalExamTable()->last_id();
                        $nombre2 = $this->getPracticalExamTable()->countWritten2($id_written);
                        if ($nombre2 == 0) {
                            $this->getPracticalExamTable()->examination($written, $last_id);
                        } else {
                            $this->getPracticalExamTable()->insertToExamination($last_id);
                        }
                        $container->alertMsg = 'Practical exam added successfully';
                        return $this->redirect()->toRoute('practical-exam', array('action' => 'add'));
                    }
                } else {
                    $container->alertMsg = 'Impossible to add !!!! Because this tester has already passed a practical exam, he is waiting to add the written exam';
                    return array('form' => $form);
                }
            }
        }
        $nombre = null;
        if (isset($provider['id'])) {
            $nombre = $this->getPracticalExamTable()->attemptNumber($provider['id']);
        }
        return array('form' => $form,
            'written' => $id_written,
            'nombre' => $nombre,
            'provider' => $provider,
        );
    }

    public function editAction() {
        $this->forward()->dispatch('Certification\Controller\Certification', array('action' => 'index'));
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $practice_exam_id = (int) base64_decode($this->params()->fromRoute('practice_exam_id', 0));
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
        $form->get('submit')->setAttribute('value', 'UPDATE');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($practicalExam->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $this->getPracticalExamTable()->savePracticalExam($practicalExam);
                $container = new Container('alert');
                $container->alertMsg = 'Practical exam updated successfully';

                return $this->redirect()->toRoute('practical-exam');
            }
        }
$attemptNumber= $this->getPracticalExamTable()->getExamType($practice_exam_id);

        return array(
            'practice_exam_id' => $practice_exam_id,
            'form' => $form,
            'attemptNumber'=> $attemptNumber,
        );
    }

    public function attemptAction() {
        $q = (int) $_GET['q'];
        $result = $this->getPracticalExamTable()->attemptNumber($q);
        return array(
            'result' => $result,
        );
    }

}

<?php

namespace Certification\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Certification\Model\Certification;
use Certification\Form\CertificationForm;

class CertificationController extends AbstractActionController {

    protected $certificationTable;

    public function getCertificationTable() {
        if (!$this->certificationTable) {
            $sm = $this->getServiceLocator();
            $this->certificationTable = $sm->get('Certification\Model\CertificationTable');
        }
        return $this->certificationTable;
    }

    public function indexAction() {
        return new ViewModel(array(
            'certifications' => $this->getCertificationTable()->fetchAll(),
            'certifications2' => $this->getCertificationTable()->fetchAll2(),
        ));
    }

    public function addAction() {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

        $id = (int) $this->params()->fromRoute('id', 0);
        $written = $this->params()->fromQuery('written');
        $practical = $this->params()->fromQuery('practical');
        $sample = $this->params()->fromQuery('sample');
        $direct = $this->params()->fromQuery('direct');
        $provider = $this->params()->fromQuery('provider');
        $certification_id= $this->getCertificationTable()->certificationType($provider);
        
        $form = new CertificationForm($dbAdapter);
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $certification = new Certification();
            $form->setInputFilter($certification->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $certification->exchangeArray($form->getData());
                $this->getCertificationTable()->saveCertification($certification);
                $last_id = $this->getCertificationTable()->last_id();
                $this->getCertificationTable()->updateExamination($last_id);
                $this->getCertificationTable()->setToActive($last_id);
                if(empty($certification_id) && $written >= 80 && $direct >= 90 && $sample = 100){
                    $this->getCertificationTable()->certificationId($provider);
                }
                
                return $this->redirect()->toRoute('certification');
            }
        }
        return array('id' => $id,
            'written' => $written,
            'practical' => $practical,
            'sample' => $sample,
            'direct' => $direct,
            'certification_id' => $certification_id,
            'form' => $form);
    }

    public function editAction() {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('certification', array(
                        'action' => 'add'
            ));
        }

        try {
            $certification = $this->getCertificationTable()->getCertification($id);
        } catch (\Exception $ex) {
            return $this->redirect()->toRoute('certification', array(
                        'action' => 'index'
            ));
        }
        $form = new CertificationForm($dbAdapter);
        $form->bind($certification);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($certification->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getCertificationTable()->saveCertification($certification);

                return $this->redirect()->toRoute('certification');
            }
        }

        return array(
            'id' => $id,
            'form' => $form,
        );
    }

    public function pdfAction() {

        $last = $this->params()->fromQuery('last');
        $first = $this->params()->fromQuery('first');
        $middle = $this->params()->fromQuery('middle');
        $certification_id = $this->params()->fromQuery('certification_id');
        $professional_reg_no= $this->params()->fromQuery('professional_reg_no');
        $date_issued= $this->params()->fromQuery('date_issued');
        return array(
            'last' => $last,
            'first' => $first,
            'middle' => $middle,
            'professional_reg_no'=>$professional_reg_no,
            'certification_id'=>$certification_id,
            'date_issued'=>$date_issued
            );
    }

}

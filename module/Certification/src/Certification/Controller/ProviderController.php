<?php

namespace Certification\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Certification\Model\Provider;
use Certification\Form\ProviderForm;
use Zend\Session\Container;

class ProviderController extends AbstractActionController {

    protected $providerTable;

    public function getProviderTable() {
        if (!$this->providerTable) {
            $sm = $this->getServiceLocator();
            $this->providerTable = $sm->get('Certification\Model\ProviderTable');
        }
        return $this->providerTable;
    }

    public function indexAction() {
        $this->forward()->dispatch('Certification\Controller\Certification', array('action' => 'index'));
        return new ViewModel(array(
            'providers' => $this->getProviderTable()->fetchAll(),
        ));
    }

    public function addAction() {
        $this->forward()->dispatch('Certification\Controller\Certification', array('action' => 'index'));
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new ProviderForm($dbAdapter);
        $form->get('submit')->setValue('SUBMIT');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $provider = new Provider();
            $form->setInputFilter($provider->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $provider->exchangeArray($form->getData());
                ?>
                <pre> <?php // print_r($provider)                  ?></pre>

                <?php
                $this->getProviderTable()->saveProvider($provider);
                $container = new Container('alert');
                $container->alertMsg = 'New tester added successfully';
                return $this->redirect()->toRoute('provider', array('action' => 'add'));
            }
        }
        return array('form' => $form,
            'providers' => $this->getProviderTable()->fetchAll(),
        );
    }

    public function editAction() {
        $this->forward()->dispatch('Certification\Controller\Certification', array('action' => 'index'));
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

        $id = (int) base64_decode($this->params()->fromRoute('id', 0));

        if (!$id) {
            return $this->redirect()->toRoute('provider', array(
                        'action' => 'add'
            ));
        }

        try {
            $provider = $this->getProviderTable()->getProvider($id);
//            die(print_r($provider));
        } catch (\Exception $ex) {
            return $this->redirect()->toRoute('provider', array(
                        'action' => 'index'
            ));
        }

        $form = new ProviderForm($dbAdapter);

        $form->bind($provider);
        $form->get('submit')->setAttribute('value', 'UPDATE');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($provider->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getProviderTable()->saveProvider($provider);
                $container = new Container('alert');
                $container->alertMsg = 'Tester updated successfully';
                return $this->redirect()->toRoute('provider');
            }
        }
        $district = $this->getProviderTable()->DistrictName($provider->district);
        $facility = $this->getProviderTable()->FacilityName($provider->facility_id);

        return array(
            'id' => $id,
            'form' => $form,
            'district_id' => $district['district_id'],
            'district_name' => $district['district_name'],
            'facility_id' => $facility['facility_id'],
            'facility_name' => $facility['facility_name'],
        );
    }

    public function districtAction() {

        $q = (int) $_GET['q'];
        $result = $this->getProviderTable()->getDistrict($q);
        return array(
            'result' => $result,
        );
    }

    public function facilityAction() {

        $q = (int) $_GET['q'];
        $result = $this->getProviderTable()->getFacility($q);
        return array(
            'result' => $result,
        );
    }
    
    public function  deleteAction(){
        $id = (int) $this->params()->fromRoute('id', 0);

        if (!$id) {
            return $this->redirect()->toRoute('provider');
        } else {
            $forein_key1= $this->getProviderTable()->foreigne_key1($id);
            $forein_key2= $this->getProviderTable()->foreigne_key2($id);
            if($forein_key1==0 || $forein_key2==0){
            $this->getProviderTable()->deleteProvider($id);
            $container = new Container('alert');
            $container->alertMsg = 'Deleted successfully';
             return $this->redirect()->toRoute('provider');
        } else {
            $container = new Container('alert');
            $container->alertMsg = 'Unable to remove this provider because he has already completed an exam.';
             return $this->redirect()->toRoute('provider');
        }
        }
    }
    
     public function xlsAction() {
        $paginator =$this->getProviderTable()->fetchAll();
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Cretification registration no');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Certification id');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Professional registration no');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'First name');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Last name');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Middle name');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Region');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'District');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'Type of vih test');
        $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'Phone');
        $objPHPExcel->getActiveSheet()->SetCellValue('K1', 'Prefered contact method');
        $objPHPExcel->getActiveSheet()->SetCellValue('L1', 'Current job title');
        $objPHPExcel->getActiveSheet()->SetCellValue('M1', 'Time worked as tester');
        $objPHPExcel->getActiveSheet()->SetCellValue('N1', 'Testing site in charge name');
        $objPHPExcel->getActiveSheet()->SetCellValue('O1', 'Testing site in charge phone');
        $objPHPExcel->getActiveSheet()->SetCellValue('P1', 'Testing site in charge email');
        $objPHPExcel->getActiveSheet()->SetCellValue('Q1', 'Facility in charge name');
        $objPHPExcel->getActiveSheet()->SetCellValue('R1', 'Facility in charge phone');
        $objPHPExcel->getActiveSheet()->SetCellValue('S1', 'Facility in charge email');
        $objPHPExcel->getActiveSheet()->SetCellValue('T1', 'facility');
        

        
         $ligne=2;
       foreach ($paginator as $orga)
//           
        {
           $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $ligne, $orga->certification_reg_no);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $ligne, $orga->certification_id);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $ligne, $orga->professional_reg_no);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $ligne, $orga->first_name);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $ligne, $orga->last_name);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $ligne, $orga->middle_name);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $ligne, $orga->region);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $ligne, $orga->district);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $ligne, $orga->type_vih_test);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $ligne, $orga->phone);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $ligne, $orga->email);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $ligne, $orga->prefered_contact_method);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, $ligne, $orga->current_jod);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $ligne, $orga->time_worked);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $ligne, $orga->test_site_in_charge_name);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, $ligne, $orga->test_site_in_charge_phone);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(15, $ligne, $orga->test_site_in_charge_email);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(16, $ligne, $orga->facility_in_charge_name);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(17, $ligne, $orga->facility_in_charge_phone);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(18, $ligne, $orga->facility_in_charge_email);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(19, $ligne, $orga->facility_id);
            $ligne++;
        }
// 
       $objWriter = new \PHPExcel_Writer_CSV($objPHPExcel);

        $response = $this->getEvent()->getResponse();
        $response->getHeaders()->clearHeaders()->addHeaders(array(
            'Pragma' => 'public',
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="test.xls"',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Transfer-Encoding' => 'binary',
        ));
        $objWriter->save('php://output');
        return $response;
  
    
    }


}

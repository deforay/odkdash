<?php

namespace Certification\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Certification\Model\Certification;
use Certification\Form\CertificationForm;
use Zend\Session\Container;

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
        $nb = $this->getCertificationTable()->countCertificate();
        $nb2 = $this->getCertificationTable()->countReminder();
        $this->layout()->setVariable('nb', $nb);
        $this->layout()->setVariable('nb2', $nb2);
        $certification_id = (int) base64_decode($this->params()->fromQuery(base64_encode('certification_id'), null));
        $key = base64_decode($this->params()->fromQuery(base64_encode('key'), null));
        if (!empty($certification_id) && !empty($key)) {
            $this->getCertificationTable()->CertificateSent($certification_id);
            $container = new Container('alert');
            $container->alertMsg = 'Perform successfully';
            return $this->redirect()->toRoute('certification', array(
                        'action' => 'edit',
                        'certification_id' => base64_encode($certification_id)));
        } else {

            return new ViewModel(array(
                'certifications' => $this->getCertificationTable()->fetchAll(),
                'certifications2' => $this->getCertificationTable()->fetchAll2(),
                'certifications3' => $this->getCertificationTable()->fetchAll3(),
            ));
        }
    }

    public function addAction() {
        $this->indexAction();
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

        $id = (int) base64_decode($this->params()->fromRoute('id', 0));
//        die($id);
        $written = (int) base64_decode($this->params()->fromQuery(base64_encode('written')));
        $practical = (int) base64_decode($this->params()->fromQuery(base64_encode('practical')));
        $sample = (int) base64_decode($this->params()->fromQuery(base64_encode('sample')));
        $direct = (int) base64_decode($this->params()->fromQuery(base64_encode('direct')));
        if (!$id || !$written || !$practical || !$sample || !$direct) {

            return $this->redirect()->toRoute('examination');
        }
        $provider = (int) base64_decode($this->params()->fromQuery(base64_encode('provider')));
        $certification_id = $this->getCertificationTable()->certificationType($provider);

        $form = new CertificationForm($dbAdapter);
        $form->get('submit')->setValue('Submit');

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
                if (empty($certification_id) && $written >= 80 && $direct >= 90 && $sample = 100) {
                    $this->getCertificationTable()->certificationId($provider);
                }
                $container = new Container('alert');
                $container->alertMsg = 'Added successfully';
                return $this->redirect()->toRoute('examination');
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
        $this->indexAction();
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $id = (int) base64_decode($this->params()->fromRoute('id', 0));
        if (!$id) {
            return $this->redirect()->toRoute('certification', array(
                        'action' => 'index'));
        }

        try {
            $certification = $this->getCertificationTable()->getCertification($id);
        } catch (\Exception $ex) {
            return $this->redirect()->toRoute('certification', array(
                        'action' => 'index'
            ));
        }
        $certification->date_certificate_issued = date("d-m-Y", strtotime($certification->date_certificate_issued));
        if (isset($certification->date_certificate_sent)) {
            $certification->date_certificate_sent = date("d-m-Y", strtotime($certification->date_certificate_sent));
        }
        $form = new CertificationForm($dbAdapter);
        $form->bind($certification);
        $form->get('submit')->setAttribute('value', 'UPDATE');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($certification->getInputFilter());
            $form->setData($request->getPost());


            if ($form->isValid()) {
                $this->getCertificationTable()->saveCertification($certification);
                $container = new Container('alert');
                $container->alertMsg = 'updated successfully';
                return $this->redirect()->toRoute('certification');
            }
        }

        return array(
            'id' => $id,
            'form' => $form,
        );
    }

    public function pdfAction() {
        $last = base64_decode($this->params()->fromQuery(base64_encode('last')));
        $first = base64_decode($this->params()->fromQuery(base64_encode('first')));
        $middle = base64_decode($this->params()->fromQuery(base64_encode('middle')));
        $certification_id = base64_decode($this->params()->fromQuery(base64_encode('certification_id')));
        $professional_reg_no = base64_decode($this->params()->fromQuery(base64_encode('professional_reg_no')));
        $date_issued = base64_decode($this->params()->fromQuery(base64_encode('date_issued')));
        return array(
            'last' => $last,
            'first' => $first,
            'middle' => $middle,
            'professional_reg_no' => $professional_reg_no,
            'certification_id' => $certification_id,
            'date_issued' => $date_issued
        );
    }

    public function xlsAction() {
        $regions = $this->getCertificationTable()->getRegions();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $decision = $request->getPost('decision');
            $typeHiv = $request->getPost('typeHIV');
            $jobTitle = $request->getPost('jobTitle');
            $dateRange = $request->getPost('dateRange');
            if (!empty($dateRange)) {

                $array = explode(" ", $dateRange);
                $startDate = date("Y-m-d", strtotime($array[0]));
                $endDate = date("Y-m-d", strtotime($array[2]));
            } else {
//                die('nnnnnnnnnnnnn');
                $startDate = "";
                $endDate = "";
            }

            $region = $request->getPost('region');
            $district = $request->getPost('district');
            $facility = $request->getPost('facility');
//            if (empty($facility)){
//                $facility="";
//            }
            $result = $this->getCertificationTable()->report($startDate, $endDate, $decision, $typeHiv, $jobTitle, $region, $district, $facility);
//        die(print_r($result));
            $objPHPExcel = new \PHPExcel();
            $objPHPExcel->setActiveSheetIndex(0);
            $sheet = $objPHPExcel->getActiveSheet();
            $sheet->getStyle('A')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Certification registration no');
            $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Certification id');
            $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Professional registration no');
            $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'First name');
            $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Last name');
            $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Middle name');
            $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Region');
            $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'District');
            $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'Type of vih test');
            $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'Phone');
            $objPHPExcel->getActiveSheet()->SetCellValue('k1', 'Email');
            $objPHPExcel->getActiveSheet()->SetCellValue('L1', 'Prefered contact method');
            $objPHPExcel->getActiveSheet()->SetCellValue('M1', 'Current job title');
            $objPHPExcel->getActiveSheet()->SetCellValue('N1', 'Time worked as tester');
            $objPHPExcel->getActiveSheet()->SetCellValue('O1', 'Facility Name');
            $objPHPExcel->getActiveSheet()->SetCellValue('P1', 'Testing site in charge name');
            $objPHPExcel->getActiveSheet()->SetCellValue('Q1', 'Testing site in charge phone');
            $objPHPExcel->getActiveSheet()->SetCellValue('R1', 'Testing site in charge email');
            $objPHPExcel->getActiveSheet()->SetCellValue('S1', 'Facility in charge name');
            $objPHPExcel->getActiveSheet()->SetCellValue('T1', 'Facility in charge phone');
            $objPHPExcel->getActiveSheet()->SetCellValue('U1', 'Facility in charge email');
            $objPHPExcel->getActiveSheet()->SetCellValue('V1', 'Practical exam date');
            $objPHPExcel->getActiveSheet()->SetCellValue('W1', 'Practical exam admin');
            $objPHPExcel->getActiveSheet()->SetCellValue('X1', 'Practical exam number of attempt');
            $objPHPExcel->getActiveSheet()->SetCellValue('Y1', 'Sample testing score');
            $objPHPExcel->getActiveSheet()->SetCellValue('Z1', 'Direct observation score');
            $objPHPExcel->getActiveSheet()->SetCellValue('AA1', 'Practical exam score');
            $objPHPExcel->getActiveSheet()->SetCellValue('AB1', 'Written exam date');
            $objPHPExcel->getActiveSheet()->SetCellValue('AC1', 'Written exam admin');
            $objPHPExcel->getActiveSheet()->SetCellValue('AD1', 'Written exam number of attempt');
            $objPHPExcel->getActiveSheet()->SetCellValue('AE1', 'QA (points)');
            $objPHPExcel->getActiveSheet()->SetCellValue('AF1', 'RT (points)');
            $objPHPExcel->getActiveSheet()->SetCellValue('AG1', 'Safety (points)');
            $objPHPExcel->getActiveSheet()->SetCellValue('AH1', 'Specimen collection (points)');
            $objPHPExcel->getActiveSheet()->SetCellValue('AI1', 'Testing algorithm (points)');
            $objPHPExcel->getActiveSheet()->SetCellValue('AJ1', 'Record keeping (points)');
            $objPHPExcel->getActiveSheet()->SetCellValue('AK1', 'EQA/PT (points)');
            $objPHPExcel->getActiveSheet()->SetCellValue('AL1', 'Ethics (points)');
            $objPHPExcel->getActiveSheet()->SetCellValue('AM1', 'Inevntory (points)');
            $objPHPExcel->getActiveSheet()->SetCellValue('AN1', 'total point');
            $objPHPExcel->getActiveSheet()->SetCellValue('AO1', 'Written exam score');
            $objPHPExcel->getActiveSheet()->SetCellValue('AP1', 'Final score');
            $objPHPExcel->getActiveSheet()->SetCellValue('AQ1', 'Final decision');
            $objPHPExcel->getActiveSheet()->SetCellValue('AR1', 'Type of certification');
            $objPHPExcel->getActiveSheet()->SetCellValue('AS1', 'Date certificate issued');
            $objPHPExcel->getActiveSheet()->SetCellValue('AT1', 'certificate issuer');
            $objPHPExcel->getActiveSheet()->SetCellValue('AU1', 'Due date');

            $ligne = 2;
            foreach ($result as $result) {
////           
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $ligne, $result['certification_reg_no']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $ligne, $result['certification_id']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $ligne, $result['professional_reg_no']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $ligne, $result['first_name']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $ligne, $result['last_name']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $ligne, $result['middle_name']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $ligne, $result['region_name']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $ligne, $result['district_name']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $ligne, $result['type_vih_test']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $ligne, $result['phone']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $ligne, $result['email']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, $ligne, $result['prefered_contact_method']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $ligne, $result['current_jod']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $ligne, $result['time_worked']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, $ligne, $result['facility_name']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(15, $ligne, $result['test_site_in_charge_name']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(16, $ligne, $result['test_site_in_charge_phone']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(17, $ligne, $result['test_site_in_charge_email']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(18, $ligne, $result['facility_in_charge_name']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(19, $ligne, $result['facility_in_charge_phone']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(20, $ligne, $result['facility_in_charge_email']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(21, $ligne, $result['practical_exam_date']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(22, $ligne, $result['practical_exam_admin']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(23, $ligne, $result['practical_exam_type']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(24, $ligne, $result['direct_observation_score'] . ' %');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(25, $ligne, $result['Sample_testing_score'] . ' %');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(26, $ligne, $result['practical_total_score'] . ' %');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(27, $ligne, $result['written_exam_date']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(28, $ligne, $result['written_exam_admin']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(29, $ligne, $result['written_exam_type']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(30, $ligne, $result['qa_point']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(31, $ligne, $result['rt_point']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(32, $ligne, $result['safety_point']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(33, $ligne, $result['specimen_point']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(34, $ligne, $result['testing_algo_point']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(35, $ligne, $result['report_keeping_point']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(36, $ligne, $result['EQA_PT_points']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(37, $ligne, $result['ethics_point']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(38, $ligne, $result['inventory_point']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(39, $ligne, $result['total_points']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(40, $ligne, $result['final_score'] . '  %');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(41, $ligne, ($result['practical_total_score'] + $result['final_score']) / 2 . '  %');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(42, $ligne, $result['final_decision']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(43, $ligne, $result['certification_type']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(44, $ligne, $result['date_certificate_issued']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(45, $ligne, $result['certification_issuer']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(46, $ligne, $result['date_end_validity']);

                $ligne++;
            }


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

        return array(
            'regions' => $regions,
        );
    }

}

<?php

namespace Application\Service;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Session\Container;
use PHPExcel;

class OdkFormService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }

    
    public function saveSpiFormVer3($params) {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->saveData($params);
    }
    
    public function getPerformance($params) {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->getPerformance($params);
    }
   
    
    public function getPerformanceLast30Days($params) {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->getPerformanceLast30Days($params);
    }
   
    
    public function getPerformanceLast180Days($params = null) {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->getPerformanceLast180Days();
    }
    
    public function getAllSubmissions($sortOrder = 'DESC') {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->getAllSubmissions($sortOrder);
    }
    
    public function getAllSubmissionsDetails($params) {
        $db = $this->sm->get('SpiFormVer3Table');
        $acl = $this->sm->get('AppAcl');
        return $db->fetchAllSubmissionsDetails($params,$acl);
    }
    
    public function getAllSubmissionsDatas($params) {
        $db = $this->sm->get('SpiFormVer3Table');
        $acl = $this->sm->get('AppAcl');
        return $db->fetchAllSubmissionsDatas($params,$acl);
    }
    //get pending facility names
    public function getPendingFacilityNames()
    {
     $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchPendingFacilityNames();   
    }
    //get all facility names
    public function getAllFacilityNames()
    {
     $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchAllFacilityNames();   
    }
     //merge all facility name
    public function mergeFacilityName($params){
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->mergeFacilityName($params);  
    }
    
    public function getAuditRoundWiseData($params) {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->getAuditRoundWiseData($params);
    }
    
    public function getFormData($id) {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->getFormData($id);
    }
    
    public function getSpiV3FormLabels() {
        $db = $this->sm->get('SpiFormLabelsTable');
        return $db->getAllLabels();
    }
    
    public function approveFormStatus($params){
        //\Zend\Debug\Debug::dump(count($params['idList']));die;
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
            $db = $this->sm->get('SpiFormVer3Table');
            if(isset($params['idList']) && $params['idList']!='')
            {
                for($i=0;$i<count($params['idList']);$i++){
                $result = $db->updateFormStatus($params['idList'][$i],'approved');
                $facilityDb->addFacilityBasedOnForm($params['idList'][$i]);
                }
            }
            if(isset($params['id'])){
            $result = $db->updateFormStatus($params['id'],'approved');
            $facilityDb->addFacilityBasedOnForm($params['id']);
            }
            if ($result > 0) {
                $adapter->commit();
                return $result;
            }
        } catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getAllApprovedSubmissions($sortOrder = 'DESC') {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchAllApprovedSubmissions($sortOrder);
    }
    
    public function getAllApprovedSubmissionsTable($params) {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fecthAllApprovedSubmissionsTable($params);
    }
    //export facilty report
    public function exportFacilityReport($params)
    {
        try{
            $common = new \Application\Service\CommonService();
            $queryContainer = new Container('query');
            $excel = new PHPExcel();
            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
            $cacheSettings = array('memoryCacheSize' => '80MB');
            \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
            $output = array();
            $sheet = $excel->getActiveSheet();
            $dbAdapter = $this->sm->get('Zend\Db\Adapter\Adapter');
            $sql = new Sql($dbAdapter);
            if (isset($params['dateRange']) && ($params['dateRange'] != "")) {
		$dateRangeDate = explode(" - ", $params['dateRange']);
		if (isset($dateRangeDate[0]) && trim($dateRangeDate[0]) != "") {
		    $fromDate = $dateRangeDate[0];
		}
		if (isset($dateRangeDate[1]) && trim($dateRangeDate[1]) != "") {
		    $toDate = $dateRangeDate[1];
		}
                if($fromDate == $toDate){
               $displayDate="Date : ".$fromDate;
                }else{
                    $displayDate="Date : ".$fromDate." to ".$toDate;
                }
            }else{
                $displayDate="";
            }
           
            $sQueryStr = $sql->getSqlStringForSqlObject($queryContainer->exportQuery);
            
            $sResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
            if(count($sResult) > 0) {
                
                foreach($sResult as $aRow) {
                    $row = array();
                    $row[] = $aRow['facilityname'];
                    $row[] = $common->humanDateFormat($aRow['assesmentofaudit']);
                    $row[] = $aRow['testingpointname']. " - " .$aRow['testingpointtype'];
                    $row[] = $aRow['PERSONAL_SCORE'];
                    $row[] = $aRow['PHYSICAL_SCORE'];
                    $row[] = $aRow['SAFETY_SCORE'];
                    $row[] = $aRow['PRETEST_SCORE'];
                    $row[] = $aRow['TEST_SCORE'];
                    $row[] = $aRow['POST_SCORE'];
                    $row[] = $aRow['EQA_SCORE'];
                    $row[] = $aRow['FINAL_AUDIT_SCORE'];
                    $row[] = round($aRow['AUDIT_SCORE_PERCANTAGE'],2);
                    $output[] = $row;
               }
            }
            $styleArray = array(
                'font' => array(
                    'bold' => true,
                    'size'=>12,
                ),
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                ),
                'borders' => array(
                    'outline' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THICK,
                    ),
                )
            );
           $borderStyle = array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'borders' => array(
                    'outline' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_MEDIUM,
                    ),
                )
            );
           
            $sheet->mergeCells('A1:B1');
            $sheet->mergeCells('A2:B2');
            $sheet->mergeCells('A4:A5');
            $sheet->mergeCells('B4:B5');
            $sheet->mergeCells('C4:C5');
            $sheet->mergeCells('D4:D5');
            $sheet->mergeCells('E4:E5');
            $sheet->mergeCells('F4:F5');
            $sheet->mergeCells('G4:G5');
            $sheet->mergeCells('H4:H5');
            $sheet->mergeCells('I4:I5');
            $sheet->mergeCells('J4:J5');
            $sheet->mergeCells('K4:K5');
            $sheet->mergeCells('L4:L5');
            
            $sheet->setCellValue('A1', html_entity_decode('Facility Report', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('A2', html_entity_decode($displayDate, ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
           
            $sheet->setCellValue('A4', html_entity_decode('Facility name', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('B4', html_entity_decode('Audit Date', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('C4', html_entity_decode('Testing Point', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('D4', html_entity_decode('Personnel Training & Certification', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('E4', html_entity_decode('Physical', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('F4', html_entity_decode('Safety', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('G4', html_entity_decode('Pre-Testing', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('H4', html_entity_decode('Testing', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('I4', html_entity_decode('Post-Testing', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('J4', html_entity_decode('External QA', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('K4', html_entity_decode('Total', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('L4', html_entity_decode('% Scores', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            
            
            $sheet->getStyle('A1:B1')->getFont()->setBold(TRUE)->setSize(16);
            $sheet->getStyle('A2:B2')->getFont()->setBold(TRUE)->setSize(13);
            
            $sheet->getStyle('A4:A5')->applyFromArray($styleArray);
            $sheet->getStyle('B4:B5')->applyFromArray($styleArray);
            $sheet->getStyle('C4:C5')->applyFromArray($styleArray);
            $sheet->getStyle('D4:D5')->applyFromArray($styleArray);
            $sheet->getStyle('E4:E5')->applyFromArray($styleArray);
            $sheet->getStyle('F4:F5')->applyFromArray($styleArray);
            $sheet->getStyle('G4:G5')->applyFromArray($styleArray);
            $sheet->getStyle('H4:H5')->applyFromArray($styleArray);
            $sheet->getStyle('I4:I5')->applyFromArray($styleArray);
            $sheet->getStyle('J4:J5')->applyFromArray($styleArray);
            $sheet->getStyle('K4:K5')->applyFromArray($styleArray);
            $sheet->getStyle('L4:L5')->applyFromArray($styleArray);
            
            $start=0;
            foreach ($output as $rowNo => $rowData) {
                $colNo = 0;
                foreach ($rowData as $field => $value) {
                    if (!isset($value)) {
                        $value = "";
                    }
                    if (is_numeric($value)) {
                        $sheet->getCellByColumnAndRow($colNo, $rowNo + 6)->setValueExplicit(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    } else {
                        $sheet->getCellByColumnAndRow($colNo, $rowNo + 6)->setValueExplicit(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
                    }
                    $rRowCount = $rowNo + 6;
                    $cellName = $sheet->getCellByColumnAndRow($colNo, $rowNo + 6)->getColumn();
                    $sheet->getStyle($cellName . $rRowCount)->applyFromArray($borderStyle);
                    $sheet->getDefaultRowDimension()->setRowHeight(18);
                    $sheet->getColumnDimensionByColumn($colNo)->setWidth(20);
                    $sheet->getStyleByColumnAndRow($colNo, $rowNo + 6)->getAlignment()->setWrapText(true);
                    $colNo++;
                }
	    }
	    
            $writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel5');
            $filename = 'facility-report-' . date('d-M-Y-H-i-s') . '.xls';
            $writer->save(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename);
            return $filename;
        }
        catch (Exception $exc) {
            return "";
            error_log("GENERATE-FACILITY-REPORT-EXCEL--" . $exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getAllApprovedSubmissionLocation($params) {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchAllApprovedSubmissionLocation($params);
    }
    
    public function getZeroQuestionCounts($params) {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->getZeroQuestionCounts($params);
    }
    
    public function getAllApprovedTestingVolume($params) {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->getAllApprovedTestingVolume($params);
    }
    
    public function getSpiV3PendingCount() {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->getSpiV3PendingCount();
    }
    
    public function updateSpiForm($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $db = $this->sm->get('SpiFormVer3Table');
            $result = $db->updateSpiFormDetails($params);
            if ($result > 0) {
                $adapter->commit();
                $container = new Container('alert');
                $container->alertMsg = 'Form details updated successfully';
                return $result;
            }
        } catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    //get all audit round no
    public function getSpiV3FormAuditNo(){
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchSpiV3FormAuditNo();
    }
    
    public function getFacilitiesAudits($params){
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchFacilitiesAudits($params);
    }
    
    public function deleteAuditData($params){
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->deleteAuditRowData($params);
    }
    
    public function getSpiV3FormFacilityAuditNo($params){
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchSpiV3FormFacilityAuditNo($params);
    }
    
    public function getAllApprovedSubmissionsDetailsBasedOnAuditDate($params) {
        $db = $this->sm->get('SpiFormVer3Table');
        $acl = $this->sm->get('AppAcl');
        return $db->fetchAllApprovedSubmissionsDetailsBasedOnAuditDate($params,$acl);
    }
    
    public function getSpiV3FormUniqueTokens(){
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchSpiV3FormUniqueTokens();
    }
    
    public function getViewDataDetails($params){
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchViewDataDetails($params);
    }
}
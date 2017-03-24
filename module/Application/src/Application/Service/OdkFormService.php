<?php

namespace Application\Service;

use Zend\Session\Container;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use PHPExcel;
use pData;
use pDraw;
use pRadar;
use pImage;
use pPie;

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
    //export all submissions
    public function exportAllSubmissions($params)
    {
        try{
            $common = new \Application\Service\CommonService();
            $queryContainer = new Container('query');
            $excel = new PHPExcel();
            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
            $cacheSettings = array('memoryCacheSize' => '80MB');
            \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
            $output = array();
            $outputScore = array();
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
                $displayDate="Date Range : ".$fromDate;
                }else{
                    $displayDate="Date Range : ".$fromDate." to ".$toDate;
                }
            }else{
                $displayDate="";
            }
            $auditRndNo = '';$levelData = '';$affiliation = '';$province = '';$scoreLevel = '';$testPoint = '';
            if (isset($params['auditRndNo']) && ($params['auditRndNo'] != "")) {
                $auditRndNo = "Audit Round No. : ". $params['auditRndNo'];
            }
            if (isset($params['level']) && ($params['level'] != "")) {
                $levelData = "Level : ". $params['level'];
            }
            if (isset($params['affiliation']) && ($params['affiliation'] != "")) {
                $affiliation = "Affiliation : ".$params['affiliation'];
            }
            if (isset($params['province']) && ($params['province'] != "")) {
                $province = "Province/District(s) : ". implode(',',$params['province']);
            }
            if (isset($params['scoreLevel']) && ($params['scoreLevel'] != "")) {
                $scoreLevel = "Score Level : ". $params['scoreLevel'];
            }
            if (isset($params['testPoint']) && ($params['testPoint'] != "")) {
                $testPoint = "Type of Testing Point : ".$params['testPoint'];
            }
            
            $sQueryStr = $sql->getSqlStringForSqlObject($queryContainer->exportAllDataQuery);
            
            $sResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
            if(count($sResult) > 0) {
                $auditScore = 0;
                $levelZero = array();
                $levelOne = array();
                $levelTwo = array();
                $levelThree = array();
                $levelFour = array();
                for($l=0;$l<count($sResult);$l++){
                    $row = array();
                    foreach($sResult[$l] as $key=>$aRow) {
                        if($key!='id' && $key!='content' && $key!='token'){
                            
                            if($key=='AUDIT_SCORE_PERCANTAGE'){
                                $auditScore+=$sResult[$l][$key];
                                if($sResult[$l][$key] < 40){
                                    $levelZero[] = $sResult[$l][$key];
                                }
                                else if($sResult[$l][$key] >= 40 && $sResult[$l][$key] < 60){
                                    $levelOne[] = $sResult[$l][$key];
                                }
                                else if($sResult[$l][$key] >= 60 && $sResult[$l][$key] < 80){
                                    $levelTwo[] = $sResult[$l][$key];
                                }
                                else if($sResult[$l][$key] >= 80 && $sResult[$l][$key] < 90){
                                    $levelThree[] = $sResult[$l][$key];
                                }
                                else if($sResult[$l][$key] >= 90){
                                    $levelFour[] = $sResult[$l][$key];
                                }
                            }
                            if($key=='level_other'){
                               $level = " - " .$sResult[$l][$key];
                            }else{
                               $level = '';
                            }
                            if($key=='today'){
                                $sResult[$l][$key] = $common->humanDateFormat($sResult[$l][$key]);
                            }else if($key=='assesmentofaudit'){
                                $sResult[$l][$key] = $common->humanDateFormat($sResult[$l][$key]);
                            }
                            $row[] = $sResult[$l][$key].$level;
                        }
                    }
                    $output[] = $row;
                }
                
                $outputScore['avgAuditScore'] = (count($sResult) > 0) ? round($auditScore/count($sResult),2) : 0;
                $outputScore['levelZeroCount'] = count($levelZero);
                $outputScore['levelOneCount'] = count($levelOne);
                $outputScore['levelTwoCount'] = count($levelTwo);
                $outputScore['levelThreeCount'] = count($levelThree);
                $outputScore['levelFourCount'] = count($levelFour);
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
            $sheet->mergeCells('A2:B2');$sheet->mergeCells('C2:D2');
            $sheet->mergeCells('E2:F2');$sheet->mergeCells('G2:H2');$sheet->mergeCells('I2:J2');$sheet->mergeCells('K2:L2');$sheet->mergeCells('M2:N2');
            $sheet->mergeCells('A4:A5');
            $sheet->mergeCells('B4:B5');
            $sheet->mergeCells('C4:C5');
            $sheet->mergeCells('D4:D5');
            $sheet->mergeCells('E4:E5');
            $sheet->mergeCells('F4:F5');
            $sheet->mergeCells('G4:G5');
            $sheet->mergeCells('H4:H5');
            $sheet->mergeCells('I4:I5');
            
            $sheet->setCellValue('A1', html_entity_decode('Facility Report', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('A2', html_entity_decode($displayDate, ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('C2', html_entity_decode($auditRndNo, ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('E2', html_entity_decode($levelData, ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('G2', html_entity_decode($affiliation, ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('I2', html_entity_decode($province, ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('K2', html_entity_decode($scoreLevel, ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('M2', html_entity_decode($testPoint, ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
           
           $colmnNo = 0;
           $rowmnNo = 4;
           $rowmnNo1 = 5;
           foreach($sResult[0] as $key=>$aRow) {
            if($key!='id' && $key!='content' && $key!='token'){
            $cellName = $sheet->getCellByColumnAndRow($colmnNo, $rowmnNo)->getColumn();
            $sheet->mergeCells($cellName.$rowmnNo.':'.$cellName.$rowmnNo1);
            $sheet->setCellValue($cellName.$rowmnNo, html_entity_decode($key, ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getStyle($cellName.$rowmnNo.':'.$cellName.$rowmnNo1)->applyFromArray($styleArray);
            $colmnNo++;
            }
           }
            $sheet->getStyle('A1:B1')->getFont()->setBold(TRUE)->setSize(16);
            $sheet->getStyle('A2:B2')->getFont()->setBold(TRUE)->setSize(13);
            $sheet->getStyle('C2:D2')->getFont()->setBold(TRUE)->setSize(13);
            $sheet->getStyle('E2:F2')->getFont()->setBold(TRUE)->setSize(13);
            $sheet->getStyle('G2:H2')->getFont()->setBold(TRUE)->setSize(13);
            $sheet->getStyle('I2:J2')->getFont()->setBold(TRUE)->setSize(13);
            $sheet->getStyle('K2:L2')->getFont()->setBold(TRUE)->setSize(13);
            $sheet->getStyle('M2:N2')->getFont()->setBold(TRUE)->setSize(13);
            
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
            $rCount = $rRowCount+3;
            
            $sheet->setCellValue('A'.$rCount, html_entity_decode('No.of Audit(s) : ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('B'.$rCount, html_entity_decode(count($sResult)." ", ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getStyle('A'.$rCount.':B'.$rCount)->getFont()->setBold(TRUE)->setSize(13);
            $sheet->setCellValue('C'.$rCount, html_entity_decode('Avg. Audit Score : ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('D'.$rCount, html_entity_decode($outputScore['avgAuditScore']." %", ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getStyle('C'.$rCount.':D'.$rCount)->getFont()->setBold(TRUE)->setSize(13);
            $sheet->getStyle('E'.$rCount)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000');
            $sheet->setCellValue('E'.$rCount, html_entity_decode('Level 0(Below 40) : ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('F'.$rCount, html_entity_decode($outputScore['levelZeroCount']." ", ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getStyle('E'.$rCount.':F'.$rCount)->getFont()->setBold(TRUE)->setSize(13);
            $sheet->getStyle('G'.$rCount)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FF808000');
            $sheet->setCellValue('G'.$rCount, html_entity_decode('Level 1(40-59) : ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('H'.$rCount, html_entity_decode($outputScore['levelOneCount']." ", ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getStyle('G'.$rCount.':H'.$rCount)->getFont()->setBold(TRUE)->setSize(13);
            $sheet->getStyle('I'.$rCount)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
            $sheet->setCellValue('I'.$rCount, html_entity_decode('Level 2(60-79) : ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('J'.$rCount, html_entity_decode($outputScore['levelTwoCount']." ", ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getStyle('I'.$rCount.':J'.$rCount)->getFont()->setBold(TRUE)->setSize(13);
            $sheet->getStyle('K'.$rCount)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FF00FF00');
            $sheet->setCellValue('K'.$rCount, html_entity_decode('Level 3(80-89) : ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('L'.$rCount, html_entity_decode($outputScore['levelThreeCount']." ", ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getStyle('K'.$rCount.':L'.$rCount)->getFont()->setBold(TRUE)->setSize(13);
            $sheet->getStyle('M'.$rCount)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FF008000');
            $sheet->setCellValue('M'.$rCount, html_entity_decode('Level 4(90) : ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('N'.$rCount, html_entity_decode($outputScore['levelFourCount']." ", ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getStyle('M'.$rCount.':N'.$rCount)->getFont()->setBold(TRUE)->setSize(13);
            
            $writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel5');
            $filename = 'SPI-RT--CHECKLIST-version-3-' . time() . '.csv';
            $writer->save(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename);
            return $filename;
        }
        catch (Exception $exc) {
            return "";
            error_log("SPI-RT--CHECKLIST-version-3-REPORT-EXCEL--" . $exc->getMessage());
            error_log($exc->getTraceAsString());
        }
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
    //download pie chart
    public function getPerformancePieChart($params) {
        $db = $this->sm->get('SpiFormVer3Table');
        $result = $db->getPerformance($params);
        $MyData = new pData();
        
        if(count($result)>0){
            foreach($result as $key=>$data){
                $MyData->addPoints(array($data['level0'],$data['level1'],$data['level2'],$data['level3'],$data['level4']),"Level".$key);
                $MyData->setSerieDescription("Level".$key);
                $rgbColor = array();
                //Create a loop.
                foreach(array('r', 'g', 'b') as $color){
                    //Generate a random number between 0 and 255.
                    $rgbColor[$color] = mt_rand(0, 255);
                }
                $MyData->setPalette("Level".$key,array("R"=>$rgbColor['r'],"G"=>$rgbColor['g'],"B"=>$rgbColor['b']));
            }
        }
        
        /* Define the absissa serie */
        $MyData->addPoints(array("Level 0 (Below 40)","Level 1 (40-59)","Level 2 (60-79)","Level 3 (80-89)","Level 4 (90 and above)"),"Labels");
        $MyData->setAbscissa("Labels");
       
        /* Create the pChart object */
        $myPicture = new pImage(230,510,$MyData);
        $myPicture->drawRectangle(0,0,220,500,array("R"=>0,"G"=>0,"B"=>0));
        $path = font_path . DIRECTORY_SEPARATOR;
        
        /* Set the default font properties */ 
        $myPicture->setFontProperties(array("FontName"=>$path."/Forgotte.ttf","FontSize"=>13,"R"=>80,"G"=>80,"B"=>80));
       
        /* Enable shadow computing */ 
        $myPicture->setShadow(TRUE,array("X"=>0,"Y"=>0,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>0));
       
        $PieChart = new pPie($myPicture,$MyData);
        $PieChart->draw2DPie(105,150,array("Radius"=>95,"Border"=>TRUE));
        $PieChart->drawPieLegend(40,300);
        $fileName =  'piechart.png';
        $result = $myPicture->autoOutput(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "piechart.png");
        return $fileName;
    }
    
    //download spider chart pdf
    public function getAuditRoundWiseDataChart($params)
    {
        $db = $this->sm->get('SpiFormVer3Table');
        $result = $db->getAuditRoundWiseData($params);
        $MyData = new pData();
        /* Create and populate the pData object */
        $filename = '';
        if(count($result)>0){
            foreach ($result as $auditNo => $adata){
                $MyData->addPoints(array(round($adata['PERSONAL_SCORE'],2),round($adata['PHYSICAL_SCORE'],2),round($adata['SAFETY_SCORE'],2),round($adata['PRETEST_SCORE'],2),round($adata['TEST_SCORE'],2),round($adata['POST_SCORE'],2),round($adata['EQA_SCORE'],2)),"Score".$auditNo);
                $MyData->setSerieDescription("Score".$auditNo,$auditNo);
                $rgbColor = array();
                //Create a loop.
                foreach(array('r', 'g', 'b') as $color){
                    //Generate a random number between 0 and 255.
                    $rgbColor[$color] = mt_rand(0, 255);
                }
                $MyData->setPalette("Score".$auditNo,array("R"=>$rgbColor['r'],"G"=>$rgbColor['g'],"B"=>$rgbColor['b']));
            }
        }
            /* Define the absissa serie */
            $MyData->addPoints(array("Personnel Training & Certification", "Physical", "Safety", "Pre-Testing", "Testing", "Post Testing Phase", "External Quality Audit"),"Label");
            $MyData->setAbscissa("Label");

            /* Create the pChart object */
            $myPicture = new pImage(450,480,$MyData);
            //$myPicture->drawGradientArea(0,0,450,50,DIRECTION_VERTICAL,array("StartR"=>400,"StartG"=>400,"StartB"=>400,"EndR"=>480,"EndG"=>480,"EndB"=>480,"Alpha"=>0));
            //$myPicture->drawGradientArea(0,0,450,25,DIRECTION_HORIZONTAL,array("StartR"=>60,"StartG"=>60,"StartB"=>60,"EndR"=>200,"EndG"=>200,"EndB"=>200,"Alpha"=>0));
            //$myPicture->drawLine(0,25,450,25,array("R"=>255,"G"=>255,"B"=>255));
            //$RectangleSettings = array("R"=>180,"G"=>180,"B"=>180,"Alpha"=>50);
           
            /* Add a border to the picture */
            $myPicture->drawRectangle(0,0,398,478,array("R"=>0,"G"=>0,"B"=>0));
           
            $path = font_path . DIRECTORY_SEPARATOR;
            /* Write the picture title */ 
            //$myPicture->setFontProperties(array("FontName"=>$path."/Silkscreen.ttf","FontSize"=>6));
            //$myPicture->drawText(10,13,"pRadar - Draw radar charts",array("R"=>255,"G"=>255,"B"=>255));
           
            /* Set the default font properties */ 
            $myPicture->setFontProperties(array("FontName"=>$path."/Forgotte.ttf","FontSize"=>15,"R"=>80,"G"=>80,"B"=>80));
           
            /* Enable shadow computing */ 
            $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
           
            /* Create the pRadar object */ 
            $SplitChart = new pRadar();
            /* Draw a radar chart */ 
            $myPicture->setGraphArea(15,15,370,370);
            $Options = array("Layout"=>RADAR_LAYOUT_STAR,"BackgroundGradient"=>array("StartR"=>510,"StartG"=>510,"StartB"=>510,"StartAlpha"=>10,"EndR"=>414,"EndG"=>454,"EndB"=>250,"EndAlpha"=>10), "FontName"=>$path."/pf_arma_five.ttf","FontSize"=>15);
            $SplitChart->drawRadar($myPicture,$MyData,$Options);
           
            /* Write the chart legend */
            $myPicture->setFontProperties(array("FontName"=>$path."/pf_arma_five.ttf","FontSize"=>7));
            $myPicture->drawLegend(230,370,array("Style"=>LEGEND_BOX,"Mode"=>LEGEND_VERTICAL));
           
            /* Render the picture (choose the best way) */
            $fileName =  'radar.png';
            $result = $myPicture->autoOutput(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "radar.png");
            return $fileName;
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
                    $auditDate="";
                    if(isset($aRow['assesmentofaudit']) && trim($aRow['assesmentofaudit'])!=""){
                        $auditDate=$common->humanDateFormat($aRow['assesmentofaudit']);
                    }
                    $row = array();
                    $row[] = $aRow['facilityname'];
                    $row[] = $auditDate;
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
    
    public function getAllTestingPointType(){
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchAllTestingPointType();
    }
    
    public function getTestingPointTypeNamesByType($params){
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchTestingPointTypeNamesByType($params);
    }
    
    public function getSpiV3FormUniqueLevelNames(){
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchSpiV3FormUniqueLevelNames();
    }
}
<?php

namespace Application\Model;

use Zend\Session\Container;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Zend\Db\TableGateway\AbstractTableGateway;
use Application\Model\SpiRtFacilitiesTable;
use Application\Model\GlobalTable;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Countries
 *
 * @author amit
 */
class SpiFormVer3Table extends AbstractTableGateway {

    protected $table = 'spi_form_v_3';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
      
    public function saveData($params) {
       
        $sql = new Sql($this->adapter);
        $insert = $sql->insert('form_dump');
        $d = array('data_dump' => json_encode($params) , 'received_on' => new \Zend\Db\Sql\Expression("NOW()"));
        $dbAdapter = $this->adapter;
        $insert->values($d);
        $selectString = $sql->getSqlStringForSqlObject($insert);
        $results = $dbAdapter->query($selectString, $dbAdapter::QUERY_MODE_EXECUTE);
       
       //get global values
       $globalDB = new GlobalTable($dbAdapter);
       $globalValue = $globalDB->getGlobalValue('approve_status');
       if($globalValue=='yes')
       {
        $approveStatus = 'approved';
       }else{
        $approveStatus = 'pending';
       }
       
        foreach($params['data'] as $datar){
            $par = array();
            $data = array();
            foreach($datar as $key=>$val){
               $key = preg_replace('/[\*]+/', '', $key);
                $key = str_replace("lab_geopoint:","",$key);
                if(is_array($val)){
                    $val = json_encode($val);
                }
                $data[$key] = $val."";
            
            }
        
            try{
               
        
        $sql = new Sql($this->adapter);
    
    
        $insert = $sql->insert('spi_form_v_3');
        if(isset($data['testingpointname']) && trim($data['testingpointname'])==""){
            $data['testingpointname']=$data['testingpointtype'];
        }
        
        $par = array(
                'token' => $params['token'],
                'content' => $params['content'],
                'formId' => $params['formId'],
                'formVersion' => $params['formVersion'],
                'meta-instance-id' => $data['meta-instance-id'],
                'meta-model-version' => $data['meta-model-version'],
                'meta-ui-version' => $data['meta-ui-version'],
                'meta-submission-date' => $data['meta-submission-date'],
                'meta-is-complete' => $data['meta-is-complete'],
                'meta-date-marked-as-complete' => $data['meta-date-marked-as-complete'],
                'start' => $data['start'],
                'end' => $data['end'],
                'today' => $data['today'],
                'deviceid' => $data['deviceid'],
                'subscriberid' => $data['subscriberid'],
                'text_image' => $data['text_image'],
                'info1' => $data['info1'],
                'info2' => $data['info2'],
                'assesmentofaudit' => $data['assesmentofaudit'],
                'auditroundno' => $data['auditroundno'],
                'facilityname' => $data['facilityname'],
                'facilityid' => $data['facilityid'],
                'testingpointname' => $data['testingpointname'],
                'testingpointtype' => $data['testingpointtype'],
                'testingpointtype_other' => $data['testingpointtype_other'],
                'locationaddress' => $data['locationaddress'],
                'level' => $data['level'],
                'level_other' => $data['level_other'],
                'level_name' => $data['level_name'],
                'affiliation' => $data['affiliation'],
                'affiliation_other' => $data['affiliation_other'],
                'NumberofTester' => $data['NumberofTester'],
                'avgMonthTesting' => $data['avgMonthTesting'],
                'name_auditor_lead' => $data['name_auditor_lead'],
                'name_auditor2' => $data['name_auditor2'],
                'info4' => $data['info4'],
                'INSTANCE' => $data['INSTANCE'],
                'PERSONAL_Q_1_1' => $data['PERSONAL_Q_1_1'],
                'PERSONAL_C_1_1' => $data['PERSONAL_C_1_1'],
                'PERSONAL_Q_1_2' => $data['PERSONAL_Q_1_2'],
                'PERSONAL_C_1_2' => $data['PERSONAL_C_1_2'],
                'PERSONAL_Q_1_3' => $data['PERSONAL_Q_1_3'],
                'PERSONAL_C_1_3' => $data['PERSONAL_C_1_3'],
                'PERSONAL_Q_1_4' => $data['PERSONAL_Q_1_4'],
                'PERSONAL_C_1_4' => $data['PERSONAL_C_1_4'],
                'PERSONAL_Q_1_5' => $data['PERSONAL_Q_1_5'],
                'PERSONAL_C_1_5' => $data['PERSONAL_C_1_5'],
                'PERSONAL_Q_1_6' => $data['PERSONAL_Q_1_6'],
                'PERSONAL_C_1_6' => $data['PERSONAL_C_1_6'],
                'PERSONAL_Q_1_7' => $data['PERSONAL_Q_1_7'],
                'PERSONAL_C_1_7' => $data['PERSONAL_C_1_7'],
                'PERSONAL_Q_1_8' => $data['PERSONAL_Q_1_8'],
                'PERSONAL_C_1_8' => $data['PERSONAL_C_1_8'],
                'PERSONAL_Q_1_9' => $data['PERSONAL_Q_1_9'],
                'PERSONAL_C_1_9' => $data['PERSONAL_C_1_9'],
                'PERSONAL_Q_1_10' => $data['PERSONAL_Q_1_10'],
                'PERSONAL_C_1_10' => $data['PERSONAL_C_1_10'],
                'PERSONAL_SCORE' => $data['PERSONAL_SCORE'],
                'PERSONAL_Display' => $data['PERSONAL_Display'],
                'PERSONALPHOTO' => $data['PERSONALPHOTO'],
                'PHYSICAL_Q_2_1' => $data['PHYSICAL_Q_2_1'],
                'PHYSICAL_C_2_1' => $data['PHYSICAL_C_2_1'],
                'PHYSICAL_Q_2_2' => $data['PHYSICAL_Q_2_2'],
                'PHYSICAL_C_2_2' => $data['PHYSICAL_C_2_2'],
                'PHYSICAL_Q_2_3' => $data['PHYSICAL_Q_2_3'],
                'PHYSICAL_C_2_3' => $data['PHYSICAL_C_2_3'],
                'PHYSICAL_Q_2_4' => $data['PHYSICAL_Q_2_4'],
                'PHYSICAL_C_2_4' => $data['PHYSICAL_C_2_4'],
                'PHYSICAL_Q_2_5' => $data['PHYSICAL_Q_2_5'],
                'PHYSICAL_C_2_5' => $data['PHYSICAL_C_2_5'],
                'PHYSICAL_SCORE' => $data['PHYSICAL_SCORE'],
                'PHYSICAL_Display' => $data['PHYSICAL_Display'],
                'PHYSICALPHOTO' => $data['PHYSICALPHOTO'],
                'SAFETY_Q_3_1' => $data['SAFETY_Q_3_1'],
                'SAFETY_C_3_1' => $data['SAFETY_C_3_1'],
                'SAFETY_Q_3_2' => $data['SAFETY_Q_3_2'],
                'SAFETY_C_3_2' => $data['SAFETY_C_3_2'],
                'SAFETY_Q_3_3' => $data['SAFETY_Q_3_3'],
                'SAFETY_C_3_3' => $data['SAFETY_C_3_3'],
                'SAFETY_Q_3_4' => $data['SAFETY_Q_3_4'],
                'SAFETY_C_3_4' => $data['SAFETY_C_3_4'],
                'SAFETY_Q_3_5' => $data['SAFETY_Q_3_5'],
                'SAFETY_C_3_5' => $data['SAFETY_C_3_5'],
                'SAFETY_Q_3_6' => $data['SAFETY_Q_3_6'],
                'SAFETY_C_3_6' => $data['SAFETY_C_3_6'],
                'SAFETY_Q_3_7' => $data['SAFETY_Q_3_7'],
                'SAFETY_C_3_7' => $data['SAFETY_C_3_7'],
                'SAFETY_Q_3_8' => $data['SAFETY_Q_3_8'],
                'SAFETY_C_3_8' => $data['SAFETY_C_3_8'],
                'SAFETY_Q_3_9' => $data['SAFETY_Q_3_9'],
                'SAFETY_C_3_9' => $data['SAFETY_C_3_9'],
                'SAFETY_Q_3_10' => $data['SAFETY_Q_3_10'],
                'SAFETY_C_3_10' => $data['SAFETY_C_3_10'],
                'SAFETY_Q_3_11' => $data['SAFETY_Q_3_11'],
                'SAFETY_C_3_11' => $data['SAFETY_C_3_11'],
                'SAFETY_SCORE' => $data['SAFETY_SCORE'],
                'SAFETY_DISPLAY' => $data['SAFETY_DISPLAY'],
                'SAFETYPHOTO' => $data['SAFETYPHOTO'],
                'PRE_Q_4_1' => $data['PRE_Q_4_1'],
                'PRE_C_4_1' => $data['PRE_C_4_1'],
                'PRE_Q_4_2' => $data['PRE_Q_4_2'],
                'PRE_C_4_2' => $data['PRE_C_4_2'],
                'PRE_Q_4_3' => $data['PRE_Q_4_3'],
                'PRE_C_4_3' => $data['PRE_C_4_3'],
                'PRE_Q_4_4' => $data['PRE_Q_4_4'],
                'PRE_C_4_4' => $data['PRE_C_4_4'],
                'PRE_Q_4_5' => $data['PRE_Q_4_5'],
                'PRE_C_4_5' => $data['PRE_C_4_5'],
                'PRE_Q_4_6' => $data['PRE_Q_4_6'],
                'PRE_C_4_6' => $data['PRE_C_4_6'],
                'PRE_Q_4_7' => $data['PRE_Q_4_7'],
                'PRE_C_4_7' => $data['PRE_C_4_7'],
                'PRE_Q_4_8' => $data['PRE_Q_4_8'],
                'PRE_C_4_8' => $data['PRE_C_4_8'],
                'PRE_Q_4_9' => $data['PRE_Q_4_9'],
                'PRE_C_4_9' => $data['PRE_C_4_9'],
                'PRE_Q_4_10' => $data['PRE_Q_4_10'],
                'PRE_C_4_10' => $data['PRE_C_4_10'],
                'PRE_Q_4_11' => $data['PRE_Q_4_11'],
                'PRE_C_4_11' => $data['PRE_C_4_11'],
                'PRE_Q_4_12' => $data['PRE_Q_4_12'],
                'PRE_C_4_12' => $data['PRE_C_4_12'],
                'PRETEST_SCORE' => $data['PRETEST_SCORE'],
                'PRETEST_Display' => $data['PRETEST_Display'],
                'PRETESTPHOTO' => $data['PRETESTPHOTO'],
                'TEST_Q_5_1' => $data['TEST_Q_5_1'],
                'TEST_C_5_1' => $data['TEST_C_5_1'],
                'TEST_Q_5_2' => $data['TEST_Q_5_2'],
                'TEST_C_5_2' => $data['TEST_C_5_2'],
                'TEST_Q_5_3' => $data['TEST_Q_5_3'],
                'TEST_C_5_3' => $data['TEST_C_5_3'],
                'TEST_Q_5_4' => $data['TEST_Q_5_4'],
                'TEST_C_5_4' => $data['TEST_C_5_4'],
                'TEST_Q_5_5' => $data['TEST_Q_5_5'],
                'TEST_C_5_5' => $data['TEST_C_5_5'],
                'TEST_Q_5_6' => $data['TEST_Q_5_6'],
                'TEST_C_5_6' => $data['TEST_C_5_6'],
                'TEST_Q_5_7' => $data['TEST_Q_5_7'],
                'TEST_C_5_7' => $data['TEST_C_5_7'],
                'TEST_Q_5_8' => $data['TEST_Q_5_8'],
                'TEST_C_5_8' => $data['TEST_C_5_8'],
                'TEST_Q_5_9' => $data['TEST_Q_5_9'],
                'TEST_C_5_9' => $data['TEST_C_5_9'],
                'TEST_SCORE' => $data['TEST_SCORE'],
                'TEST_DISPLAY' => $data['TEST_DISPLAY'],
                'TESTPHOTO' => $data['TESTPHOTO'],
                'POST_Q_6_1' => $data['POST_Q_6_1'],
                'POST_C_6_1' => $data['POST_C_6_1'],
                'POST_Q_6_2' => $data['POST_Q_6_2'],
                'POST_C_6_2' => $data['POST_C_6_2'],
                'POST_Q_6_3' => $data['POST_Q_6_3'],
                'POST_C_6_3' => $data['POST_C_6_3'],
                'POST_Q_6_4' => $data['POST_Q_6_4'],
                'POST_C_6_4' => $data['POST_C_6_4'],
                'POST_Q_6_5' => $data['POST_Q_6_5'],
                'POST_C_6_5' => $data['POST_C_6_5'],
                'POST_Q_6_6' => $data['POST_Q_6_6'],
                'POST_C_6_6' => $data['POST_C_6_6'],
                'POST_Q_6_7' => $data['POST_Q_6_7'],
                'POST_C_6_7' => $data['POST_C_6_7'],
                'POST_Q_6_8' => $data['POST_Q_6_8'],
                'POST_C_6_8' => $data['POST_C_6_8'],
                'POST_Q_6_9' => $data['POST_Q_6_9'],
                'POST_C_6_9' => $data['POST_C_6_9'],
                'POST_SCORE' => $data['POST_SCORE'],
                'POST_DISPLAY' => $data['POST_DISPLAY'],
                'POSTTESTPHOTO' => $data['POSTTESTPHOTO'],
                'EQA_Q_7_1' => $data['EQA_Q_7_1'],
                'EQA_C_7_1' => $data['EQA_C_7_1'],
                'EQA_Q_7_2' => $data['EQA_Q_7_2'],
                'EQA_C_7_2' => $data['EQA_C_7_2'],
                'EQA_Q_7_3' => $data['EQA_Q_7_3'],
                'EQA_C_7_3' => $data['EQA_C_7_3'],
                'EQA_Q_7_4' => $data['EQA_Q_7_4'],
                'EQA_C_7_4' => $data['EQA_C_7_4'],
                'EQA_Q_7_5' => $data['EQA_Q_7_5'],
                'EQA_C_7_5' => $data['EQA_C_7_5'],
                'EQA_Q_7_6' => $data['EQA_Q_7_6'],
                'EQA_C_7_6' => $data['EQA_C_7_6'],
                'EQA_Q_7_7' => $data['EQA_Q_7_7'],
                'EQA_C_7_7' => $data['EQA_C_7_7'],
                'EQA_Q_7_8' => $data['EQA_Q_7_8'],
                'EQA_C_7_8' => $data['EQA_C_7_8'],
                'sampleretesting' => $data['sampleretesting'],
                'EQA_Q_7_9' => $data['EQA_Q_7_9'],
                'EQA_C_7_9' => $data['EQA_C_7_9'],
                'EQA_Q_7_10' => $data['EQA_Q_7_10'],
                'EQA_C_7_10' => $data['EQA_C_7_10'],
                'EQA_Q_7_11' => $data['EQA_Q_7_11'],
                'EQA_C_7_11' => $data['EQA_C_7_11'],
                'EQA_Q_7_12' => $data['EQA_Q_7_12'],
                'EQA_C_7_12' => $data['EQA_C_7_12'],
                'EQA_Q_7_13' => $data['EQA_Q_7_13'],
                'EQA_C_7_13' => $data['EQA_C_7_13'],
                'EQA_Q_7_14' => $data['EQA_Q_7_14'],
                'EQA_C_7_14' => $data['EQA_C_7_14'],
                'EQA_MAX_SCORE' => $data['EQA_MAX_SCORE'],
                'EQA_REQ' => $data['EQA_REQ'],
                'EQA_OPT' => $data['EQA_OPT'],
                'EQA_SCORE' => $data['EQA_SCORE'],
                'EQA_DISPLAY' => $data['EQA_DISPLAY'],
                'EQAPHOTO' => $data['EQAPHOTO'],
                'FINAL_AUDIT_SCORE' => $data['FINAL_AUDIT_SCORE'],
                'MAX_AUDIT_SCORE' => $data['MAX_AUDIT_SCORE'],
                'AUDIT_SCORE_PERCANTAGE' => $data['AUDIT_SCORE_PERCANTAGE'],
                'staffaudited' => $data['staffaudited'],
                'durationaudit' => $data['durationaudit'],
                'personincharge' => $data['personincharge'],
                'endofsurvey' => $data['endofsurvey'],
                'info5' => $data['info5'],
                'info6' => $data['info6'],
                'info10' => $data['info10'],
                'info11' => $data['info11'],
                'summarypage' => $data['summarypage'],
                'SUMMARY_NOT_AVL' => $data['SUMMARY_NOT_AVL'],
                'info12' => $data['info12'],
                'info17' => $data['info17'],
                'info21' => $data['info21'],
                'info22' => $data['info22'],
                'info23' => $data['info23'],
                'info24' => $data['info24'],
                'info25' => $data['info25'],
                'info26' => $data['info26'],
                'info27' => $data['info27'],
                'correctiveaction' => $data['correctiveaction'],
                'sitephoto' => $data['sitephoto'],
                'Latitude' => $data['Latitude'],
                'Longitude' => $data['Longitude'],
                'Altitude' => $data['Altitude'],
                'Accuracy' => $data['Accuracy'],
                'auditorSignature' => $data['auditorSignature'],
                'instanceID' => $data['instanceID'],
                'instanceName' => $data['instanceName'],
                'status'=>$approveStatus
            );
            $dbAdapter = $this->adapter;
            $insert->values($par);
            $selectString = $sql->getSqlStringForSqlObject($insert);
            $results = $dbAdapter->query($selectString, $dbAdapter::QUERY_MODE_EXECUTE);        
            
            if($approveStatus=='approved')
            {
                $facilityDb = new SpiRtFacilitiesTable($dbAdapter);
                $facilityResult = $facilityDb->addFacilityBasedOnForm($results->getGeneratedValue());
            }
            
            }catch(Exception $e){
                error_log($e->getMessage());
                error_log( $e->getTraceAsString());
            }
             
        }
        
        
        
    }
    
    
    public function getPerformance(){
     
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                                ->columns(array(
                                                'oldestDate' => new \Zend\Db\Sql\Expression("MIN(`assesmentofaudit`)"),
                                                'newestDate' => new \Zend\Db\Sql\Expression("MAX(`assesmentofaudit`)"),
                                                'totalDataPoints' => new \Zend\Db\Sql\Expression("COUNT(*)"),
                                                'level0' => new \Zend\Db\Sql\Expression("SUM(IF(ROUND(AUDIT_SCORE_PERCANTAGE) < 40, 1,0))"),
                                                'level1' => new \Zend\Db\Sql\Expression("SUM(IF(ROUND(AUDIT_SCORE_PERCANTAGE) >= 40 and ROUND(AUDIT_SCORE_PERCANTAGE) < 60, 1,0))"),
                                                'level2' => new \Zend\Db\Sql\Expression("SUM(IF(ROUND(AUDIT_SCORE_PERCANTAGE) >= 60 and ROUND(AUDIT_SCORE_PERCANTAGE) < 80, 1,0))"),
                                                'level3' => new \Zend\Db\Sql\Expression("SUM(IF(ROUND(AUDIT_SCORE_PERCANTAGE) >= 80 and ROUND(AUDIT_SCORE_PERCANTAGE) < 90, 1,0))"),
                                                'level4' => new \Zend\Db\Sql\Expression("SUM(IF(ROUND(AUDIT_SCORE_PERCANTAGE) >= 90, 1,0))"),
                                                ))
                                ->where(array('status'=>'approved'));
        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
        //die($sQueryStr);
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        
        return $rResult;
     
        
    }
    
    
    public function getPerformanceLast30Days(){
     
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        
        $today = date('Y-m-d');
        $last30Date = date('Y-m-d', strtotime('-30 days', strtotime($today)));
        
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                                ->columns(array(
                                                'newestDate' => new \Zend\Db\Sql\Expression("'$today'"),
                                                'oldestDate' => new \Zend\Db\Sql\Expression("'$last30Date'"),
                                                'totalDataPoints' => new \Zend\Db\Sql\Expression("COUNT(*)"),                                    
                                                'level0' => new \Zend\Db\Sql\Expression("SUM(IF(ROUND(AUDIT_SCORE_PERCANTAGE) < 40, 1,0))"),
                                                'level1' => new \Zend\Db\Sql\Expression("SUM(IF(ROUND(AUDIT_SCORE_PERCANTAGE) >= 40 and ROUND(AUDIT_SCORE_PERCANTAGE) < 60, 1,0))"),
                                                'level2' => new \Zend\Db\Sql\Expression("SUM(IF(ROUND(AUDIT_SCORE_PERCANTAGE) >= 60 and ROUND(AUDIT_SCORE_PERCANTAGE) < 80, 1,0))"),
                                                'level3' => new \Zend\Db\Sql\Expression("SUM(IF(ROUND(AUDIT_SCORE_PERCANTAGE) >= 80 and ROUND(AUDIT_SCORE_PERCANTAGE) < 90, 1,0))"),
                                                'level4' => new \Zend\Db\Sql\Expression("SUM(IF(ROUND(AUDIT_SCORE_PERCANTAGE) >= 90, 1,0))"),
                                                ))
                                ->where("status='approved'")
                                ->where("(`assesmentofaudit` BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE())");
        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
        //die($sQueryStr);
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        
        return $rResult;
     
        
    }
    
    public function getPerformanceLast180Days(){
     
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $today = date('Y-m-d');
        $last180Date = date('Y-m-d', strtotime('-180 days', strtotime($today)));        
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                                ->columns(array(
                                                'newestDate' => new \Zend\Db\Sql\Expression("'$today'"),
                                                'oldestDate' => new \Zend\Db\Sql\Expression("'$last180Date'"),
                                                'totalDataPoints' => new \Zend\Db\Sql\Expression("COUNT(*)"),
                                                'level0' => new \Zend\Db\Sql\Expression("SUM(IF(ROUND(AUDIT_SCORE_PERCANTAGE) < 40, 1,0))"),
                                                'level1' => new \Zend\Db\Sql\Expression("SUM(IF(ROUND(AUDIT_SCORE_PERCANTAGE) >= 40 and ROUND(AUDIT_SCORE_PERCANTAGE) < 60, 1,0))"),
                                                'level2' => new \Zend\Db\Sql\Expression("SUM(IF(ROUND(AUDIT_SCORE_PERCANTAGE) >= 60 and ROUND(AUDIT_SCORE_PERCANTAGE) < 80, 1,0))"),
                                                'level3' => new \Zend\Db\Sql\Expression("SUM(IF(ROUND(AUDIT_SCORE_PERCANTAGE) >= 80 and ROUND(AUDIT_SCORE_PERCANTAGE) < 90, 1,0))"),
                                                'level4' => new \Zend\Db\Sql\Expression("SUM(IF(ROUND(AUDIT_SCORE_PERCANTAGE) >= 90, 1,0))"),
                                                ))
                                ->where(array('status'=>'approved'))
                                ->where("(`assesmentofaudit` BETWEEN CURDATE() - INTERVAL 180 DAY AND CURDATE())");
        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        
        return $rResult;
     
        
    }
    
    public function getAllSubmissions($sortOrder = 'DESC'){
     
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                                ->order(array("status DESC","id $sortOrder"));
        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        
        return $rResult;
     
        
    }
    
    public function fetchAllSubmissionsDetails($parameters,$acl)
    {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
	
        $aColumns = array('id','facilityname','formId', 'formVersion','auditroundno','assesmentofaudit' ,'testingpointtype','level','affiliation','AUDIT_SCORE_PERCANTAGE','status');
        $orderColumns = array('id','facilityname','formId', 'formVersion','auditroundno','assesmentofaudit' ,'testingpointtype','level','affiliation','AUDIT_SCORE_PERCANTAGE','status');

        /*
        * Paging
        */
        $sLimit = "";
        if (isset($parameters['iDisplayStart']) && $parameters['iDisplayLength'] != '-1') {
            $sOffset = $parameters['iDisplayStart'];
            $sLimit = $parameters['iDisplayLength'];
        }

        /*
        * Ordering
        */

        $sOrder = "";
        if (isset($parameters['iSortCol_0'])) {
            for ($i = 0; $i < intval($parameters['iSortingCols']); $i++) {
                if ($parameters['bSortable_' . intval($parameters['iSortCol_' . $i])] == "true") {
                    $sOrder .= $orderColumns[intval($parameters['iSortCol_' . $i])] . " " . ( $parameters['sSortDir_' . $i] ) . ",";
                }
            }
            $sOrder = substr_replace($sOrder, "", -1);
        }

        /*
        * Filtering
        * NOTE this does not match the built-in DataTables filtering which does it
        * word by word on any field. It's possible to do here, but concerned about efficiency
        * on very large tables, and MySQL's regex functionality is very limited
        */

        $sWhere = "";
        if (isset($parameters['sSearch']) && $parameters['sSearch'] != "") {
            $searchArray = explode(" ", $parameters['sSearch']);
            $sWhereSub = "";
            foreach ($searchArray as $search) {
                if ($sWhereSub == "") {
                    $sWhereSub .= "(";
                } else {
                    $sWhereSub .= " AND (";
                }
                $colSize = count($aColumns);
 
                for ($i = 0; $i < $colSize; $i++) {
                    if ($i < $colSize - 1) {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search ) . "%' OR ";
                    } else {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search ) . "%' ";
                    }
                }
                $sWhereSub .= ")";
            }
            $sWhere .= $sWhereSub;
        }

        /* Individual column filtering */
        for ($i = 0; $i < count($aColumns); $i++) {
            if (isset($parameters['bSearchable_' . $i]) && $parameters['bSearchable_' . $i] == "true" && $parameters['sSearch_' . $i] != '') {
                if ($sWhere == "") {
                    $sWhere .= $aColumns[$i] . " LIKE '%" . ($parameters['sSearch_' . $i]) . "%' ";
                } else {
                    $sWhere .= " AND " . $aColumns[$i] . " LIKE '%" . ($parameters['sSearch_' . $i]) . "%' ";
                }
            }
        }

        /*
        * SQL queries
        * Get data to display
        */
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $start_date = "";
        $end_date = "";
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'));
       
        if (isset($parameters['date']) && ($parameters['date'] != "")) {
            $dateField = explode(" ", $parameters['date']);
            //print_r($proceed_date);die;
            if (isset($dateField[0]) && trim($dateField[0]) != "") {
                $start_date = $this->dateFormat($dateField[0]);                
            }
            if (isset($dateField[2]) && trim($dateField[2]) != "") {
                $end_date = $this->dateFormat($dateField[2]);
            }
        }
        if (trim($start_date) != "" && trim($end_date) != "") {
            $sQuery = $sQuery->where(array("spiv3.assesmentofaudit >='" . $start_date ."'", "spiv3.assesmentofaudit <='" . $end_date."'"));
        }
        if($parameters['testPoint']!=''){
         $sQuery = $sQuery->where("spiv3.testingpointtype='".$parameters['testPoint']."'");
        } if($parameters['level']!=''){
         $sQuery = $sQuery->where("spiv3.level='".$parameters['level']."'");
        } if($parameters['affiliation']!=''){
         $sQuery = $sQuery->where("spiv3.affiliation='".$parameters['affiliation']."'");
        } if($parameters['auditRndNo']!=''){
         $sQuery = $sQuery->where("spiv3.auditroundno='".$parameters['auditRndNo']."'");
        }
        
        if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }
 
        if (isset($sOrder) && $sOrder != "") {
            $sQuery->order($sOrder);
        }
 
        if (isset($sLimit) && isset($sOffset)) {
            $sQuery->limit($sLimit);
            $sQuery->offset($sOffset);
        }

        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery); // Get the string of the Sql, instead of the Select-instance 
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->getSqlStringForSqlObject($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $tQuery =  $sql->select()->from(array('spiv3' => 'spi_form_v_3'));
        $tQueryStr = $sql->getSqlStringForSqlObject($tQuery); // Get the string of the Sql, instead of the Select-instance
        $tResult = $dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);
        $iTotal = count($tResult);
        $output = array(
               "sEcho" => intval($parameters['sEcho']),
               "iTotalRecords" => $iTotal,
               "iTotalDisplayRecords" => $iFilteredTotal,
               "aaData" => array()
        );
        
        $loginContainer = new Container('credo');
        $role = $loginContainer->roleCode;
        if ($acl->isAllowed($role, 'Application\Controller\SpiV3', 'download-pdf')) {
            $downloadPdfAction = true;
        } else {
            $downloadPdfAction = false;
        }
        
        if ($acl->isAllowed($role, 'Application\Controller\SpiV3', 'approve-status')) {
            $approveStatusAction = true;
        } else {
            $approveStatusAction = false;
        }
        
        $commonService = new \Application\Service\CommonService();
        foreach ($rResult as $aRow) {
         $row = array();
         $approve = '';
         $downloadPdf="";
         
         $row['DT_RowId'] = $aRow['id'];
         if(isset($aRow['level_other']) && $aRow['level_other'] != ""){
             $level = " - " .$aRow['level_other'];
         }else{
             $level = '';
         }
         $row[] = '';
         $row[] = $aRow['facilityname'];
         $row[] = $aRow['formId'];
         $row[] = $aRow['formVersion'];
         $row[] = $aRow['auditroundno'];
         $row[] = $commonService->humanDateFormat($aRow['assesmentofaudit']);
         $row[] = $aRow['testingpointtype'];
         $row[] = $aRow['level'].$level;
         $row[] = $aRow['affiliation'];
         $row[] = round($aRow['AUDIT_SCORE_PERCANTAGE'],2);
         $row[] = ucwords($aRow['status']);
         //$print = '<a href="/spi-v3/print/' . $aRow['id'] . '" target="_blank" style="white-space:nowrap;"><i class="fa fa-print"></i> Print</a>';
        if($aRow['status']=='pending'){
            if($approveStatusAction){
            $approve = '<br><a href="javascript:void(0);" onclick="approveStatus('.$aRow['id'].')"  style="white-space:nowrap;"><i class="fa fa-check"></i>  Approve</a>';
            }
        }
         
        if($downloadPdfAction){
            $downloadPdf = '<br><a href="javascript:void(0);" onclick="downloadPdf('.$aRow['id'].')" style="white-space:nowrap;"><i class="fa fa-download"></i> PDF</a>';
        }
         //$pending = '<br><a href="/spi-v3/edit/' . $aRow['id'] . '" style="white-space:nowrap;"><i class="fa fa-pencil"></i> Edit</a>';
         $row[] = $approve." ".$downloadPdf;
         $output['aaData'][] = $row;
        }
        return $output;
    }
    
    public function fetchAllSubmissionsDatas($parameters,$acl){
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
	
        $aColumns = array('facilityname','formId', 'formVersion','auditroundno','assesmentofaudit' ,'testingpointtype','level','affiliation','AUDIT_SCORE_PERCANTAGE','status');
        $orderColumns = array('facilityname','formId', 'formVersion','auditroundno','assesmentofaudit' ,'testingpointtype','level','affiliation','AUDIT_SCORE_PERCANTAGE','status');

        /*
        * Paging
        */
       $sLimit = "";
       if (isset($parameters['iDisplayStart']) && $parameters['iDisplayLength'] != '-1') {
           $sOffset = $parameters['iDisplayStart'];
           $sLimit = $parameters['iDisplayLength'];
       }

        /*
        * Ordering
        */

       $sOrder = "";
       if (isset($parameters['iSortCol_0'])) {
           for ($i = 0; $i < intval($parameters['iSortingCols']); $i++) {
               if ($parameters['bSortable_' . intval($parameters['iSortCol_' . $i])] == "true") {
                   $sOrder .= $orderColumns[intval($parameters['iSortCol_' . $i])] . " " . ( $parameters['sSortDir_' . $i] ) . ",";
               }
           }
           $sOrder = substr_replace($sOrder, "", -1);
       }

       /*
        * Filtering
        * NOTE this does not match the built-in DataTables filtering which does it
        * word by word on any field. It's possible to do here, but concerned about efficiency
        * on very large tables, and MySQL's regex functionality is very limited
        */

       $sWhere = "";
       if (isset($parameters['sSearch']) && $parameters['sSearch'] != "") {
           $searchArray = explode(" ", $parameters['sSearch']);
           $sWhereSub = "";
           foreach ($searchArray as $search) {
               if ($sWhereSub == "") {
                   $sWhereSub .= "(";
               } else {
                   $sWhereSub .= " AND (";
               }
               $colSize = count($aColumns);

               for ($i = 0; $i < $colSize; $i++) {
                   if ($i < $colSize - 1) {
                       $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search ) . "%' OR ";
                   } else {
                       $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search ) . "%' ";
                   }
               }
               $sWhereSub .= ")";
           }
           $sWhere .= $sWhereSub;
       }

       /* Individual column filtering */
       for ($i = 0; $i < count($aColumns); $i++) {
           if (isset($parameters['bSearchable_' . $i]) && $parameters['bSearchable_' . $i] == "true" && $parameters['sSearch_' . $i] != '') {
               if ($sWhere == "") {
                   $sWhere .= $aColumns[$i] . " LIKE '%" . ($parameters['sSearch_' . $i]) . "%' ";
               } else {
                   $sWhere .= " AND " . $aColumns[$i] . " LIKE '%" . ($parameters['sSearch_' . $i]) . "%' ";
               }
           }
       }

       /*
        * SQL queries
        * Get data to display
        */
       $dbAdapter = $this->adapter;
       $sql = new Sql($dbAdapter);
       $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'));
       
       if (isset($sWhere) && $sWhere != "") {
           $sQuery->where($sWhere);
       }

       if (isset($sOrder) && $sOrder != "") {
           $sQuery->order($sOrder);
       }

       if (isset($sLimit) && isset($sOffset)) {
           $sQuery->limit($sLimit);
           $sQuery->offset($sOffset);
       }

       $sQueryStr = $sql->getSqlStringForSqlObject($sQuery); // Get the string of the Sql, instead of the Select-instance 
       //echo $sQueryStr;die;
       $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

       /* Data set length after filtering */
       $sQuery->reset('limit');
       $sQuery->reset('offset');
       $fQuery = $sql->getSqlStringForSqlObject($sQuery);
       $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
       $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $tQuery =  $sql->select()->from(array('spiv3' => 'spi_form_v_3'));
        $tQueryStr = $sql->getSqlStringForSqlObject($tQuery); // Get the string of the Sql, instead of the Select-instance
        $tResult = $dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);
        $iTotal = count($tResult);
        $output = array(
               "sEcho" => intval($parameters['sEcho']),
               "iTotalRecords" => $iTotal,
               "iTotalDisplayRecords" => $iFilteredTotal,
               "aaData" => array()
        );
        $loginContainer = new Container('credo');
        $role = $loginContainer->roleCode;
        if ($acl->isAllowed($role, 'Application\Controller\SpiV3', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
        
        if ($acl->isAllowed($role, 'Application\Controller\SpiV3', 'delete')) {
            $delete = true;
        } else {
            $delete = false;
        }
        
        if ($acl->isAllowed($role, 'Application\Controller\SpiV3', 'download-pdf')) {
            $downloadPdfAction = true;
        } else {
            $downloadPdfAction = false;
        }
        
        $commonService = new \Application\Service\CommonService();
       foreach ($rResult as $aRow) {
        $row = array();
        $downloadPdf="";
        $edit="";
        $remove="";
        $row['DT_RowId'] = $aRow['id'];
        $row[] = $aRow['facilityname'];
        $row[] = $aRow['formId'];
        $row[] = $aRow['formVersion'];
        $row[] = $aRow['auditroundno'];
        $row[] = $commonService->humanDateFormat($aRow['assesmentofaudit']);
        $row[] = $aRow['testingpointtype'];
        $row[] = $aRow['level'];
        $row[] = $aRow['affiliation'];
        $row[] = round($aRow['AUDIT_SCORE_PERCANTAGE'],2);
        $row[] = ucwords($aRow['status']);
        if($downloadPdfAction){
            $downloadPdf = '<br><a href="javascript:void(0);" onclick="downloadPdf('.$aRow['id'].')" style="white-space:nowrap;"><i class="fa fa-download"></i> PDF</a>';
        }
        if($update){
            $edit = '<br><a href="/spi-v3/edit/' . $aRow['id'] . '" style="white-space:nowrap;"><i class="fa fa-pencil"></i> Edit</a>';
        }
        if($delete){
            $remove = '<br><a href="#" style="white-space:nowrap;"> Delete</a>';
        }
        $row[] = $edit." ".$downloadPdf;
        $output['aaData'][] = $row;
       }
       return $output;
    }
    
    public function fetchPendingFacilityNames()
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                                ->where(array('spiv3.status'=>'pending'));
        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        return $rResult;
    }

    public function getFormData($id) {
        $row = $this->select(array('id' => (int) $id))->current();
        return $row;
    }

    public function getAuditRoundWiseData($params) {
        //$rResult = $this->getAllSubmissions();
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                                ->order(array("id DESC"));
        if(isset($params['roundno']) && $params['roundno']!=''){
            $roundNo = implode(",",$params['roundno']);
            $sQuery = $sQuery->where('spiv3.auditroundno IN ("' . implode('", "', $params['roundno']) . '")');
            
        }
        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        
        $response = array();
        
        foreach($rResult as $row){
            
            $response[$row['auditroundno']]['PERSONAL_SCORE'][]=  $row['PERSONAL_SCORE'];
            $response[$row['auditroundno']]['PHYSICAL_SCORE'][]=  $row['PHYSICAL_SCORE'];
            $response[$row['auditroundno']]['SAFETY_SCORE'][]=  $row['SAFETY_SCORE'];
            $response[$row['auditroundno']]['PRETEST_SCORE'][]=  $row['PRETEST_SCORE'];
            $response[$row['auditroundno']]['TEST_SCORE'][]=  $row['TEST_SCORE'];
            $response[$row['auditroundno']]['POST_SCORE'][]=  $row['POST_SCORE'];
            $response[$row['auditroundno']]['EQA_SCORE'][]=  $row['EQA_SCORE'];
        }
        
        
        $auditRoundWiseData = array();
        
        foreach($response as $auditNo => $auditScores){
            $auditRoundWiseData[$auditNo]['PERSONAL_SCORE'] = array_sum($auditScores['PERSONAL_SCORE']) / count($auditScores['PERSONAL_SCORE']);
            $auditRoundWiseData[$auditNo]['PHYSICAL_SCORE'] = array_sum($auditScores['PHYSICAL_SCORE']) / count($auditScores['PERSONAL_SCORE']);
            $auditRoundWiseData[$auditNo]['SAFETY_SCORE'] = array_sum($auditScores['SAFETY_SCORE']) / count($auditScores['PERSONAL_SCORE']);
            $auditRoundWiseData[$auditNo]['PRETEST_SCORE'] = array_sum($auditScores['PRETEST_SCORE']) / count($auditScores['PERSONAL_SCORE']);
            $auditRoundWiseData[$auditNo]['TEST_SCORE'] = array_sum($auditScores['TEST_SCORE']) / count($auditScores['PERSONAL_SCORE']);
            $auditRoundWiseData[$auditNo]['POST_SCORE'] = array_sum($auditScores['POST_SCORE']) / count($auditScores['PERSONAL_SCORE']);
            $auditRoundWiseData[$auditNo]['EQA_SCORE'] = array_sum($auditScores['EQA_SCORE']) / count($auditScores['PERSONAL_SCORE']);
        }
        
        $response = array('');
        //\Zend\Debug\Debug::dump($auditRoundWiseData);die;
        return $auditRoundWiseData;
     
    }
    

    public function getZeroQuestionCounts($params) {
        //$rResult = $this->fetchAllApprovedSubmissions();
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                                ->where(array('status'=>'approved'))
                                ->order(array("assesmentofaudit DESC"));
        if(isset($params['roundno']) && $params['roundno']!=''){
            $roundNo = implode(",",$params['roundno']);
            $sQuery = $sQuery->where('spiv3.auditroundno IN ("' . implode('", "', $params['roundno']) . '")');
        }
        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        
        $response = array();
        
        $questionColums = array(
                                'PERSONAL_Q_1_1',
                                'PERSONAL_Q_1_2',
                                'PERSONAL_Q_1_3',
                                'PERSONAL_Q_1_4',
                                'PERSONAL_Q_1_5',
                                'PERSONAL_Q_1_6',
                                'PERSONAL_Q_1_7',
                                'PERSONAL_Q_1_8',
                                'PERSONAL_Q_1_9',
                                'PERSONAL_Q_1_10',
                                'PHYSICAL_Q_2_1',
                                'PHYSICAL_Q_2_2',
                                'PHYSICAL_Q_2_3',
                                'PHYSICAL_Q_2_4',
                                'PHYSICAL_Q_2_5',
                                'SAFETY_Q_3_1',
                                'SAFETY_Q_3_2',
                                'SAFETY_Q_3_3',
                                'SAFETY_Q_3_4',
                                'SAFETY_Q_3_5',
                                'SAFETY_Q_3_6',
                                'SAFETY_Q_3_7',
                                'SAFETY_Q_3_8',
                                'SAFETY_Q_3_9',
                                'SAFETY_Q_3_10',
                                'SAFETY_Q_3_11',
                                'PRE_Q_4_1',
                                'PRE_Q_4_2',
                                'PRE_Q_4_3',
                                'PRE_Q_4_4',
                                'PRE_Q_4_5',
                                'PRE_Q_4_6',
                                'PRE_Q_4_7',
                                'PRE_Q_4_8',
                                'PRE_Q_4_9',
                                'PRE_Q_4_10',
                                'PRE_Q_4_11',
                                'PRE_Q_4_12',
                                'TEST_Q_5_1',
                                'TEST_Q_5_2',
                                'TEST_Q_5_3',
                                'TEST_Q_5_4',
                                'TEST_Q_5_5',
                                'TEST_Q_5_6',
                                'TEST_Q_5_7',
                                'TEST_Q_5_8',
                                'TEST_Q_5_9',
                                'POST_Q_6_1',
                                'POST_Q_6_2',
                                'POST_Q_6_3',
                                'POST_Q_6_4',
                                'POST_Q_6_5',
                                'POST_Q_6_6',
                                'POST_Q_6_7',
                                'POST_Q_6_8',
                                'POST_Q_6_9',
                                'EQA_Q_7_1',
                                'EQA_Q_7_2',
                                'EQA_Q_7_3',
                                'EQA_Q_7_4',
                                'EQA_Q_7_5',
                                'EQA_Q_7_6',
                                'EQA_Q_7_7',
                                'EQA_Q_7_8',
                                'EQA_Q_7_9',
                                'EQA_Q_7_10',
                                'EQA_Q_7_11',
                                'EQA_Q_7_12',
                                'EQA_Q_7_13',
                                'EQA_Q_7_14'
                                );
        
        
        
        foreach($rResult as $row){
            foreach($row as $col => $val){
                if(in_array($col,$questionColums)){
                    if($val == "0"){
                        if(isset($response[$col])){
                            $response[$col] = $response[$col] + 1;    
                        }else{
                            $response[$col] = 1;    
                        }
                        
                    }
                }
            }
        }
        arsort($response);
        return $response;
     
    }
    
    public function updateFormStatus($id,$status){
        if (trim($id) != "" && trim($status) != '') {
            $data = array('status' => $status);
            $this->update($data, array('id' => $id));
        }
        return $id;
    }
    
    public function fetchAllApprovedSubmissions($sortOrder = 'DESC'){
     
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                                ->where(array('status'=>'approved'))
                                ->order(array("assesmentofaudit $sortOrder"));
        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        
        return $rResult;
     
        
    }
    public function fetchAllApprovedSubmissionLocation($params){
     
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                                ->where(array('status'=>'approved'))
                                ->order(array("assesmentofaudit DESC"));
        if(isset($params['roundno']) && $params['roundno']!=''){
            $roundNo = implode(",",$params['roundno']);
            $sQuery = $sQuery->where('spiv3.auditroundno IN ("' . implode('", "', $params['roundno']) . '")');
        }
        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        
        return $rResult;
     
        
    }
    public function dateFormat($date) {
        if (!isset($date) || $date == null || $date == "" || $date == "0000-00-00") {
            return "0000-00-00";
        } else {
            $dateArray = explode('-', $date);
            if (sizeof($dateArray) == 0) {
                return;
            }
            $newDate = $dateArray[2] . "-";

            $monthsArray = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
            $mon = 1;
            $mon += array_search(ucfirst($dateArray[1]), $monthsArray);

            if (strlen($mon) == 1) {
                $mon = "0" . $mon;
            }
            return $newDate .= $mon . "-" . $dateArray[0];
        }
    }
    
    public function updateSpiFormDetails($params){
		
        if (trim($params['formId']) != "") {
            $dbAdapter = $this->adapter;
            $sql = new Sql($dbAdapter);
            $formId=base64_decode($params['formId']);
            $summationData="";
            if(isset($params['sectionNo'])){
                $n=count($params['sectionNo']);
                for ($i = 0; $i < $n; $i++) {
					$rowId=$params['rowId'][$i];
					if(isset($params['sectionNo'][$i]) && trim($params['sectionNo'][$i])!="" && trim($params['deficiency'][$i])!="" && trim($params['correction'.$rowId])!=""){
						$summationData[] = array(
						'sectionno' => $params['sectionNo'][$i],
						'deficiency' => $params['deficiency'][$i],
						'correction' => $params['correction'.$rowId],
						'auditorcomment' => $params['auditorComment'][$i],
						'action' => $params['action'][$i],
						'timeline' => $params['timeline'][$i],
						);
					}
                }
                $summationData=json_encode($summationData,true);
            }
            
            
            if($params['testingFacilityName']!=$params['oldFacilityName']){
                $facilityDb = new SpiRtFacilitiesTable($dbAdapter);
                if(isset($params['selectedFacilityId']) && trim($params['selectedFacilityId'])!="" && trim($params['testingFacilityName'])!=""){
                    $facilityDb->updateFacilityName($params['selectedFacilityId'],$params['testingFacilityName']);
                }else{
                    $facilityDb->addFacilityName(trim($params['testingFacilityName']));
                }
            }
            
            $data = array(
                //'assesmentofaudit' => $this->dateFormat($params['auditDate']),
                'auditroundno' => $params['auditRound'],
                'facilityname' => $params['testingFacilityName'],
                'facilityid' => $params['testingFacilityId'],
                'testingpointname' => $params['testingPointName'],
                'testingpointtype' => $params['testingPointType'],
                'testingpointtype_other' => $params['testingPointTypeOther'],
                'locationaddress' => $params['location'],
                'level' => $params['level'],
                'level_other' => $params['levelOther'],
                'level_name' => $params['levelName'],
                'affiliation' => $params['affiliation'],
                'affiliation_other' => $params['affiliationOther'],
                'NumberofTester' => $params['numberOfTester'],
                'avgMonthTesting' => $params['averageTestedPerMonth'],
                'name_auditor_lead' => $params['nameOfAuditor1'],
                'name_auditor2' => $params['nameOfAuditor2'],
                'PERSONAL_C_1_1' => $params['personal_c_1_1'],
                'PERSONAL_C_1_2' => $params['personal_c_1_2'],
                'PERSONAL_C_1_3' => $params['personal_c_1_3'],
                'PERSONAL_C_1_4' => $params['personal_c_1_4'],
                'PERSONAL_C_1_5' => $params['personal_c_1_5'],
                'PERSONAL_C_1_6' => $params['personal_c_1_6'],
                'PERSONAL_C_1_7' => $params['personal_c_1_7'],
                'PERSONAL_C_1_8' => $params['personal_c_1_8'],
                'PERSONAL_C_1_9' => $params['personal_c_1_9'],
                'PERSONAL_C_1_10' => $params['personal_c_1_10'],
                'PHYSICAL_C_2_1' => $params['physical_c_2_1'],
                'PHYSICAL_C_2_2' => $params['physical_c_2_2'],
                'PHYSICAL_C_2_3' => $params['physical_c_2_3'],
                'PHYSICAL_C_2_4' => $params['physical_c_2_4'],
                'PHYSICAL_C_2_5' => $params['physical_c_2_5'],
                'SAFETY_C_3_1' => $params['safety_c_3_1'],
                'SAFETY_C_3_2' => $params['safety_c_3_2'],
                'SAFETY_C_3_3' => $params['safety_c_3_3'],
                'SAFETY_C_3_4' => $params['safety_c_3_4'],
                'SAFETY_C_3_5' => $params['safety_c_3_5'],
                'SAFETY_C_3_6' => $params['safety_c_3_6'],
                'SAFETY_C_3_7' => $params['safety_c_3_7'],
                'SAFETY_C_3_8' => $params['safety_c_3_8'],
                'SAFETY_C_3_9' => $params['safety_c_3_9'],
                'SAFETY_C_3_10' => $params['safety_c_3_10'],
                'SAFETY_C_3_11' => $params['safety_c_3_11'],
                'PRE_C_4_1' => $params['pre_c_4_1'],
                'PRE_C_4_2' => $params['pre_c_4_2'],
                'PRE_C_4_3' => $params['pre_c_4_3'],
                'PRE_C_4_4' => $params['pre_c_4_4'],
                'PRE_C_4_5' => $params['pre_c_4_5'],
                'PRE_C_4_6' => $params['pre_c_4_6'],
                'PRE_C_4_7' => $params['pre_c_4_7'],
                'PRE_C_4_8' => $params['pre_c_4_8'],
                'PRE_C_4_9' => $params['pre_c_4_9'],
                'PRE_C_4_10' => $params['pre_c_4_10'],
                'PRE_C_4_11' => $params['pre_c_4_11'],
                'PRE_C_4_12' => $params['pre_c_4_12'],
                'TEST_C_5_1' => $params['test_c_5_1'],
                'TEST_C_5_2' => $params['test_c_5_2'],
                'TEST_C_5_3' => $params['test_c_5_3'],
                'TEST_C_5_4' => $params['test_c_5_4'],
                'TEST_C_5_5' => $params['test_c_5_5'],
                'TEST_C_5_6' => $params['test_c_5_6'],
                'TEST_C_5_7' => $params['test_c_5_7'],
                'TEST_C_5_8' => $params['test_c_5_8'],
                'TEST_C_5_9' => $params['test_c_5_9'],
                'POST_C_6_1' => $params['post_C_6_1'],
                'POST_C_6_2' => $params['post_C_6_2'],
                'POST_C_6_3' => $params['post_C_6_3'],
                'POST_C_6_4' => $params['post_C_6_4'],
                'POST_C_6_5' => $params['post_C_6_5'],
                'POST_C_6_6' => $params['post_C_6_6'],
                'POST_C_6_7' => $params['post_C_6_7'],
                'POST_C_6_8' => $params['post_C_6_8'],
                'POST_C_6_9' => $params['post_C_6_9'],
                'EQA_C_7_1' => $params['eqa_c_7_1'],
                'EQA_C_7_2' => $params['eqa_c_7_2'],
                'EQA_C_7_3' => $params['eqa_c_7_3'],
                'EQA_C_7_4' => $params['eqa_c_7_4'],
                'EQA_C_7_5' => $params['eqa_c_7_5'],
                'EQA_C_7_6' => $params['eqa_c_7_6'],
                'EQA_C_7_7' => $params['eqa_c_7_7'],
                'EQA_C_7_8' => $params['eqa_c_7_8'],
                'EQA_C_7_9' => $params['eqa_c_7_9'],
                'EQA_C_7_10' => $params['eqa_c_7_10'],
                'EQA_C_7_11' => $params['eqa_c_7_11'],
                'EQA_C_7_12' => $params['eqa_c_7_12'],
                'EQA_C_7_13' => $params['eqa_c_7_13'],
                'EQA_C_7_14' => $params['eqa_c_7_14'],
                'Latitude' => $params['latitude'],
                'Longitude' => $params['longitude'],
                
                'staffaudited' => $params['staffAuditedName'],
                'durationaudit' => $params['durationAudit'],
                //'FINAL_AUDIT_SCORE' => $params['totalPointsScored'],
                //'MAX_AUDIT_SCORE' => $params['totalScoreExpect'],
                //'AUDIT_SCORE_PERCANTAGE' => $params['auditScorePercentage'],
                'correctiveaction' => $summationData
            );
            $result = $this->update($data, array('id' =>$formId));
            
            return $formId;
        }
        
    }
    public function fetchSpiV3FormAuditNo()
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                               ->columns(array(new Expression('DISTINCT(auditroundno) as auditroundno'),'rowCount' => new Expression("COUNT('auditroundno')")))
                               ->group('auditroundno')
                               ->order("auditroundno ASC");
        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        
        return $rResult;
    }
    public function mergeFacilityName($params)
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $c = count($params['upFaciltyName']);
        for($i=0;$i<$c;$i++)
        {
            $aQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))->columns(array('facilityname','id'))
                                    ->where(array('spiv3.facilityname'=>$params['upFaciltyName'][$i]));
            $aQueryStr = $sql->getSqlStringForSqlObject($aQuery);
            $aResult = $dbAdapter->query($aQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
            
            if(count($aResult)>0){
                for($k=0;$k<count($aResult);$k++){
                    $result =  $this->update(array('facilityname'=>$params['fName']),array('id'=>$aResult[$k]['id']));
                }
            }
        }
        return $result;
    }
    //get all faciltiy name
    public function fetchAllFacilityNames(){
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $uQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))->columns(array('facilityname'=>new Expression("DISTINCT facilityname")));
        $uQueryStr = $sql->getSqlStringForSqlObject($uQuery);
        $uResult = $dbAdapter->query($uQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        
        $aQuery = $sql->select()->from(array('spiv3' => 'spi_rt_3_facilities'));
        $aQueryStr = $sql->getSqlStringForSqlObject($aQuery);
        $aResult = $dbAdapter->query($aQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        
        return array('uniqueName'=>$uResult,'allName'=>$aResult);
    }
    
    public function fetchAllTestingPointsBasedOnFacility($parameters,$acl)
    {
        
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
	
        $aColumns = array('facilityid','facilityname','testingpointname',"DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')",'AUDIT_SCORE_PERCANTAGE');
        $orderColumns = array('facilityid','facilityname','testingpointname','assesmentofaudit','AUDIT_SCORE_PERCANTAGE');

        /*
        * Paging
        */
        $sLimit = "";
        if (isset($parameters['iDisplayStart']) && $parameters['iDisplayLength'] != '-1') {
            $sOffset = $parameters['iDisplayStart'];
            $sLimit = $parameters['iDisplayLength'];
        }

        /*
        * Ordering
        */

        $sOrder = "";
        if (isset($parameters['iSortCol_0'])) {
            for ($i = 0; $i < intval($parameters['iSortingCols']); $i++) {
                if ($parameters['bSortable_' . intval($parameters['iSortCol_' . $i])] == "true") {
                    $sOrder .= $orderColumns[intval($parameters['iSortCol_' . $i])] . " " . ( $parameters['sSortDir_' . $i] ) . ",";
                }
            }
            $sOrder = substr_replace($sOrder, "", -1);
        }

        /*
        * Filtering
        * NOTE this does not match the built-in DataTables filtering which does it
        * word by word on any field. It's possible to do here, but concerned about efficiency
        * on very large tables, and MySQL's regex functionality is very limited
        */

        $sWhere = "";
        if (isset($parameters['sSearch']) && $parameters['sSearch'] != "") {
            $searchArray = explode(" ", $parameters['sSearch']);
            $sWhereSub = "";
            foreach ($searchArray as $search) {
                if ($sWhereSub == "") {
                    $sWhereSub .= "(";
                } else {
                    $sWhereSub .= " AND (";
                }
                $colSize = count($aColumns);
 
                for ($i = 0; $i < $colSize; $i++) {
                    if ($i < $colSize - 1) {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search ) . "%' OR ";
                    } else {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search ) . "%' ";
                    }
                }
                $sWhereSub .= ")";
            }
            $sWhere .= $sWhereSub;
        }

        /* Individual column filtering */
        for ($i = 0; $i < count($aColumns); $i++) {
            if (isset($parameters['bSearchable_' . $i]) && $parameters['bSearchable_' . $i] == "true" && $parameters['sSearch_' . $i] != '') {
                if ($sWhere == "") {
                    $sWhere .= $aColumns[$i] . " LIKE '%" . ($parameters['sSearch_' . $i]) . "%' ";
                } else {
                    $sWhere .= " AND " . $aColumns[$i] . " LIKE '%" . ($parameters['sSearch_' . $i]) . "%' ";
                }
            }
        }

        /*
        * SQL queries
        * Get data to display
        */
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $start_date = "";
        $end_date = "";
        $sQuery = $sql->select()->from('spi_form_v_3')->columns(array('id','facilityid','facilityname','testingpointname','testingpointtype','assesmentofaudit','AUDIT_SCORE_PERCANTAGE'));
        
        if(isset($parameters['fieldName']) && $parameters['fieldName']=='facilityId'){
            $sQuery=$sQuery->where(array('facilityid'=>$parameters['val']));
        }
        else if(isset($parameters['fieldName']) && $parameters['fieldName']=='facilityName'){
            $sQuery=$sQuery->where(array('facilityname'=>$parameters['val']));
        }
        if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }
 
        if (isset($sOrder) && $sOrder != "") {
            $sQuery->order($sOrder);
        }
 
        if (isset($sLimit) && isset($sOffset)) {
            $sQuery->limit($sLimit);
            $sQuery->offset($sOffset);
        }

        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery); // Get the string of the Sql, instead of the Select-instance 
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->getSqlStringForSqlObject($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $tQuery =  $sql->select()->from('spi_form_v_3');
        if(isset($parameters['fieldName']) && $parameters['fieldName']=='facilityId'){
            $tQuery=$tQuery->where(array('facilityid'=>$parameters['val']));
        }
        else if(isset($parameters['fieldName']) && $parameters['fieldName']=='facilityName'){
            $tQuery=$tQuery->where(array('facilityname'=>$parameters['val']));
        }
        $tQueryStr = $sql->getSqlStringForSqlObject($tQuery); // Get the string of the Sql, instead of the Select-instance
        
        $tResult = $dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);
        $iTotal = count($tResult);
        $output = array(
           "sEcho" => intval($parameters['sEcho']),
           "iTotalRecords" => $iTotal,
           "iTotalDisplayRecords" => $iFilteredTotal,
           "aaData" => array()
        );
		$loginContainer = new Container('credo');
                $role = $loginContainer->roleCode;
                if ($acl->isAllowed($role, 'Application\Controller\SpiV3', 'download-pdf')) {
                    $downloadPdfAction = true;
                } else {
                    $downloadPdfAction = false;
                }
		$commonService = new \Application\Service\CommonService();
		foreach ($rResult as $aRow) {
		 $row = array();
		 $downloadPdf="";
		 
		 $row[] = $aRow['facilityid'];
		 $row[] = ucwords($aRow['facilityname']);
                 $row[] = $aRow['testingpointtype'];
		 $row[] = $commonService->humanDateFormat($aRow['assesmentofaudit']);
		 $row[] = round($aRow['AUDIT_SCORE_PERCANTAGE'],2);
                if($downloadPdfAction){
                    $downloadPdf = '<br><a href="javascript:void(0);" onclick="downloadPdf('.$aRow['id'].')" style="white-space:nowrap;"><i class="fa fa-download"></i> PDF</a>';
                }
                $row[] = $downloadPdf;
		 $output['aaData'][] = $row;
		}
		return $output;
    }
    
    public function fetchFacilitiesAudits($params){
        $audits = array();
        $aResult = "";
        if(isset($params['facilityName']) && trim($params['facilityName'])!= '') {
            $dbAdapter = $this->adapter;
            $sql = new Sql($dbAdapter);
            $query = $sql->select()->from(array('spiv3'=>'spi_form_v_3'))
                                   ->columns(array('id','testingpointname','assesmentofaudit'))
                                   ->where(array('spiv3.facilityname'=>$params['facilityName'],'spiv3.status'=>'approved'));
            $queryStr = $sql->getSqlStringForSqlObject($query);
            $audits = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
            
            $aQuery = $sql->select()->from(array('spiv3_fclt' => 'spi_rt_3_facilities'))
                                    ->columns(array('facility_name','email'))
                                    ->where(array('spiv3_fclt.facility_name'=>$params['facilityName']));
            $aQueryStr = $sql->getSqlStringForSqlObject($aQuery);
            $aResult = $dbAdapter->query($aQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
        }
      return array('audits'=>$audits,'facilityProfile'=>$aResult);
    }
}

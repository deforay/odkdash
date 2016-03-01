<?php

namespace Application\Model;

use Zend\Session\Container;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Zend\Db\TableGateway\AbstractTableGateway;

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
        $d = array('data_dump' => json_encode($params));
        $dbAdapter = $this->adapter;
        $insert->values($d);
        $selectString = $sql->getSqlStringForSqlObject($insert);
        $results = $dbAdapter->query($selectString, $dbAdapter::QUERY_MODE_EXECUTE);
       
       
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
            );
            $dbAdapter = $this->adapter;
            $insert->values($par);
            $selectString = $sql->getSqlStringForSqlObject($insert);
            $results = $dbAdapter->query($selectString, $dbAdapter::QUERY_MODE_EXECUTE);        
            
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
                                                'level0' => new \Zend\Db\Sql\Expression("SUM(IF(AUDIT_SCORE_PERCANTAGE < 40, 1,0))"),
                                                'level1' => new \Zend\Db\Sql\Expression("SUM(IF(AUDIT_SCORE_PERCANTAGE >= 40 and AUDIT_SCORE_PERCANTAGE < 60, 1,0))"),
                                                'level2' => new \Zend\Db\Sql\Expression("SUM(IF(AUDIT_SCORE_PERCANTAGE >= 60 and AUDIT_SCORE_PERCANTAGE < 80, 1,0))"),
                                                'level3' => new \Zend\Db\Sql\Expression("SUM(IF(AUDIT_SCORE_PERCANTAGE >= 80 and AUDIT_SCORE_PERCANTAGE < 90, 1,0))"),
                                                'level4' => new \Zend\Db\Sql\Expression("SUM(IF(AUDIT_SCORE_PERCANTAGE >= 90, 1,0))"),
                                                ));
        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);

        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        
        return $rResult;
     
        
    }
    
    
    public function getPerformanceLast30Days(){
     
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                                ->columns(array(
                                                'level0' => new \Zend\Db\Sql\Expression("SUM(IF(AUDIT_SCORE_PERCANTAGE < 40, 1,0))"),
                                                'level1' => new \Zend\Db\Sql\Expression("SUM(IF(AUDIT_SCORE_PERCANTAGE >= 40 and AUDIT_SCORE_PERCANTAGE < 60, 1,0))"),
                                                'level2' => new \Zend\Db\Sql\Expression("SUM(IF(AUDIT_SCORE_PERCANTAGE >= 60 and AUDIT_SCORE_PERCANTAGE < 80, 1,0))"),
                                                'level3' => new \Zend\Db\Sql\Expression("SUM(IF(AUDIT_SCORE_PERCANTAGE >= 80 and AUDIT_SCORE_PERCANTAGE < 90, 1,0))"),
                                                'level4' => new \Zend\Db\Sql\Expression("SUM(IF(AUDIT_SCORE_PERCANTAGE >= 90, 1,0))"),
                                                ))
                                ->where("`today` BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE()");
        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);

        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        
        return $rResult;
     
        
    }
    
    public function getPerformanceLast180Days(){
     
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                                ->columns(array(
                                                'level0' => new \Zend\Db\Sql\Expression("SUM(IF(AUDIT_SCORE_PERCANTAGE < 40, 1,0))"),
                                                'level1' => new \Zend\Db\Sql\Expression("SUM(IF(AUDIT_SCORE_PERCANTAGE >= 40 and AUDIT_SCORE_PERCANTAGE < 60, 1,0))"),
                                                'level2' => new \Zend\Db\Sql\Expression("SUM(IF(AUDIT_SCORE_PERCANTAGE >= 60 and AUDIT_SCORE_PERCANTAGE < 80, 1,0))"),
                                                'level3' => new \Zend\Db\Sql\Expression("SUM(IF(AUDIT_SCORE_PERCANTAGE >= 80 and AUDIT_SCORE_PERCANTAGE < 90, 1,0))"),
                                                'level4' => new \Zend\Db\Sql\Expression("SUM(IF(AUDIT_SCORE_PERCANTAGE >= 90, 1,0))"),
                                                ))
                                ->where("`today` BETWEEN CURDATE() - INTERVAL 180 DAY AND CURDATE()");
        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);

        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        
        return $rResult;
     
        
    }
    
    public function getAllSubmissions(){
     
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'));
        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);

        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        
        return $rResult;
     
        
    }

    public function getFormData($id) {
        $row = $this->select(array('id' => (int) $id))->current();
        return $row;
    }
    
    

    public function getAuditRoundWiseData() {
        $rResult = $this->getAllSubmissions();
        
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
        
        return $auditRoundWiseData;
     
    }
    
    
    
}

<?php

namespace Application\Model;

use Laminas\Session\Container;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Db\TableGateway\AbstractTableGateway;
//use Application\Model\SpiRt5FacilitiesTable;
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
class SpiFormVer5Table extends AbstractTableGateway {

    protected $table = 'spi_form_v_5';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    public function saveData($params) {
        
        if($params == null || $params == "" || (is_array($params) && count($params) ==0 )){
            exit;
        }
        
        $sql = new Sql($this->adapter);
        $insert = $sql->insert('form_dump');
        $d = array('data_dump' => json_encode($params) , 'received_on' => new \Laminas\Db\Sql\Expression("NOW()"));
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
       
        //error_log(json_encode($params,true));
        
        
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
    
    
        $insert = $sql->insert('spi_form_v_5');
        if(isset($data['testingpointtype']) && trim($data['testingpointtype'])==""){
            $data['testingpointtype']=$data['testingpointtype'];
        }
        
        
        $data['instanceID'] = isset($data['instanceID']) ? $data['instanceID'] :"";
        $data['instanceName'] = isset($data['instanceName']) ? $data['instanceName'] :"";
        
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
                'auditEndTime' => $data['auditEndTime'],
                'auditStartTime' => $data['auditStartTime'],
                'auditroundno' => $data['auditroundno'],
                'facilityname' => $data['facilityname'],
                'facilityid' => $data['facilityid'],                
                'testingpointtype' => $data['testingpointtype'],
                'testingpointtype_other' => $data['testingpointtype_other'],
                'physicaladdress' => $data['physicaladdress'],
                'level' => $data['level'],
                'level_other' => $data['level_other'],
                
                'affiliation' => $data['affiliation'],
                'affiliation_other' => $data['affiliation_other'],
                'NumberofTester' => (isset($data['NumberofTester']) && $data['NumberofTester'] > 0 ? $data['NumberofTester'] : 0),
                'client_tested_HIV' => $data['client_tested_HIV'],
                'client_tested_HIV_PM' => $data['client_tested_HIV_PM'],
                'client_tested_HIV_PQ' => $data['client_tested_HIV_PQ'],
                'client_newly_HIV' => $data['client_newly_HIV'],
                'client_newly_HIV_PM' => $data['client_newly_HIV_PM'],
                'client_newly_HIV_PQ' => $data['client_newly_HIV_PQ'],
                'client_negative_HIV' => $data['client_negative_HIV'],
                'client_negative_HIV_PM' => $data['client_negative_HIV_PM'],
                'client_negative_HIV_PQ' => $data['client_negative_HIV_PQ'],
                'client_positive_HIV_RTRI' => $data['client_positive_HIV_RTRI'],
                'client_positive_HIV_RTRI_PM' => $data['client_positive_HIV_RTRI_PM'],
                'client_positive_HIV_RTRI_PQ' => $data['client_positive_HIV_RTRI_PQ'],
                'client_recent_RTRI' => $data['client_recent_RTRI'],
                'client_recent_RTRI_PM' => $data['client_recent_RTRI_PM'],
                'client_recent_RTRI_PQ' => $data['client_recent_RTRI_PQ'],
                'name_auditor_lead' => $data['name_auditor_lead'],
                'name_auditor2' => $data['nameOfAuditor2'],
                'info4' => $data['info4'],
                'INSTANCE' => $data['INSTANCE'],
                'PERSONAL_Q_1_1_HIV_TRAINING' => $data['PERSONAL_Q_1_1_HIV_TRAINING'],
                'PERSONAL_C_1_1_HIV_TRAINING' => $data['PERSONAL_C_1_1_HIV_TRAINING'],
                'PERSONAL_Q_1_2_HIV_TESTING_REGISTER' => $data['PERSONAL_Q_1_2_HIV_TESTING_REGISTER'],
                'PERSONAL_C_1_2_HIV_TESTING_REGISTER' => $data['PERSONAL_C_1_2_HIV_TESTING_REGISTER'],
                'PERSONAL_Q_1_3_EQA_PT' => $data['PERSONAL_Q_1_3_EQA_PT'],
                'PERSONAL_C_1_3_EQA_PT' => $data['PERSONAL_C_1_3_EQA_PT'],
                'PERSONAL_Q_1_4_QC_PROCESS' => $data['PERSONAL_Q_1_4_QC_PROCESS'],
                'PERSONAL_C_1_4_QC_PROCESS' => $data['PERSONAL_C_1_4_QC_PROCESS'],
                'PERSONAL_Q_1_5_SAFETY_MANAGEMENT' => $data['PERSONAL_Q_1_5_SAFETY_MANAGEMENT'],
                'PERSONAL_C_1_5_SAFETY_MANAGEMENT' => $data['PERSONAL_C_1_5_SAFETY_MANAGEMENT'],
                'PERSONAL_Q_1_6_REFRESHER_TRAINING' => $data['PERSONAL_Q_1_6_REFRESHER_TRAINING'],
                'PERSONAL_C_1_6_REFRESHER_TRAINING' => $data['PERSONAL_C_1_6_REFRESHER_TRAINING'],
                'PERSONAL_Q_1_7_HIV_COMPETENCY_TESTING' => $data['PERSONAL_Q_1_7_HIV_COMPETENCY_TESTING'],
                'PERSONAL_C_1_7_HIV_COMPETENCY_TESTING' => $data['PERSONAL_C_1_7_HIV_COMPETENCY_TESTING'],
                'PERSONAL_Q_1_8_NATIONAL_CERTIFICATION' => $data['PERSONAL_Q_1_8_NATIONAL_CERTIFICATION'],
                'PERSONAL_C_1_8_NATIONAL_CERTIFICATION' => $data['PERSONAL_C_1_8_NATIONAL_CERTIFICATION'],
                'PERSONAL_Q_1_9_CERTIFIED_TESTERS' => $data['PERSONAL_Q_1_9_CERTIFIED_TESTERS'],
                'PERSONAL_C_1_9_CERTIFIED_TESTERS' => $data['PERSONAL_C_1_9_CERTIFIED_TESTERS'],
                'PERSONAL_Q_1_10_RECERTIFIED' => $data['PERSONAL_Q_1_10_RECERTIFIED'],
                'PERSONAL_C_1_10_RECERTIFIED' => $data['PERSONAL_C_1_10_RECERTIFIED'],
                'PERSONAL_SCORE' => $data['PERSONAL_SCORE'],
                'PERSONAL_Display' => $data['PERSONAL_Display'],
                'PERSONALPHOTO' => $data['PERSONALPHOTO'],
                'PHYSICAL_Q_2_1_DESIGNATED_HIV_AREA' => $data['PHYSICAL_Q_2_1_DESIGNATED_HIV_AREA'],
                'PHYSICAL_C_2_1_DESIGNATED_HIV_AREA' => $data['PHYSICAL_C_2_1_DESIGNATED_HIV_AREA'],
                'PHYSICAL_Q_2_2_CLEAN_TESTING_AREA' => $data['PHYSICAL_Q_2_2_CLEAN_TESTING_AREA'],
                'PHYSICAL_C_2_2_CLEAN_TESTING_AREA' => $data['PHYSICAL_C_2_2_CLEAN_TESTING_AREA'],
                'PHYSICAL_Q_2_3_SUFFICIENT_LIGHT_AVAILABILITY' => $data['PHYSICAL_Q_2_3_SUFFICIENT_LIGHT_AVAILABILITY'],
                'PHYSICAL_C_2_3_SUFFICIENT_LIGHT_AVAILABILITY' => $data['PHYSICAL_C_2_3_SUFFICIENT_LIGHT_AVAILABILITY'],
                'PHYSICAL_Q_2_4_TEST_KIT_STORAGE' => $data['PHYSICAL_Q_2_4_TEST_KIT_STORAGE'],
                'PHYSICAL_C_2_4_TEST_KIT_STORAGE' => $data['PHYSICAL_C_2_4_TEST_KIT_STORAGE'],
                'PHYSICAL_Q_2_5_SUFFICIENT_SECURE_STORAGE' => $data['PHYSICAL_Q_2_5_SUFFICIENT_SECURE_STORAGE'],
                'PHYSICAL_C_2_5_SUFFICIENT_SECURE_STORAGE' => $data['PHYSICAL_C_2_5_SUFFICIENT_SECURE_STORAGE'],
                'PHYSICAL_SCORE' => $data['PHYSICAL_SCORE'],
                'PHYSICAL_Display' => $data['PHYSICAL_Display'],
                'PHYSICALPHOTO' => $data['PHYSICALPHOTO'],
                'SAFETY_Q_3_1_IMPLEMENT_SAFETY_PRACTICES' => $data['SAFETY_Q_3_1_IMPLEMENT_SAFETY_PRACTICES'],
                'SAFETY_C_3_1_IMPLEMENT_SAFETY_PRACTICES' => $data['SAFETY_C_3_1_IMPLEMENT_SAFETY_PRACTICES'],
                'SAFETY_Q_3_2_ACCIDENTAL_EXPOSURE' => $data['SAFETY_Q_3_2_ACCIDENTAL_EXPOSURE'],
                'SAFETY_C_3_2_ACCIDENTAL_EXPOSURE' => $data['SAFETY_C_3_2_ACCIDENTAL_EXPOSURE'],
                'SAFETY_Q_3_3_PRACTICE_SAFETY_PRACTICES' => $data['SAFETY_Q_3_3_PRACTICE_SAFETY_PRACTICES'],
                'SAFETY_C_3_3_PRACTICE_SAFETY_PRACTICES' => $data['SAFETY_C_3_3_PRACTICE_SAFETY_PRACTICES'],
                'SAFETY_Q_3_4_PPE_AVAILABILITY' => $data['SAFETY_Q_3_4_PPE_AVAILABILITY'],
                'SAFETY_C_3_4_PPE_AVAILABILITY' => $data['SAFETY_C_3_4_PPE_AVAILABILITY'],
                'SAFETY_Q_3_5_PPE_USED_PROPERLY' => $data['SAFETY_Q_3_5_PPE_USED_PROPERLY'],
                'SAFETY_C_3_5_PPE_USED_PROPERLY' => $data['SAFETY_C_3_5_PPE_USED_PROPERLY'],
                'SAFETY_Q_3_6_WATER_SOAP_AVAILABILITY' => $data['SAFETY_Q_3_6_WATER_SOAP_AVAILABILITY'],
                'SAFETY_C_3_6_WATER_SOAP_AVAILABILITY' => $data['SAFETY_C_3_6_WATER_SOAP_AVAILABILITY'],
                'SAFETY_Q_3_7_DISINFECTANT_AVAILABLE' => $data['SAFETY_Q_3_7_DISINFECTANT_AVAILABLE'],
                'SAFETY_C_3_7_DISINFECTANT_AVAILABLE' => $data['SAFETY_C_3_7_DISINFECTANT_AVAILABLE'],
                'SAFETY_Q_3_8_DISINFECTANT_LABELED_PROPERLY' => $data['SAFETY_Q_3_8_DISINFECTANT_LABELED_PROPERLY'],
                'SAFETY_C_3_8_DISINFECTANT_LABELED_PROPERLY' => $data['SAFETY_C_3_8_DISINFECTANT_LABELED_PROPERLY'],
                'SAFETY_Q_3_9_SEGREGATION_OF_WASTE' => $data['SAFETY_Q_3_9_SEGREGATION_OF_WASTE'],
                'SAFETY_C_3_9_SEGREGATION_OF_WASTE' => $data['SAFETY_C_3_9_SEGREGATION_OF_WASTE'],
                'SAFETY_Q_3_10_INFECTIOUS_WASTE_EMPTIED' => $data['SAFETY_Q_3_10_INFECTIOUS_WASTE_EMPTIED'],
                'SAFETY_C_3_10_INFECTIOUS_WASTE_EMPTIED' => $data['SAFETY_C_3_10_INFECTIOUS_WASTE_EMPTIED'],
                'SAFETY_SCORE' => $data['SAFETY_SCORE'],
                'SAFETY_DISPLAY' => $data['SAFETY_DISPLAY'],
                'SAFETYPHOTO' => $data['SAFETYPHOTO'],
                'PRE_Q_4_1_NATIONAL_GUIDELINES' => $data['PRE_Q_4_1_NATIONAL_GUIDELINES'],
                'PRE_C_4_1_NATIONAL_GUIDELINES' => $data['PRE_C_4_1_NATIONAL_GUIDELINES'],
                'PRE_Q_4_2_HIV_TESTING_ALGORITHM' => $data['PRE_Q_4_2_HIV_TESTING_ALGORITHM'],
                'PRE_C_4_2_HIV_TESTING_ALGORITHM' => $data['PRE_C_4_2_HIV_TESTING_ALGORITHM'],
                'PRE_Q_4_3_TEST_PROCEDURES_ACCESSIBLE' => $data['PRE_Q_4_3_TEST_PROCEDURES_ACCESSIBLE'],
                'PRE_C_4_3_TEST_PROCEDURES_ACCESSIBLE' => $data['PRE_C_4_3_TEST_PROCEDURES_ACCESSIBLE'],
                'PRE_Q_4_4_TEST_PROCEDURES_ACCURATE' => $data['PRE_Q_4_4_TEST_PROCEDURES_ACCURATE'],
                'PRE_C_4_4_TEST_PROCEDURES_ACCURATE' => $data['PRE_C_4_4_TEST_PROCEDURES_ACCURATE'],
                'PRE_Q_4_5_APPROVED_KITS_AVAILABLE' => $data['PRE_Q_4_5_APPROVED_KITS_AVAILABLE'],
                'PRE_C_4_5_APPROVED_KITS_AVAILABLE' => $data['PRE_C_4_5_APPROVED_KITS_AVAILABLE'],
                'PRE_Q_4_6_HIV_KITS_EXPIRATION' => $data['PRE_Q_4_6_HIV_KITS_EXPIRATION'],
                'PRE_C_4_6_HIV_KITS_EXPIRATION' => $data['PRE_C_4_6_HIV_KITS_EXPIRATION'],
                'PRE_Q_4_7_KIT_SUPPLIES_AVAILABILITY' => $data['PRE_Q_4_7_KIT_SUPPLIES_AVAILABILITY'],
                'PRE_C_4_7_KIT_SUPPLIES_AVAILABILITY' => $data['PRE_C_4_7_KIT_SUPPLIES_AVAILABILITY'],
                'PRE_Q_4_8_STOCK_MANAGEMENT' => $data['PRE_Q_4_8_STOCK_MANAGEMENT'],
                'PRE_C_4_8_STOCK_MANAGEMENT' => $data['PRE_C_4_8_STOCK_MANAGEMENT'],
                'PRE_Q_4_9_DOCUMENTED_INVENTORY' => $data['PRE_Q_4_9_DOCUMENTED_INVENTORY'],
                'PRE_C_4_9_DOCUMENTED_INVENTORY' => $data['PRE_C_4_9_DOCUMENTED_INVENTORY'],
                'PRE_Q_4_10_SOPS_BLOOD_COLLECTION' => $data['PRE_Q_4_10_SOPS_BLOOD_COLLECTION'],
                'PRE_C_4_10_SOPS_BLOOD_COLLECTION' => $data['PRE_C_4_10_SOPS_BLOOD_COLLECTION'],
                'PRE_Q_4_11_BLOOD_COLLECTION_SUPPLIES' => $data['PRE_Q_4_11_BLOOD_COLLECTION_SUPPLIES'],
                'PRE_C_4_11_BLOOD_COLLECTION_SUPPLIES' => $data['PRE_C_4_11_BLOOD_COLLECTION_SUPPLIES'],
                'PRE_Q_4_12_CLIENT_IDENTIFICATION' => $data['PRE_Q_4_12_CLIENT_IDENTIFICATION'],
                'PRE_C_4_12_CLIENT_IDENTIFICATION' => $data['PRE_C_4_12_CLIENT_IDENTIFICATION'],
                'PRE_Q_4_13_CLIENT_ID_RECORDED' => $data['PRE_Q_4_13_CLIENT_ID_RECORDED'],
                'PRE_C_4_13_CLIENT_ID_RECORDED' => $data['PRE_C_4_13_CLIENT_ID_RECORDED'],
                'PRETEST_SCORE' => $data['PRETEST_SCORE'],
                'PRETEST_Display' => $data['PRETEST_Display'],
                'PRETESTPHOTO' => $data['PRETESTPHOTO'],
                'TEST_Q_5_1_PROCEDURES_TESTING_ALGORITHM' => $data['TEST_Q_5_1_PROCEDURES_TESTING_ALGORITHM'],
                'TEST_C_5_1_PROCEDURES_TESTING_ALGORITHM' => $data['TEST_C_5_1_PROCEDURES_TESTING_ALGORITHM'],
                'TEST_Q_5_2_TIMERS_AVAILABILITY' => $data['TEST_Q_5_2_TIMERS_AVAILABILITY'],
                'TEST_C_5_2_TIMERS_AVAILABILITY' => $data['TEST_C_5_2_TIMERS_AVAILABILITY'],
                'TEST_Q_5_3_SAMPLE_DEVICE_ACCURACY' => $data['TEST_Q_5_3_SAMPLE_DEVICE_ACCURACY'],
                'TEST_C_5_3_SAMPLE_DEVICE_ACCURACY' => $data['TEST_C_5_3_SAMPLE_DEVICE_ACCURACY'],
                'TEST_Q_5_4_TESTING_PROCEDURE_FOLLOWED' => $data['TEST_Q_5_4_TESTING_PROCEDURE_FOLLOWED'],
                'TEST_C_5_4_TESTING_PROCEDURE_FOLLOWED' => $data['TEST_C_5_4_TESTING_PROCEDURE_FOLLOWED'],
                'TEST_Q_5_5_QUALITY_CONTROL' => $data['TEST_Q_5_5_QUALITY_CONTROL'],
                'TEST_C_5_5_QUALITY_CONTROL' => $data['TEST_C_5_5_QUALITY_CONTROL'],
                'TEST_Q_5_6_QC_RESULTS_RECORDED' => $data['TEST_Q_5_6_QC_RESULTS_RECORDED'],
                'TEST_C_5_6_QC_RESULTS_RECORDED' => $data['TEST_C_5_6_QC_RESULTS_RECORDED'],
                'TEST_Q_5_7_INCORRECT_QC_RESULTS' => $data['TEST_Q_5_7_INCORRECT_QC_RESULTS'],
                'TEST_C_5_7_INCORRECT_QC_RESULTS' => $data['TEST_C_5_7_INCORRECT_QC_RESULTS'],
                'TEST_Q_5_8_APPROPRIATE_STEPS_TAKEN' => $data['TEST_Q_5_8_APPROPRIATE_STEPS_TAKEN'],
                'TEST_C_5_8_APPROPRIATE_STEPS_TAKEN' => $data['TEST_C_5_8_APPROPRIATE_STEPS_TAKEN'],
                'TEST_Q_5_9_REVIEW_QC_RECORDS' => $data['TEST_Q_5_9_REVIEW_QC_RECORDS'],
                'TEST_C_5_9_REVIEW_QC_RECORDS' => $data['TEST_C_5_9_REVIEW_QC_RECORDS'],
                'TEST_SCORE' => $data['TEST_SCORE'],
                'TEST_DISPLAY' => $data['TEST_DISPLAY'],
                'TESTPHOTO' => $data['TESTPHOTO'],
                'POST_Q_6_1_STANDARDIZED_HIV_REGISTER' => $data['POST_Q_6_1_STANDARDIZED_HIV_REGISTER'],
                'POST_C_6_1_STANDARDIZED_HIV_REGISTER' => $data['POST_C_6_1_STANDARDIZED_HIV_REGISTER'],
                'POST_Q_6_2_ELEMENTS_CAPTURED_CORRECTLY' => $data['POST_Q_6_2_ELEMENTS_CAPTURED_CORRECTLY'],
                'POST_C_6_2_ELEMENTS_CAPTURED_CORRECTLY' => $data['POST_C_6_2_ELEMENTS_CAPTURED_CORRECTLY'],
                'POST_Q_6_3_PAGE_TOTAL_SUMMARY' => $data['POST_Q_6_3_PAGE_TOTAL_SUMMARY'],
                'POST_C_6_3_PAGE_TOTAL_SUMMARY' => $data['POST_C_6_3_PAGE_TOTAL_SUMMARY'],
                'POST_Q_6_4_INVALID_TEST_RESULT_RECORDED' => $data['POST_Q_6_4_INVALID_TEST_RESULT_RECORDED'],
                'POST_C_6_4_INVALID_TEST_RESULT_RECORDED' => $data['POST_C_6_4_INVALID_TEST_RESULT_RECORDED'],
                'POST_Q_6_5_APPROPRIATE_STEPS_TAKEN' => $data['POST_Q_6_5_APPROPRIATE_STEPS_TAKEN'],
                'POST_C_6_5_APPROPRIATE_STEPS_TAKEN' => $data['POST_C_6_5_APPROPRIATE_STEPS_TAKEN'],
                'POST_Q_6_6_REGISTERS_REVIEWED' => $data['POST_Q_6_6_REGISTERS_REVIEWED'],
                'POST_C_6_6_REGISTERS_REVIEWED' => $data['POST_C_6_6_REGISTERS_REVIEWED'],
                'POST_Q_6_7_DOCUMENTS_SECURELY_KEPT' => $data['POST_Q_6_7_DOCUMENTS_SECURELY_KEPT'],
                'POST_C_6_7_DOCUMENTS_SECURELY_KEPT' => $data['POST_C_6_7_DOCUMENTS_SECURELY_KEPT'],
                'POST_Q_6_8_REGISTER_SECURE_LOCATION' => $data['POST_Q_6_8_REGISTER_SECURE_LOCATION'],
                'POST_C_6_8_REGISTER_SECURE_LOCATION' => $data['POST_C_6_8_REGISTER_SECURE_LOCATION'],
                'POST_Q_6_9_REGISTERS_PROPERLY_LABELED' => $data['POST_Q_6_9_REGISTERS_PROPERLY_LABELED'],
                'POST_C_6_9_REGISTERS_PROPERLY_LABELED' => $data['POST_C_6_9_REGISTERS_PROPERLY_LABELED'],
                'POST_SCORE' => $data['POST_SCORE'],
                'POST_DISPLAY' => $data['POST_DISPLAY'],
                'POSTTESTPHOTO' => $data['POSTTESTPHOTO'],
                'EQA_Q_7_1_PT_ENROLLMENT' => $data['EQA_Q_7_1_PT_ENROLLMENT'],
                'EQA_C_7_1_PT_ENROLLMENT' => $data['EQA_C_7_1_PT_ENROLLMENT'],
                'EQA_Q_7_2_TESTING_EQAPT_SAMPLES' => $data['EQA_Q_7_2_TESTING_EQAPT_SAMPLES'],
                'EQA_C_7_2_TESTING_EQAPT_SAMPLES' => $data['EQA_C_7_2_TESTING_EQAPT_SAMPLES'],
                'EQA_Q_7_3_REVIEW_BEFORE_SUBMISSION' => $data['EQA_Q_7_3_REVIEW_BEFORE_SUBMISSION'],
                'EQA_C_7_3_REVIEW_BEFORE_SUBMISSION' => $data['EQA_C_7_3_REVIEW_BEFORE_SUBMISSION'],
                'EQA_Q_7_4_FEEDBACK_RECEIVED_REVIEWED' => $data['EQA_Q_7_4_FEEDBACK_RECEIVED_REVIEWED'],
                'EQA_C_7_4_FEEDBACK_RECEIVED_REVIEWED' => $data['EQA_C_7_4_FEEDBACK_RECEIVED_REVIEWED'],
                'EQA_Q_7_5_IMPLEMENT_CORRECTIVE_ACTION' => $data['EQA_Q_7_5_IMPLEMENT_CORRECTIVE_ACTION'],
                'EQA_C_7_5_IMPLEMENT_CORRECTIVE_ACTION' => $data['EQA_C_7_5_IMPLEMENT_CORRECTIVE_ACTION'],
                'EQA_Q_7_6_RECEIVE_PERIODIC_VISITS' => $data['EQA_Q_7_6_RECEIVE_PERIODIC_VISITS'],
                'EQA_C_7_6_RECEIVE_PERIODIC_VISITS' => $data['EQA_C_7_6_RECEIVE_PERIODIC_VISITS'],
                'EQA_Q_7_7_FEEDBACK_PROVIDED_DOCUMENTED' => $data['EQA_Q_7_7_FEEDBACK_PROVIDED_DOCUMENTED'],
                'EQA_C_7_7_FEEDBACK_PROVIDED_DOCUMENTED' => $data['EQA_C_7_7_FEEDBACK_PROVIDED_DOCUMENTED'],
                'EQA_Q_7_8_TESTERS_RETRAINED_IN_VISITS' => $data['EQA_Q_7_8_TESTERS_RETRAINED_IN_VISITS'],
                'EQA_C_7_8_TESTERS_RETRAINED_IN_VISITS' => $data['EQA_C_7_8_TESTERS_RETRAINED_IN_VISITS'],
                'performrtritesting' => $data['performrtritesting'],                
                'EQA_SCORE' => $data['EQA_SCORE'],
                'EQA_DISPLAY' => $data['EQA_DISPLAY'],
                'EQAPHOTO' => $data['EQAPHOTO'],
                'RTRI_Q_8_1_TESTERS_RECEIVED_RTRI_TRAINING' => $data['RTRI_Q_8_1_TESTERS_RECEIVED_RTRI_TRAINING'],
                'RTRI_C_8_1_TESTERS_RECEIVED_RTRI_TRAINING' => $data['RTRI_C_8_1_TESTERS_RECEIVED_RTRI_TRAINING'],
                'RTRI_Q_8_2_TESTERS_DEMONSTRATED_COMPETENCY' => $data['RTRI_Q_8_2_TESTERS_DEMONSTRATED_COMPETENCY'],
                'RTRI_C_8_2_TESTERS_DEMONSTRATED_COMPETENCY' => $data['RTRI_C_8_2_TESTERS_DEMONSTRATED_COMPETENCY'],
                'RTRI_Q_8_3_JOBAIDS_READILY_AVAILABLE' => $data['RTRI_Q_8_3_JOBAIDS_READILY_AVAILABLE'],
                'RTRI_C_8_3_JOBAIDS_READILY_AVAILABLE' => $data['RTRI_C_8_3_JOBAIDS_READILY_AVAILABLE'],
                'RTRI_Q_8_4_SUFFICIENT_SUPPLY_AVAILABLE' => $data['RTRI_Q_8_4_SUFFICIENT_SUPPLY_AVAILABLE'],
                'RTRI_C_8_4_SUFFICIENT_SUPPLY_AVAILABLE' => $data['RTRI_C_8_4_SUFFICIENT_SUPPLY_AVAILABLE'],
                'RTRI_Q_8_5_RTRI_KIT_STORAGE' => $data['RTRI_Q_8_5_RTRI_KIT_STORAGE'],
                'RTRI_C_8_5_RTRI_KIT_STORAGE' => $data['RTRI_C_8_5_RTRI_KIT_STORAGE'],
                'RTRI_Q_8_6_RTRI_TESTING_PROCEDURE_FOLLOWED' => $data['RTRI_Q_8_6_RTRI_TESTING_PROCEDURE_FOLLOWED'],
                'RTRI_C_8_6_RTRI_TESTING_PROCEDURE_FOLLOWED' => $data['RTRI_C_8_6_RTRI_TESTING_PROCEDURE_FOLLOWED'],
                'RTRI_Q_8_7_RTRI_TESTING_RESULTS_DOCUMENTED' => $data['RTRI_Q_8_7_RTRI_TESTING_RESULTS_DOCUMENTED'],
                'RTRI_C_8_7_RTRI_TESTING_RESULTS_DOCUMENTED' => $data['RTRI_C_8_7_RTRI_TESTING_RESULTS_DOCUMENTED'],
                'RTRI_Q_8_8_QC_ROUTINELY_USED' => $data['RTRI_Q_8_8_QC_ROUTINELY_USED'],
                'RTRI_C_8_8_QC_ROUTINELY_USED' => $data['RTRI_C_8_8_QC_ROUTINELY_USED'],
                'RTRI_Q_8_9_QC_RESULTS_RECORDED' => $data['RTRI_Q_8_9_QC_RESULTS_RECORDED'],
                'RTRI_C_8_9_QC_RESULTS_RECORDED' => $data['RTRI_C_8_9_QC_RESULTS_RECORDED'],
                'RTRI_Q_8_10_INCORRECT_QC_DOCUMENTED' => $data['RTRI_Q_8_10_INCORRECT_QC_DOCUMENTED'],
                'RTRI_C_8_10_INCORRECT_QC_DOCUMENTED' => $data['RTRI_C_8_10_INCORRECT_QC_DOCUMENTED'],
                'RTRI_Q_8_11_INVALID_RTRI_RESULTS' => $data['RTRI_Q_8_11_INVALID_RTRI_RESULTS'],
                'RTRI_C_8_11_INVALID_RTRI_RESULTS' => $data['RTRI_C_8_11_INVALID_RTRI_RESULTS'],
                'RTRI_SCORE' => $data['RTRI_SCORE'],
                'RTRI_DISPLAY' => $data['RTRI_DISPLAY'],
                'RTRIPHOTO' => $data['RTRIPHOTO'],
                'AuditRequiredScore' => $data['AuditRequiredScore'],                
                'FINAL_AUDIT_SCORE' => $data['FINAL_AUDIT_SCORE'],
                'MAX_AUDIT_SCORE' => $data['MAX_AUDIT_SCORE'],
                'AUDIT_SCORE_PERCENTAGE' => $data['AUDIT_SCORE_PERCENTAGE'],
                'AUDIT_SCORE_PERCANTAGE_ROUNDED' => $data['AUDIT_SCORE_PERCANTAGE_ROUNDED'],
                'staffaudited' => $data['staffaudited'],
                'durationaudit' => $data['durationaudit'],
                'personincharge' => $data['personincharge'],
                'endofsurvey' => $data['endofsurvey'],
                'sitecode' => $data['sitecode'],
                
                'info5' => $data['info5'],
                'info6' => $data['info6'],
                'info10' => $data['info10'],
                'info11' => $data['info11'],
                'SUMMARY_NOT_AVL' => $data['SUMMARY_NOT_AVL'],
                'info12' => $data['info12'],
                'info177' => $data['info177'],
                'info178' => $data['info178'],
                'info179' => $data['info179'],
                'info180' => $data['info180'],
                'info181' => $data['info181'],
                'info182' => $data['info182'],
                'info183' => $data['info183'],
                'info17a' => $data['info17a'],
                'info21' => $data['info21'],
                'info22' => $data['info22'],
                'info23' => $data['info23'],
                'info24' => $data['info24'],
                'info25' => $data['info25'],
                'info26' => $data['info26'],
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
            //error_log($selectString);
            $results = $dbAdapter->query($selectString, $dbAdapter::QUERY_MODE_EXECUTE);        
            
            if($approveStatus=='approved'){
                //$facilityDb = new SpiRt5FacilitiesTable($dbAdapter);
                $facilityDb = new SpiRtFacilitiesTable($dbAdapter);
                $facilityResult = $facilityDb->addFacilityBasedOnForm($results->getGeneratedValue(),5);
            }
            
            }catch(Exception $e){
                error_log($e->getMessage());
                error_log( $e->getTraceAsString());
            }
             
        }
    }
    
    
    public function getPerformanceV5($params){
        //echo "ss";die;
        $logincontainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from(array('spiv5' => 'spi_form_v_5'))
                                ->columns(array(
                                                'oldestDate' => new \Laminas\Db\Sql\Expression("MIN(`assesmentofaudit`)"),
                                                'newestDate' => new \Laminas\Db\Sql\Expression("MAX(`assesmentofaudit`)"),
                                                'totalDataPoints' => new \Laminas\Db\Sql\Expression("COUNT(*)"),
                                                'level0' => new \Laminas\Db\Sql\Expression("SUM(IF((AUDIT_SCORE_PERCENTAGE) < 40, 1,0))"),
                                                'level1' => new \Laminas\Db\Sql\Expression("SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 40 and (AUDIT_SCORE_PERCENTAGE) < 60, 1,0))"),
                                                'level2' => new \Laminas\Db\Sql\Expression("SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 60 and (AUDIT_SCORE_PERCENTAGE) < 80, 1,0))"),
                                                'level3' => new \Laminas\Db\Sql\Expression("SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 80 and (AUDIT_SCORE_PERCENTAGE) < 90, 1,0))"),
                                                'level4' => new \Laminas\Db\Sql\Expression("SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 90, 1,0))"),
                                                ))
                                ->where(array('spiv5.status'=>'approved'));
        if(isset($params['fieldName']) && trim($params['fieldName'])!= ''){
            $sQuery = $sQuery->where(array($params['fieldName']=>$params['val']));
        }
        
        if(isset($params['roundno']) && $params['roundno']!=''){
            $sQuery = $sQuery->where('spiv5.auditroundno IN ("' . implode('", "', $params['roundno']) . '")');
        }
      // die("ss");
        $start_date = '';
        $end_date = '';
        if (isset($params['dateRange']) && ($params['dateRange'] != "")) {
            $dateField = explode(" ", $params['dateRange']);
            if (isset($dateField[0]) && trim($dateField[0]) != "") {
                $start_date = $this->dateFormat($dateField[0]);                
            }
            if (isset($dateField[2]) && trim($dateField[2]) != "") {
                $end_date = $this->dateFormat($dateField[2]);
            }
        }
        if (trim($start_date) != "" && trim($end_date) != "") {
            $sQuery = $sQuery->where(array("spiv5.assesmentofaudit >='" . $start_date ."'", "spiv5.assesmentofaudit <='" . $end_date."'"));
        }
        
        if(isset($params['testPoint']) && trim($params['testPoint'])!=''){
            
            if(trim($params['testPoint'])!= 'other'){
                $sQuery = $sQuery->where("spiv5.testingpointtype='".$params['testPoint']."'");
             }else{
                $sQuery = $sQuery->where("spiv5.testingpointtype_other='".$params['testPointName']."'");
             }
            } if(isset($params['level']) && $params['level']!=''){
               $sQuery = $sQuery->where("spiv5.level='".$params['level']."'");
            }
            if(isset($params['affiliation']) && $params['affiliation']!=''){
               $sQuery = $sQuery->where("spiv5.affiliation='".$params['affiliation']."'");
            }
            
            if(isset($params['scoreLevel']) && $params['scoreLevel']!=''){
                if($params['scoreLevel'] == 0){
                  $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE < 40");
                }else if($params['scoreLevel'] == 1){
                  $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv5.AUDIT_SCORE_PERCENTAGE <= 59");
                }else if($params['scoreLevel'] == 2){
                  $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv5.AUDIT_SCORE_PERCENTAGE <= 79");
                }else if($params['scoreLevel'] == 3){
                  $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv5.AUDIT_SCORE_PERCENTAGE <= 89");
                }else if($params['scoreLevel'] == 4){
                  $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 90");
                }
            }
        
        if(isset($logincontainer->token) && count($logincontainer->token) > 0){
            $sQuery = $sQuery->where('spiv5.token IN ("' . implode('", "', $logincontainer->token) . '")');
        }
        
        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        return $rResult;
    }
    
    
    public function getPerformanceLast30DaysV5($params){
        $logincontainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime('-30 days', strtotime($start_date)));
        
        if (isset($params['dateRange']) && ($params['dateRange'] != "")) {
            $dateField = explode(" ", $params['dateRange']);
            if (isset($dateField[2]) && trim($dateField[2]) != "") {
                $start_date = $this->dateFormat($dateField[2]);                
            }
            if (isset($dateField[0]) && trim($dateField[0]) != "") {
                $end_date = $this->dateFormat($dateField[0]);
            }
        }
        
        $sQuery = $sql->select()->from(array('spiv5' => 'spi_form_v_5'))
                                ->columns(array(
                                                'newestDate' => new \Laminas\Db\Sql\Expression("'$start_date'"),
                                                'oldestDate' => new \Laminas\Db\Sql\Expression("'$end_date'"),
                                                'totalDataPoints' => new \Laminas\Db\Sql\Expression("COUNT(*)"),                                    
                                                'level0' => new \Laminas\Db\Sql\Expression("SUM(IF((AUDIT_SCORE_PERCENTAGE) < 40, 1,0))"),
                                                'level1' => new \Laminas\Db\Sql\Expression("SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 40 and (AUDIT_SCORE_PERCENTAGE) < 60, 1,0))"),
                                                'level2' => new \Laminas\Db\Sql\Expression("SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 60 and (AUDIT_SCORE_PERCENTAGE) < 80, 1,0))"),
                                                'level3' => new \Laminas\Db\Sql\Expression("SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 80 and (AUDIT_SCORE_PERCENTAGE) < 90, 1,0))"),
                                                'level4' => new \Laminas\Db\Sql\Expression("SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 90, 1,0))"),
                                                ))
                                ->where("spiv5.status='approved'")
                                ->where("(`assesmentofaudit` BETWEEN '" . $start_date ."' - INTERVAL DATEDIFF('".$start_date."','".$end_date."') DAY AND '".$start_date."')");
        //    $sQuery = $sQuery->where("(`assesmentofaudit` BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE())");
        if(isset($logincontainer->token) && count($logincontainer->token) > 0){
            $sQuery = $sQuery->where('spiv5.token IN ("' . implode('", "', $logincontainer->token) . '")');
        }
        if(isset($params['auditRndNo']) && $params['auditRndNo']!=''){
            $sQuery = $sQuery->where("spiv5.auditroundno='".$params['auditRndNo']."'");
        }
        if(isset($params['testPoint']) && trim($params['testPoint'])!=''){
            $sQuery = $sQuery->where("spiv5.testingpointtype='".$params['testPoint']."'");
            if(trim($params['testPoint'])!= 'other'){
            $sQuery = $sQuery->where("spiv5.testingpointname='".$params['testPointName']."'");
            }else{
            $sQuery = $sQuery->where("spiv5.testingpointtype_other='".$params['testPointName']."'");
            }
            } if(isset($params['level']) && $params['level']!=''){
               $sQuery = $sQuery->where("spiv5.level='".$params['level']."'");
            }
            if(isset($params['affiliation']) && $params['affiliation']!=''){
               $sQuery = $sQuery->where("spiv5.affiliation='".$params['affiliation']."'");
            }
            // if(isset($params['province']) && is_array($params['province']) && count($params['province'])>0 ){
            // $sQuery = $sQuery->join(array('f'=>'spi_rt_3_facilities'),'f.id=spiv5.facility',array('province','district'))
            //                 ->where('f.province IN ("' . implode('", "', $params['province']) . '")');
            // if(is_array($params['district']) && count($params['district'])>0 ){
            //     $sQuery = $sQuery->where('f.province IN ("' . implode('", "', $params['province']) . '")');
            // }
            // }else{
            //     if(isset($params['province']) && $params['province']!=''){
            //         $provinces = explode(",",$params['province']);
            //         $sQuery = $sQuery->join(array('f'=>'spi_rt_3_facilities'),'f.id=spiv5.facility',array('province','district'))
            //                         ->where('f.province IN ("' . implode('", "', $provinces) . '")');
            //     }
            // }
            // if(isset($params['province']) && $params['province']!=''){
            //     if(is_array($params['district']) && count($params['district'])>0 ){
            //         $sQuery = $sQuery->where('f.district IN ("' . implode('", "', $params['district']) . '")');
            //     }else{
            //         if($params['district']!=''){
            //             $provinces = explode(",",$params['district']);
            //             $sQuery = $sQuery->where('f.district IN ("' . implode('", "', $provinces) . '")');
            //         }
            //     }
            // }
            if(isset($params['scoreLevel']) && $params['scoreLevel']!=''){
            if($params['scoreLevel'] == 0){
              $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE < 40");
            }else if($params['scoreLevel'] == 1){
              $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv5.AUDIT_SCORE_PERCENTAGE <= 59");
            }else if($params['scoreLevel'] == 2){
              $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv5.AUDIT_SCORE_PERCENTAGE <= 79");
            }else if($params['scoreLevel'] == 3){
              $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv5.AUDIT_SCORE_PERCENTAGE <= 89");
            }else if($params['scoreLevel'] == 4){
              $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 90");
            }
        }
        
        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
        // die($sQueryStr);
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        
        return $rResult;
    }
    
    public function getPerformanceLast180DaysV5(){
        $logincontainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $today = date('Y-m-d');
        $last180Date = date('Y-m-d', strtotime('-180 days', strtotime($today)));        
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_5'))
                                ->columns(array(
                                                'newestDate' => new \Laminas\Db\Sql\Expression("'$today'"),
                                                'oldestDate' => new \Laminas\Db\Sql\Expression("'$last180Date'"),
                                                'totalDataPoints' => new \Laminas\Db\Sql\Expression("COUNT(*)"),
                                                'level0' => new \Laminas\Db\Sql\Expression("SUM(IF((AUDIT_SCORE_PERCENTAGE) < 40, 1,0))"),
                                                'level1' => new \Laminas\Db\Sql\Expression("SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 40 and (AUDIT_SCORE_PERCENTAGE) < 60, 1,0))"),
                                                'level2' => new \Laminas\Db\Sql\Expression("SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 60 and (AUDIT_SCORE_PERCENTAGE) < 80, 1,0))"),
                                                'level3' => new \Laminas\Db\Sql\Expression("SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 80 and (AUDIT_SCORE_PERCENTAGE) < 90, 1,0))"),
                                                'level4' => new \Laminas\Db\Sql\Expression("SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 90, 1,0))"),
                                                ))
                                ->where(array('status'=>'approved'))
                                ->where("(`assesmentofaudit` BETWEEN CURDATE() - INTERVAL 180 DAY AND CURDATE())");
        if(isset($logincontainer->token) && count($logincontainer->token) > 0){
            $sQuery = $sQuery->where('spiv3.token IN ("' . implode('", "', $logincontainer->token) . '")');
        }
        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
        // echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        
        return $rResult;
    }
    
    public function getAllSubmissionsV5($sortOrder = 'DESC'){
        $logincontainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_5'))
                                ->where('spiv3.status != "deleted"')
                                ->order(array("status DESC","id $sortOrder"));
        if(isset($logincontainer->token) && count($logincontainer->token) > 0){
            $sQuery = $sQuery->where('spiv3.token IN ("' . implode('", "', $logincontainer->token) . '")');
        }
        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
        // echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        return $rResult;
    }
    
    public function getAllApprovedTestingVolumeV5($params){
        $logincontainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_5'));
                                // ->order(array("avgMonthTesting DESC"));
        if(isset($logincontainer->token) && count($logincontainer->token) > 0){
            $sQuery = $sQuery->where('spiv3.token IN ("' . implode('", "', $logincontainer->token) . '")');
        }
        $start_date = '';
        $end_date = '';
        if (isset($params['dateRange']) && ($params['dateRange'] != "")) {
            $dateField = explode(" ", $params['dateRange']);
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
        
        if(isset($params['auditRndNo']) && $params['auditRndNo']!=''){
            $sQuery = $sQuery->where("spiv3.auditroundno='".$params['auditRndNo']."'");
        }
        if(isset($params['testPoint']) && trim($params['testPoint'])!=''){
            $sQuery = $sQuery->where("spiv3.testingpointtype='".$params['testPoint']."'");
            // if(isset($params['testPointName']) && trim($params['testPointName'])!= ''){
                 if(trim($params['testPoint'])!= 'other'){
                    $sQuery = $sQuery->where("spiv3.testingpointname='".$params['testPointName']."'");
                 }else{
                    $sQuery = $sQuery->where("spiv3.testingpointtype_other='".$params['testPointName']."'");
                 }
            // }
            } if(isset($params['level']) && $params['level']!=''){
               $sQuery = $sQuery->where("spiv3.level='".$params['level']."'");
            }
            if(isset($params['affiliation']) && $params['affiliation']!=''){
               $sQuery = $sQuery->where("spiv3.affiliation='".$params['affiliation']."'");
            }
            // if(isset($params['province']) && is_array($params['province']) && count($params['province'])>0 ){
            // $sQuery = $sQuery->join(array('f'=>'spi_rt_3_facilities'),'f.id=spiv3.facility',array('province','district'))
            //                 ->where('f.province IN ("' . implode('", "', $params['province']) . '")');
            // if(is_array($params['district']) && count($params['district'])>0 ){
            //     $sQuery = $sQuery->where('f.province IN ("' . implode('", "', $params['province']) . '")');
            // }
            // }else{
            //     if(isset($params['province']) && $params['province']!=''){
            //         $provinces = explode(",",$params['province']);
            //         $sQuery = $sQuery->join(array('f'=>'spi_rt_3_facilities'),'f.id=spiv3.facility',array('province','district'))
            //                         ->where('f.province IN ("' . implode('", "', $provinces) . '")');
            //     }
            // }
            // if(isset($params['province']) && $params['province']!=''){
            //     if(is_array($params['district']) && count($params['district'])>0 ){
            //         $sQuery = $sQuery->where('f.district IN ("' . implode('", "', $params['district']) . '")');
            //     }else{
            //         if($params['district']!=''){
            //             $provinces = explode(",",$params['district']);
            //             $sQuery = $sQuery->where('f.district IN ("' . implode('", "', $provinces) . '")');
            //         }
            //     }
            // }
            if(isset($params['scoreLevel']) && $params['scoreLevel']!=''){
            if($params['scoreLevel'] == 0){
              $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE < 40");
            }else if($params['scoreLevel'] == 1){
              $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 59");
            }else if($params['scoreLevel'] == 2){
              $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 79");
            }else if($params['scoreLevel'] == 3){
              $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 89");
            }else if($params['scoreLevel'] == 4){
              $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 90");
            }
        }
        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
        // echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        return $rResult;
    }
    
    public function fetchAllSubmissionsDetails($parameters,$acl){
        $logincontainer = new Container('credo');
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
	    $queryContainer = new Container('query');
        $aColumns = array('spiv5.id','facilityname','auditroundno',"DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')",'testingpointtype','level','affiliation','AUDIT_SCORE_PERCENTAGE','spiv5.status');
        $orderColumns = array('spiv5.id','facilityname','auditroundno','assesmentofaudit','testingpointtype','level','affiliation','AUDIT_SCORE_PERCENTAGE','spiv3.status');

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
        if (isset($parameters['dateRange']) && ($parameters['dateRange'] != "")) {
            $dateField = explode(" ", $parameters['dateRange']);
            //print_r($proceed_date);die;
            if (isset($dateField[0]) && trim($dateField[0]) != "") {
                $start_date = $this->dateFormat($dateField[0]);                
            }
            if (isset($dateField[2]) && trim($dateField[2]) != "") {
                $end_date = $this->dateFormat($dateField[2]);
            }
        }
        
        
        $sQuery = $sql->select()->from(array('spiv5' => 'spi_form_v_5'))
                                ->where('spiv5.status != "deleted"');
                                // ->join(array('f'=>'spi_rt_3_facilities'),
                                //                 'f.id=spiv3.facility',
                                //                         array('province','district'),'left');

        if($parameters['auditRndNo']!=''){
         $sQuery = $sQuery->where("spiv5.auditroundno='".$parameters['auditRndNo']."'");
        }
        if (trim($start_date) != "" && trim($end_date) != "") {
            $sQuery = $sQuery->where(array("spiv5.assesmentofaudit >='" . $start_date ."'", "spiv5.assesmentofaudit <='" . $end_date."'"));
        }
        // if(isset($parameters['testPoint']) && trim($parameters['testPoint'])!=''){
        //     $sQuery = $sQuery->where("spiv5.testingpointtype='".$parameters['testPoint']."'");
        //     if(isset($parameters['testPointName']) && trim($parameters['testPointName'])!= ''){
        //          if(trim($parameters['testPoint'])!= 'other'){
        //             $sQuery = $sQuery->where("spiv5.testingpointname='".$parameters['testPointName']."'");
        //          }else{
        //             $sQuery = $sQuery->where("spiv5.testingpointtype_other='".$parameters['testPointName']."'");
        //          }
        //     }
        // }
        if($parameters['level']!=''){
         $sQuery = $sQuery->where("spiv5.level='".$parameters['level']."'");
        }
        if($parameters['affiliation']!=''){
         $sQuery = $sQuery->where("spiv5.affiliation='".$parameters['affiliation']."'");
        }
        
        if(isset($parameters['scoreLevel']) && $parameters['scoreLevel']!=''){
            if($parameters['scoreLevel'] == 0){
              $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE < 40");
            }else if($parameters['scoreLevel'] == 1){
              $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv5.AUDIT_SCORE_PERCENTAGE <= 59");
            }else if($parameters['scoreLevel'] == 2){
              $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv5.AUDIT_SCORE_PERCENTAGE <= 79");
            }else if($parameters['scoreLevel'] == 3){
              $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv5.AUDIT_SCORE_PERCENTAGE <= 89");
            }else if($parameters['scoreLevel'] == 4){
              $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 90");
            }
        }
        if(isset($logincontainer->token) && count($logincontainer->token) > 0){
            $sQuery = $sQuery->where('spiv5.token IN ("' . implode('", "', $logincontainer->token) . '")');
        }
        if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }

        if (isset($sOrder) && $sOrder != "") {
            $sQuery->order($sOrder);
        }
        $queryContainer->exportAllDataQuery = $sQuery;
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
        $tQuery =  $sql->select()->from(array('spiv5' => 'spi_form_v_5'))
                                 ->where('spiv5.status != "deleted"');
                                //  ->join(array('f'=>'spi_rt_3_facilities'),
                                //                 'f.id=spiv3.facility',
                                //                 array('province','district'),'left');
        if($parameters['auditRndNo']!=''){
          $tQuery = $tQuery->where("spiv5.auditroundno='".$parameters['auditRndNo']."'");
        }
        if (trim($start_date) != "" && trim($end_date) != "") {
            $tQuery = $tQuery->where(array("spiv5.assesmentofaudit >='" . $start_date ."'", "spiv5.assesmentofaudit <='" . $end_date."'"));
        }
        if(isset($parameters['testPoint']) && trim($parameters['testPoint'])!='' && strtolower(trim($parameters['testPoint']))== 'other'){
            $tQuery = $tQuery->where("spiv5.testingpointtype='".$parameters['testPoint']."'");
        }

        if(strtolower(trim($parameters['testPoint']))== 'other'){                    
            $tQuery = $tQuery->where("spiv5.testingpointtype_other='".$parameters['testPointName']."'");
         }
        
        if($parameters['level']!=''){
           $tQuery = $tQuery->where("spiv5.level='".$parameters['level']."'");
        }
        if($parameters['affiliation']!=''){
           $tQuery = $tQuery->where("spiv5.affiliation='".$parameters['affiliation']."'");
        }
        
        if(isset($parameters['scoreLevel']) && $parameters['scoreLevel']!=''){
            if($parameters['scoreLevel'] == 0){
              $tQuery = $tQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE < 40");
            }else if($parameters['scoreLevel'] == 1){
              $tQuery = $tQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv5.AUDIT_SCORE_PERCENTAGE <= 59");
            }else if($parameters['scoreLevel'] == 2){
              $tQuery = $tQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv5.AUDIT_SCORE_PERCENTAGE <= 79");
            }else if($parameters['scoreLevel'] == 3){
              $tQuery = $tQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv5.AUDIT_SCORE_PERCENTAGE <= 89");
            }else if($parameters['scoreLevel'] == 4){
              $tQuery = $tQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 90");
            }
        }
        if(isset($logincontainer->token) && count($logincontainer->token) > 0){
            $tQuery = $tQuery->where('spiv5.token IN ("' . implode('", "', $logincontainer->token) . '")');
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
        
        $role = $logincontainer->roleCode;
        if ($acl->isAllowed($role, 'Application\Controller\SpiV5', 'download-pdf')) {
            $downloadPdfAction = true;
        } else {
            $downloadPdfAction = false;
        }
        
        if ($acl->isAllowed($role, 'Application\Controller\SpiV5', 'approve-status')) {
            $approveStatusAction = true;
        } else {
            $approveStatusAction = false;
        }
        
        $commonService = new \Application\Service\CommonService();
        $auditScore = 0;
        $levelZero = array();
        $levelOne = array();
        $levelTwo = array();
        $levelThree = array();
        $levelFour = array();
        foreach ($rResult as $aRow) {
         $row = array();
         $approve = '';
         $downloadPdf="";
         $auditScore+=$aRow['AUDIT_SCORE_PERCENTAGE'];
         if(isset($aRow['AUDIT_SCORE_PERCENTAGE']) && $aRow['AUDIT_SCORE_PERCENTAGE'] < 40){
            $levelZero[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
         }else if(isset($aRow['AUDIT_SCORE_PERCENTAGE']) && $aRow['AUDIT_SCORE_PERCENTAGE'] >= 40 && $aRow['AUDIT_SCORE_PERCENTAGE'] <60){
            $levelOne[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
         }else if(isset($aRow['AUDIT_SCORE_PERCENTAGE']) && $aRow['AUDIT_SCORE_PERCENTAGE'] >= 60 && $aRow['AUDIT_SCORE_PERCENTAGE'] <80){
           $levelTwo[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
         }else if(isset($aRow['AUDIT_SCORE_PERCENTAGE']) && $aRow['AUDIT_SCORE_PERCENTAGE'] >= 80 && $aRow['AUDIT_SCORE_PERCENTAGE'] <90){
           $levelThree[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
         }else if(isset($aRow['AUDIT_SCORE_PERCENTAGE']) && $aRow['AUDIT_SCORE_PERCENTAGE'] >= 90){
           $levelFour[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
         }
         $row['DT_RowId'] = $aRow['id'];
         if(isset($aRow['level_other']) && $aRow['level_other'] != ""){
            $level = " - " .$aRow['level_other'];
         }else{
            $level = '';
         }
         $row[] = '';
         $row[] = $aRow['facilityname'];
         
         $row[] = $aRow['auditroundno'];
         $row[] = $commonService->humanDateFormat($aRow['assesmentofaudit']);
         //$row[] = (isset($aRow['testingpointname']) && $aRow['testingpointname'] != "" ? $aRow['testingpointname'] : $aRow['testingpointtype']);
         $row[] = $aRow['testingpointtype'];
         $row[] = $aRow['level'].$level;
         $row[] = $aRow['affiliation'];
         $row[] = round($aRow['AUDIT_SCORE_PERCENTAGE'],2);
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
         $row[] = $aRow['PERSONAL_SCORE'];
         $row[] = $aRow['PHYSICAL_SCORE'];
         $row[] = $aRow['SAFETY_SCORE'];
         $row[] = $aRow['PRETEST_SCORE'];
         $row[] = $aRow['TEST_SCORE'];
         $row[] = $aRow['POST_SCORE'];
         $row[] = $aRow['EQA_SCORE'];
         $row[] = $aRow['RTRI_SCORE'];

         $t = $aRow['PERSONAL_SCORE'] + $aRow['PHYSICAL_SCORE'] +$aRow['SAFETY_SCORE'] + $aRow['PRETEST_SCORE'] + $aRow['TEST_SCORE'] + $aRow['POST_SCORE'] + $aRow['EQA_SCORE'];
         if(trim($aRow['performrtritesting'])=='Yes'){
            $t += (float)$aRow['RTRI_SCORE'];
         }
         $row[] = $t."(".round($aRow['AUDIT_SCORE_PERCENTAGE'],2).")";
         $output['aaData'][] = $row;
        }
        //get earliest date
        $eQuery =  $sql->select()->from(array('spiv5' => 'spi_form_v_5'))->columns(array('assesmentofaudit'))->order('assesmentofaudit ASC');
        $eQueryStr = $sql->getSqlStringForSqlObject($eQuery); // Get the string of the Sql, instead of the Select-instance
        $eResult = $dbAdapter->query($eQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
        //get duplicate value
        $dpResult = $dbAdapter->query("SELECT `meta-instance-id`, COUNT(*) c FROM spi_form_v_5 GROUP BY `meta-instance-id` HAVING c > 1", $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        
        $output['avgAuditScore'] = (count($rResult) > 0) ? round($auditScore/count($rResult),2) : 0;
        $output['levelZeroCount'] = count($levelZero);
        $output['levelOneCount'] = count($levelOne);
        $output['levelTwoCount'] = count($levelTwo);
        $output['levelThreeCount'] = count($levelThree);
        $output['levelFourCount'] = count($levelFour);
        $output['eDate'] = $eResult['assesmentofaudit'];
        $output['duplicate'] = count($dpResult);
        return $output;
    }
    
    public function fetchAllV5DuplicateSubmissionsDetails(){
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $rResult = $dbAdapter->query("SELECT `meta-instance-id`,`id`,`facilityname`,`status`,`auditroundno`,`AUDIT_SCORE_PERCENTAGE`,`affiliation`,`level`,`assesmentofaudit`,`testingpointtype`, COUNT(*) c FROM spi_form_v_5 GROUP BY `meta-instance-id` HAVING c > 1", $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        return $rResult;
    }
    
    
    public function fetchAllSubmissionsDatas($parameters,$acl){
        $logincontainer = new Container('credo');
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
	
        $aColumns = array('facilityname','auditroundno','assesmentofaudit' ,'testingpointtype','level','affiliation','AUDIT_SCORE_PERCENTAGE','status');
        $orderColumns = array('facilityname','auditroundno','assesmentofaudit' ,'testingpointtype','level','affiliation','AUDIT_SCORE_PERCENTAGE','status');

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
       $sQuery = $sql->select()->from(array('spiv5' => 'spi_form_v_5'))
                               ->where('spiv5.status != "deleted"');
        if(isset($logincontainer->token) && count($logincontainer->token) > 0){
            $sQuery = $sQuery->where('spiv5.token IN ("' . implode('", "', $logincontainer->token) . '")');
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
        $tQuery =  $sql->select()->from(array('spiv5' => 'spi_form_v_5'))
                                 ->where('spiv5.status != "deleted"');
        if(isset($logincontainer->token) && count($logincontainer->token) > 0){
            $tQuery = $tQuery->where('spiv5.token IN ("' . implode('", "', $logincontainer->token) . '")');
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
        $role = $logincontainer->roleCode;
        if ($acl->isAllowed($role, 'Application\Controller\SpiV5', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
        
        if ($acl->isAllowed($role, 'Application\Controller\SpiV5', 'delete')) {
            $delete = true;
        } else {
            $delete = false;
        }
        
        if ($acl->isAllowed($role, 'Application\Controller\SpiV5', 'download-pdf')) {
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
        $row[] = $aRow['auditroundno'];
        $row[] = $commonService->humanDateFormat($aRow['assesmentofaudit']);
        $row[] = $aRow['testingpointtype'];
        $row[] = $aRow['level'];
        $row[] = $aRow['affiliation'];
        $row[] = round($aRow['AUDIT_SCORE_PERCENTAGE'],2);
        $row[] = ucwords($aRow['status']);
        if($downloadPdfAction){
            //$downloadPdf = '<a href="javascript:void(0);" onclick="downloadPdf('.$aRow['id'].')" style="white-space:nowrap;"><i class="fa fa-download"></i> PDF</a>';
        }
        if($update){
            $edit = '&nbsp;<a href="/spi-v5/edit/' . $aRow['id'] . '" style="white-space:nowrap;"><i class="fa fa-pencil"></i> Edit</a>';
        }
        if($delete){
            $remove = '&nbsp;<a href="javascript:void(0);" onclick="deleteAudit('.$aRow['id'].');" style="white-space:nowrap;"><i class="fa fa-times"></i> Delete</a>';
        }
        $row[] = $edit." ".$downloadPdf. " ".$remove;
        $output['aaData'][] = $row;
       }
       return $output;
    }
    
    public function fetchPendingFacilityNames()
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from(array('spiv5' => 'spi_form_v_5'))
                                ->where(array('spiv5.status'=>'pending'));
        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        return $rResult;
    }

    public function getFormData($id) {
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from(array('spiv5' => 'spi_form_v_5'))
                                ->where(array('spiv5.id'=>$id));
        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
        $sResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
        if($sResult){
            if(trim($sResult->facility)!= '' || trim($sResult->facilityid)!= '' || trim($sResult->facilityname)!= ''){
                //$fQuery = $sql->select()->from(array('spirt5' => 'spi_rt_5_facilities'))
                $fQuery = $sql->select()->from(array('spirt3' => 'spi_rt_3_facilities'))
                                        ->columns(array('fId'=>'id','ffId'=>'facility_id','fName'=>'facility_name','fEmail'=>'email','fCPerson'=>'contact_person','fLatitude'=>'latitude','fLongitude'=>'longitude'));
                if(isset($sResult->facility) && $sResult->facility >0){
                   $fQuery = $fQuery->where("spirt3.id='".$sResult->facility."'");
                }else if(isset($sResult->facilityid) && $sResult->facilityid!= ''){
                   $fQuery = $fQuery->where("spirt3.facility_id='".$sResult->facilityid."'");
                }else if(isset($sResult->facilityname) && $sResult->facilityname!= ''){
                   $fQuery = $fQuery->where("spirt3.facility_name='".$sResult->facilityname."'");
                }
                $fQueryStr = $sql->getSqlStringForSqlObject($fQuery);
                $sResult['facilityInfo'] = $dbAdapter->query($fQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
            }
        }
      return $sResult;
    }

    public function getAuditRoundWiseDataV5($params) {
        $logincontainer = new Container('credo');
        //$rResult = $this->getAllSubmissions();
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from(array('spiv5' => 'spi_form_v_5'))
        ->order(array("id DESC"));
        if(isset($params['roundno']) && $params['roundno']!=''){
            $sQuery = $sQuery->where('spiv5.auditroundno IN ("' . implode('", "', $params['roundno']) . '")');
        }
        if(isset($logincontainer->token) && count($logincontainer->token) > 0){
            $sQuery = $sQuery->where('spiv5.token IN ("' . implode('", "', $logincontainer->token) . '")');
        }
        $start_date = '';
        $end_date = '';
        if (isset($params['dateRange']) && ($params['dateRange'] != "")) {
            $dateField = explode(" ", $params['dateRange']);
            if (isset($dateField[0]) && trim($dateField[0]) != "") {
                $start_date = $this->dateFormat($dateField[0]);                
            }
            if (isset($dateField[2]) && trim($dateField[2]) != "") {
                $end_date = $this->dateFormat($dateField[2]);
            }
        }
        if (trim($start_date) != "" && trim($end_date) != "") {
            $sQuery = $sQuery->where(array("spiv5.assesmentofaudit >='" . $start_date ."'", "spiv5.assesmentofaudit <='" . $end_date."'"));
        }
        if($params['auditRndNo']!=''){
            $sQuery = $sQuery->where("spiv5.auditroundno='".$params['auditRndNo']."'");
        }
        
        if(isset($params['testPoint']) && trim($params['testPoint'])!=''){
            
            if(isset($params['testPoint']) && trim($params['testPoint'])!='' && strtolower(trim($params['testPoint']))== 'other'){
                $sQuery = $sQuery->where("spiv5.testingpointtype_other='".$params['testPointName']."'");
            }else{
                $sQuery = $sQuery->where("spiv5.testingpointtype='".$params['testPoint']."'");
            }

        } if(isset($params['level']) && $params['level']!=''){
            $sQuery = $sQuery->where("spiv5.level='".$params['level']."'");
            }
            if(isset($params['affiliation']) && $params['affiliation']!=''){
                $sQuery = $sQuery->where("spiv5.affiliation='".$params['affiliation']."'");
            }
            
            
            if(isset($params['scoreLevel']) && $params['scoreLevel']!=''){
                if($params['scoreLevel'] == 0){
                    $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE < 40");
                }else if($params['scoreLevel'] == 1){
                    $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv5.AUDIT_SCORE_PERCENTAGE <= 59");
                }else if($params['scoreLevel'] == 2){
                    $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv5.AUDIT_SCORE_PERCENTAGE <= 79");
                }else if($params['scoreLevel'] == 3){
                  $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv5.AUDIT_SCORE_PERCENTAGE <= 89");
                }else if($params['scoreLevel'] == 4){
                  $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 90");
                }
            }
        
        
        if(isset($params['fieldName']) && trim($params['fieldName'])!=''){
            $sQuery = $sQuery->where(array($params['fieldName']=>$params['val']));
        }
        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        
        
        $response = array();
        
        foreach($rResult as $row){
            
            $response[0]['PERSONAL_SCORE'][]=  $row['PERSONAL_SCORE'];
            $response[0]['PHYSICAL_SCORE'][]=  $row['PHYSICAL_SCORE'];
            $response[0]['SAFETY_SCORE'][]=  $row['SAFETY_SCORE'];
            $response[0]['PRETEST_SCORE'][]=  $row['PRETEST_SCORE'];
            $response[0]['TEST_SCORE'][]=  $row['TEST_SCORE'];
            $response[0]['POST_SCORE'][]=  $row['POST_SCORE'];
            $response[0]['EQA_SCORE'][]=  $row['EQA_SCORE'];
            $response[0]['RTRI_SCORE'][]=  $row['RTRI_SCORE'];
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
            $auditRoundWiseData[$auditNo]['RTRI_SCORE'] = array_sum($auditScores['RTRI_SCORE']) / count($auditScores['PERSONAL_SCORE']);
            
        }
        // print_r("Prasath");die;
        $response = array('');
        return $auditRoundWiseData;
        
    }
    

    public function getZeroQuestionCountsV5($params) {
        $logincontainer = new Container('credo');
        //$rResult = $this->fetchAllApprovedSubmissions();
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_5'))
                                ->where(array('spiv3.status'=>'approved'))
                                ->order(array("assesmentofaudit DESC"));
        if(isset($params['roundno']) && $params['roundno']!=''){
            $sQuery = $sQuery->where('spiv3.auditroundno IN ("' . implode('", "', $params['roundno']) . '")');
        }
        
        if(isset($logincontainer->token) && count($logincontainer->token) > 0){
            $sQuery = $sQuery->where('spiv3.token IN ("' . implode('", "', $logincontainer->token) . '")');
        }
        $start_date = '';
        $end_date = '';
        if (isset($params['dateRange']) && ($params['dateRange'] != "")) {
            $dateField = explode(" ", $params['dateRange']);
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
        if($params['auditRndNo']!=''){
            $sQuery = $sQuery->where("spiv3.auditroundno='".$params['auditRndNo']."'");
        }
        
        if(isset($params['testPoint']) && trim($params['testPoint'])!=''){
            $sQuery = $sQuery->where("spiv3.testingpointtype='".$params['testPoint']."'");
            // if(isset($params['testPointName']) && trim($params['testPointName'])!= ''){
                 if(trim($params['testPoint'])!= 'other'){
                    $sQuery = $sQuery->where("spiv3.testingpointname='".$params['testPointName']."'");
                 }else{
                    $sQuery = $sQuery->where("spiv3.testingpointtype_other='".$params['testPointName']."'");
                 }
            // }
            } if(isset($params['level']) && $params['level']!=''){
               $sQuery = $sQuery->where("spiv3.level='".$params['level']."'");
            }
            if(isset($params['affiliation']) && $params['affiliation']!=''){
               $sQuery = $sQuery->where("spiv3.affiliation='".$params['affiliation']."'");
            }
            // if(is_array($params['province']) && count($params['province'])>0 ){
            // $sQuery = $sQuery->join(array('f'=>'spi_rt_3_facilities'),'f.id=spiv3.facility',array('province','district'))
            //                 ->where('f.province IN ("' . implode('", "', $params['province']) . '")');
            // if(is_array($params['district']) && count($params['district'])>0 ){
            //     $sQuery = $sQuery->where('f.province IN ("' . implode('", "', $params['province']) . '")');
            // }
            // }else{
            //     if($params['province']!=''){
            //         $provinces = explode(",",$params['province']);
            //         $sQuery = $sQuery->join(array('f'=>'spi_rt_3_facilities'),'f.id=spiv3.facility',array('province','district'))
            //                         ->where('f.province IN ("' . implode('", "', $provinces) . '")');
            //     }
            // }
            // if($params['province']!=''){
            //     if(is_array($params['district']) && count($params['district'])>0 ){
            //         $sQuery = $sQuery->where('f.district IN ("' . implode('", "', $params['district']) . '")');
            //     }else{
            //         if($params['district']!=''){
            //             $provinces = explode(",",$params['district']);
            //             $sQuery = $sQuery->where('f.district IN ("' . implode('", "', $provinces) . '")');
            //         }
            //     }
            // }
            if(isset($params['scoreLevel']) && $params['scoreLevel']!=''){
                if($params['scoreLevel'] == 0){
                  $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE < 40");
                }else if($params['scoreLevel'] == 1){
                  $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 59");
                }else if($params['scoreLevel'] == 2){
                  $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 79");
                }else if($params['scoreLevel'] == 3){
                  $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 89");
                }else if($params['scoreLevel'] == 4){
                  $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 90");
                }
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
    
    public function fetchAllApprovedSubmissionsV5($sortOrder = 'DESC'){
        $logincontainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_5'))
                                ->where(array('status'=>'approved'))
                                ->order(array("assesmentofaudit $sortOrder"));
        if(isset($logincontainer->token) && count($logincontainer->token) > 0){
            $sQuery = $sQuery->where('spiv3.token IN ("' . implode('", "', $logincontainer->token) . '")');
        }
        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        return $rResult;
    }
    
    public function fecthAllApprovedSubmissionsTable($parameters){
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
	$queryContainer = new Container('query');
        $logincontainer = new Container('credo');
        $aColumns = array('facilityname','assesmentofaudit' ,'testingpointname','PERSONAL_SCORE','PHYSICAL_SCORE','SAFETY_SCORE','PRETEST_SCORE','TEST_SCORE','POST_SCORE','EQA_SCORE','FINAL_AUDIT_SCORE','AUDIT_SCORE_PERCENTAGE');
        $orderColumns = array('facilityname','assesmentofaudit' ,'testingpointname','PERSONAL_SCORE','PHYSICAL_SCORE','SAFETY_SCORE','PRETEST_SCORE','TEST_SCORE','POST_SCORE','EQA_SCORE','FINAL_AUDIT_SCORE','AUDIT_SCORE_PERCENTAGE');

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
        $start_date = '';
        $end_date = '';
        if (isset($parameters['dateRange']) && ($parameters['dateRange'] != "")) {
            $dateField = explode(" ", $parameters['dateRange']);
            if (isset($dateField[0]) && trim($dateField[0]) != "") {
                $start_date = $this->dateFormat($dateField[0]);                
            }
            if (isset($dateField[2]) && trim($dateField[2]) != "") {
                $end_date = $this->dateFormat($dateField[2]);
            }
        }
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                                ->where(array('spiv3.status'=>'approved'));
        if($parameters['auditRndNo']!=''){
         $sQuery = $sQuery->where("spiv3.auditroundno='".$parameters['auditRndNo']."'");
        }if (trim($start_date) != "" && trim($end_date) != "") {
            $sQuery = $sQuery->where(array("spiv3.assesmentofaudit >='" . $start_date ."'", "spiv3.assesmentofaudit <='" . $end_date."'"));
        }if(isset($parameters['testPoint']) && trim($parameters['testPoint'])!=''){
            $sQuery = $sQuery->where("spiv3.testingpointtype='".$parameters['testPoint']."'");
            if(trim($parameters['testPoint'])!= 'other'){
            $sQuery = $sQuery->where("spiv3.testingpointname='".$parameters['testPointName']."'");
            }else{
            $sQuery = $sQuery->where("spiv3.testingpointtype_other='".$parameters['testPointName']."'");
            }
        }if($parameters['level']!=''){
         $sQuery = $sQuery->where("spiv3.level='".$parameters['level']."'");
        }
        if(is_array($parameters['province']) && count($parameters['province'])>0 ){
        $sQuery = $sQuery->join(array('f'=>'spi_rt_3_facilities'),'f.id=spiv3.facility',array('province','district'))
                        ->where('f.province IN ("' . implode('", "', $parameters['province']) . '")');
        if(is_array($parameters['district']) && count($parameters['district'])>0 ){
            $sQuery = $sQuery->where('f.province IN ("' . implode('", "', $parameters['province']) . '")');
        }
        }else{
            if($parameters['province']!=''){
                $provinces = explode(",",$parameters['province']);
                $sQuery = $sQuery->join(array('f'=>'spi_rt_3_facilities'),'f.id=spiv3.facility',array('province','district'))
                                ->where('f.province IN ("' . implode('", "', $provinces) . '")');
            }
        }
        if($parameters['province']!=''){
            if(is_array($parameters['district']) && count($parameters['district'])>0 ){
                $sQuery = $sQuery->where('f.district IN ("' . implode('", "', $parameters['district']) . '")');
            }else{
                if($parameters['district']!=''){
                    $provinces = explode(",",$parameters['district']);
                    $sQuery = $sQuery->where('f.district IN ("' . implode('", "', $provinces) . '")');
                }
            }
        }
        if($parameters['affiliation']!=''){
         $sQuery = $sQuery->where("spiv3.affiliation='".$parameters['affiliation']."'");
        }if(isset($logincontainer->token) && count($logincontainer->token) > 0){
            $sQuery = $sQuery->where('spiv3.token IN ("' . implode('", "', $logincontainer->token) . '")');
        }if(isset($parameters['scoreLevel']) && $parameters['scoreLevel']!=''){
            if($parameters['scoreLevel'] == 0){
              $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE < 40");
            }else if($parameters['scoreLevel'] == 1){
              $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 59");
            }else if($parameters['scoreLevel'] == 2){
              $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 79");
            }else if($parameters['scoreLevel'] == 3){
              $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 89");
            }else if($parameters['scoreLevel'] == 4){
              $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 90");
            }
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
       $queryContainer->exportQuery = $sQuery;
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
        $tQuery =  $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                                ->where(array('spiv3.status'=>'approved'));
        if($parameters['auditRndNo']!=''){
           $tQuery = $tQuery->where("spiv3.auditroundno='".$parameters['auditRndNo']."'");
        }if (trim($start_date) != "" && trim($end_date) != "") {
            $tQuery = $tQuery->where(array("spiv3.assesmentofaudit >='" . $start_date ."'", "spiv3.assesmentofaudit <='" . $end_date."'"));
        }if(isset($parameters['testPoint']) && trim($parameters['testPoint'])!=''){
            $tQuery = $tQuery->where("spiv3.testingpointtype='".$parameters['testPoint']."'");
            // if(isset($parameters['testPointName']) && trim($parameters['testPointName'])!= ''){
                 if(trim($parameters['testPoint'])!= 'other'){
                    $tQuery = $tQuery->where("spiv3.testingpointname='".$parameters['testPointName']."'");
                 }else{
                    $tQuery = $tQuery->where("spiv3.testingpointtype_other='".$parameters['testPointName']."'");
                 }
            // }
        }if($parameters['level']!=''){
         $tQuery = $tQuery->where("spiv3.level='".$parameters['level']."'");
        }
        // if(is_array($parameters['province']) && count($parameters['province'])>0 ){
        // $tQuery = $tQuery->join(array('f'=>'spi_rt_3_facilities'),'f.id=spiv3.facility',array('province','district'))
        //                 ->where('f.province IN ("' . implode('", "', $parameters['province']) . '")');
        // if(is_array($parameters['district']) && count($parameters['district'])>0 ){
        //     $tQuery = $tQuery->where('f.province IN ("' . implode('", "', $parameters['province']) . '")');
        // }
        // }else{
        //     if($parameters['province']!=''){
        //         $provinces = explode(",",$parameters['province']);
        //         $tQuery = $tQuery->join(array('f'=>'spi_rt_3_facilities'),'f.id=spiv3.facility',array('province','district'))
        //                         ->where('f.province IN ("' . implode('", "', $provinces) . '")');
        //     }
        // }
        // if($parameters['province']!=''){
        //     if(is_array($parameters['district']) && count($parameters['district'])>0 ){
        //         $tQuery = $tQuery->where('f.district IN ("' . implode('", "', $parameters['district']) . '")');
        //     }else{
        //         if($parameters['district']!=''){
        //             $provinces = explode(",",$parameters['district']);
        //             $tQuery = $tQuery->where('f.district IN ("' . implode('", "', $provinces) . '")');
        //         }
        //     }
        // }
        if($parameters['affiliation']!=''){
         $tQuery = $tQuery->where("spiv3.affiliation='".$parameters['affiliation']."'");
        }if(isset($logincontainer->token) && count($logincontainer->token) > 0){
            $tQuery = $tQuery->where('spiv3.token IN ("' . implode('", "', $logincontainer->token) . '")');
        }if(isset($parameters['scoreLevel']) && $parameters['scoreLevel']!=''){
            if($parameters['scoreLevel'] == 0){
              $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE < 40");
            }else if($parameters['scoreLevel'] == 1){
              $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 59");
            }else if($parameters['scoreLevel'] == 2){
              $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 79");
            }else if($parameters['scoreLevel'] == 3){
              $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 89");
            }else if($parameters['scoreLevel'] == 4){
              $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 90");
            }
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
        
        $commonService = new \Application\Service\CommonService();
       foreach ($rResult as $aRow) {
        $row = array();
        $row[] = $aRow['facilityname'];
        $row[] = $commonService->humanDateFormat($aRow['assesmentofaudit']);
        $row[] = $aRow['testingpointname']. " - " .$aRow['testingpointtype'];
        $row[] = $aRow['PERSONAL_SCORE'];
        $row[] = $aRow['PHYSICAL_SCORE'];
        $row[] = $aRow['SAFETY_SCORE'];
        $row[] = $aRow['PRETEST_SCORE'];
        $row[] = $aRow['TEST_SCORE'];
        $row[] = $aRow['POST_SCORE'];
        $row[] = $aRow['EQA_SCORE'];
        $row[] = $aRow['FINAL_AUDIT_SCORE'];
        $row[] = round($aRow['AUDIT_SCORE_PERCENTAGE'],2);
        $output['aaData'][] = $row;
       }
       return $output;
    }


    public function fetchAllApprovedV5FormSubmissionsTable($parameters){
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */

	    $queryContainer = new Container('query');
        $logincontainer = new Container('credo');
        $aColumns = array('facilityname','assesmentofaudit' ,'testingpointtype','PERSONAL_SCORE','PHYSICAL_SCORE','SAFETY_SCORE','PRETEST_SCORE','TEST_SCORE','POST_SCORE','EQA_SCORE','RTRI_SCORE','FINAL_AUDIT_SCORE','AUDIT_SCORE_PERCENTAGE');
        $orderColumns = array('facilityname','assesmentofaudit' ,'testingpointtype','PERSONAL_SCORE','PHYSICAL_SCORE','SAFETY_SCORE','PRETEST_SCORE','TEST_SCORE','POST_SCORE','EQA_SCORE','RTRI_SCORE','FINAL_AUDIT_SCORE','AUDIT_SCORE_PERCENTAGE');

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
        $start_date = '';
        $end_date = '';
        if (isset($parameters['dateRange']) && ($parameters['dateRange'] != "")) {
            $dateField = explode(" ", $parameters['dateRange']);
            if (isset($dateField[0]) && trim($dateField[0]) != "") {
                $start_date = $this->dateFormat($dateField[0]);                
            }
            if (isset($dateField[2]) && trim($dateField[2]) != "") {
                $end_date = $this->dateFormat($dateField[2]);
            }
        }
        $sQuery = $sql->select()->from(array('spiv5' => 'spi_form_v_5'))
                                ->where(array('spiv5.status'=>'approved'));
        if($parameters['auditRndNo']!=''){
         $sQuery = $sQuery->where("spiv5.auditroundno='".$parameters['auditRndNo']."'");
        }if (trim($start_date) != "" && trim($end_date) != "") {
            $sQuery = $sQuery->where(array("spiv5.assesmentofaudit >='" . $start_date ."'", "spiv5.assesmentofaudit <='" . $end_date."'"));
        }
        if(isset($parameters['testPoint']) && trim($parameters['testPoint'])!=''){
            
            if(trim($parameters['testPoint'])!= 'other'){
                $sQuery = $sQuery->where("spiv5.testingpointtype='".$parameters['testPoint']."'");
            }else{
            $sQuery = $sQuery->where("spiv5.testingpointtype_other='".$parameters['testPointName']."'");
            }
        }if($parameters['level']!=''){
         $sQuery = $sQuery->where("spiv5.level='".$parameters['level']."'");
        }
        
        if($parameters['affiliation']!=''){
         $sQuery = $sQuery->where("spiv5.affiliation='".$parameters['affiliation']."'");
        }if(isset($logincontainer->token) && count($logincontainer->token) > 0){
            $sQuery = $sQuery->where('spiv5.token IN ("' . implode('", "', $logincontainer->token) . '")');
        }if(isset($parameters['scoreLevel']) && $parameters['scoreLevel']!=''){
            if($parameters['scoreLevel'] == 0){
              $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE < 40");
            }else if($parameters['scoreLevel'] == 1){
              $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv5.AUDIT_SCORE_PERCENTAGE <= 59");
            }else if($parameters['scoreLevel'] == 2){
              $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv5.AUDIT_SCORE_PERCENTAGE <= 79");
            }else if($parameters['scoreLevel'] == 3){
              $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv5.AUDIT_SCORE_PERCENTAGE <= 89");
            }else if($parameters['scoreLevel'] == 4){
              $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 90");
            }
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
       $queryContainer->exportQuery = $sQuery;
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
        $tQuery =  $sql->select()->from(array('spiv5' => 'spi_form_v_3'))
                                ->where(array('spiv5.status'=>'approved'));
        if($parameters['auditRndNo']!=''){
           $tQuery = $tQuery->where("spiv5.auditroundno='".$parameters['auditRndNo']."'");
        }if (trim($start_date) != "" && trim($end_date) != "") {
            $tQuery = $tQuery->where(array("spiv5.assesmentofaudit >='" . $start_date ."'", "spiv5.assesmentofaudit <='" . $end_date."'"));
        }if(isset($parameters['testPoint']) && trim($parameters['testPoint'])!=''){
            $tQuery = $tQuery->where("spiv5.testingpointtype='".$parameters['testPoint']."'");
             if(isset($parameters['testPointName']) && trim($parameters['testPointName'])!= ''){
                 if(trim($parameters['testPoint'])== 'other' && trim($parameters['testPointName'])!=''){
                   
                    $tQuery = $tQuery->where("spiv5.testingpointtype_other='".$parameters['testPointName']."'");
                 }
             }
        }
        if($parameters['level']!=''){
         $tQuery = $tQuery->where("spiv5.level='".$parameters['level']."'");
        }
        
        if($parameters['affiliation']!=''){
         $tQuery = $tQuery->where("spiv5.affiliation='".$parameters['affiliation']."'");
        }if(isset($logincontainer->token) && count($logincontainer->token) > 0){
            $tQuery = $tQuery->where('spiv5.token IN ("' . implode('", "', $logincontainer->token) . '")');
        }if(isset($parameters['scoreLevel']) && $parameters['scoreLevel']!=''){
            if($parameters['scoreLevel'] == 0){
              $tQuery = $tQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE < 40");
            }else if($parameters['scoreLevel'] == 1){
              $tQuery = $tQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv5.AUDIT_SCORE_PERCENTAGE <= 59");
            }else if($parameters['scoreLevel'] == 2){
              $tQuery = $tQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv5.AUDIT_SCORE_PERCENTAGE <= 79");
            }else if($parameters['scoreLevel'] == 3){
              $tQuery = $tQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv5.AUDIT_SCORE_PERCENTAGE <= 89");
            }else if($parameters['scoreLevel'] == 4){
              $tQuery = $tQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 90");
            }
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
        
        $commonService = new \Application\Service\CommonService();
       foreach ($rResult as $aRow) {
        $row = array();
        $row[] = $aRow['facilityname'];
        $row[] = $commonService->humanDateFormat($aRow['assesmentofaudit']);
        $row[] = $aRow['testingpointname']. " - " .$aRow['testingpointtype'];
        $row[] = $aRow['PERSONAL_SCORE'];
        $row[] = $aRow['PHYSICAL_SCORE'];
        $row[] = $aRow['SAFETY_SCORE'];
        $row[] = $aRow['PRETEST_SCORE'];
        $row[] = $aRow['TEST_SCORE'];
        $row[] = $aRow['POST_SCORE'];
        $row[] = $aRow['EQA_SCORE'];
        $row[] = $aRow['RTRI_SCORE'];
        $row[] = $aRow['FINAL_AUDIT_SCORE'];
        $row[] = round($aRow['AUDIT_SCORE_PERCENTAGE'],2);
        $output['aaData'][] = $row;
       }
       return $output;
    }
    
    public function fetchAllApprovedSubmissionLocationV5($params){
        $logincontainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from(array('spiv5' => 'spi_form_v_5'))
                                ->where(array('spiv5.status'=>'approved'))
                                ->order(array("assesmentofaudit DESC"));
        if(isset($params['roundno']) && $params['roundno']!=''){
            $sQuery = $sQuery->where('spiv5.auditroundno IN ("' . implode('", "', $params['roundno']) . '")');
        }
        if(isset($logincontainer->token) && count($logincontainer->token) > 0){
            $sQuery = $sQuery->where('spiv5.token IN ("' . implode('", "', $logincontainer->token) . '")');
        }
        $start_date = '';
        $end_date = '';
        if (isset($params['dateRange']) && ($params['dateRange'] != "")) {
            $dateField = explode(" ", $params['dateRange']);
            if (isset($dateField[0]) && trim($dateField[0]) != "") {
                $start_date = $this->dateFormat($dateField[0]);                
            }
            if (isset($dateField[2]) && trim($dateField[2]) != "") {
                $end_date = $this->dateFormat($dateField[2]);
            }
        }
        if (trim($start_date) != "" && trim($end_date) != "") {
            $sQuery = $sQuery->where(array("spiv5.assesmentofaudit >='" . $start_date ."'", "spiv5.assesmentofaudit <='" . $end_date."'"));
        }
        if($params['auditRndNo']!=''){
            $sQuery = $sQuery->where("spiv5.auditroundno='".$params['auditRndNo']."'");
        }
        
        if(isset($params['testPoint']) && trim($params['testPoint'])!=''){
            $sQuery = $sQuery->where("spiv5.testingpointtype='".$params['testPoint']."'");
            // if(isset($params['testPointName']) && trim($params['testPointName'])!= ''){
                 if(trim($params['testPoint'])!= 'other'){
                    $sQuery = $sQuery->where("spiv5.testingpointname='".$params['testPointName']."'");
                 }else{
                    $sQuery = $sQuery->where("spiv5.testingpointtype_other='".$params['testPointName']."'");
                 }
            // }
            } if(isset($params['level']) && $params['level']!=''){
               $sQuery = $sQuery->where("spiv5.level='".$params['level']."'");
            }
            if(isset($params['affiliation']) && $params['affiliation']!=''){
               $sQuery = $sQuery->where("spiv5.affiliation='".$params['affiliation']."'");
            }
            // if(is_array($params['province']) && count($params['province'])>0 ){
            // $sQuery = $sQuery->join(array('f'=>'spi_rt_3_facilities'),'f.id=spiv5.facility',array('province','district'))
            //                 ->where('f.province IN ("' . implode('", "', $params['province']) . '")');
            // if(is_array($params['district']) && count($params['district'])>0 ){
            //     $sQuery = $sQuery->where('f.province IN ("' . implode('", "', $params['province']) . '")');
            // }
            // }else{
            //     if($params['province']!=''){
            //         $provinces = explode(",",$params['province']);
            //         $sQuery = $sQuery->join(array('f'=>'spi_rt_3_facilities'),'f.id=spiv5.facility',array('province','district'))
            //                         ->where('f.province IN ("' . implode('", "', $provinces) . '")');
            //     }
            // }
            // if($params['province']!=''){
            //     if(is_array($params['district']) && count($params['district'])>0 ){
            //         $sQuery = $sQuery->where('f.district IN ("' . implode('", "', $params['district']) . '")');
            //     }else{
            //         if($params['district']!=''){
            //             $provinces = explode(",",$params['district']);
            //             $sQuery = $sQuery->where('f.district IN ("' . implode('", "', $provinces) . '")');
            //         }
            //     }
            // }
            if(isset($params['scoreLevel']) && $params['scoreLevel']!=''){
                if($params['scoreLevel'] == 0){
                  $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE < 40");
                }else if($params['scoreLevel'] == 1){
                  $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv5.AUDIT_SCORE_PERCENTAGE <= 59");
                }else if($params['scoreLevel'] == 2){
                  $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv5.AUDIT_SCORE_PERCENTAGE <= 79");
                }else if($params['scoreLevel'] == 3){
                  $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv5.AUDIT_SCORE_PERCENTAGE <= 89");
                }else if($params['scoreLevel'] == 4){
                  $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE >= 90");
                }
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
    
    public function updateSpiV5FormDetails($params){
        // \Zend\Debug\Debug::dump($params);
        if (trim($params['formId']) != "") {
            $dbAdapter = $this->adapter;
            $sql = new Sql($dbAdapter);
            $formId=base64_decode($params['formId']);
            $summationData=array();
            if(isset($params['sectionNo'])){
                $n=count($params['sectionNo']);
                for ($i = 0; $i < $n; $i++) {
					$rowId=$params['rowId'][$i];
					if(isset($params['sectionNo'][$i]) && trim($params['sectionNo'][$i])!="" && trim($params['deficiency'][$i])!="" && trim($params['correction'.$rowId])!=""){
                        // \Zend\Debug\Debug::dump($summationData);die;
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
                // \Zend\Debug\Debug::dump($summationData);die;
            }

            
            // update facility tbl
            $id = 0;
            //$facilityDb = new SpiRt5FacilitiesTable($dbAdapter);
            $facilityDb = new SpiRtFacilitiesTable($dbAdapter);
            if(trim($params['testingFacility'])!= ''){
                $id = base64_decode($params['testingFacility']);
                $facilityDb->updateFacilityInfo($id,$params);
            }else if(trim($params['testingFacilityName'])!= ''){
                //$fQuery = $sql->select()->from(array('spirt5' => 'spi_rt_5_facilities'))
                $fQuery = $sql->select()->from(array('spirt3' => 'spi_rt_3_facilities'))
                                        ->columns(array('id'))
                                        ->where("spirt3.facility_name='".$params['testingFacilityName']."'");
                $fQueryStr = $sql->getSqlStringForSqlObject($fQuery);
                $fResult = $dbAdapter->query($fQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
                if($fResult){
                   $id = $fResult->id;
                   $facilityDb->updateFacilityInfo($id,$params);
                }else{
                   $id = $facilityDb->addFacilityInfo($params); 
                }
            }
            
            $data = array(
                //'assesmentofaudit' => $this->dateFormat($params['auditDate']),
                'auditroundno' => $params['auditRound'],
                'facility' => ($id > 0)?$id:null,
                'facilityid' => $params['testingFacilityId'],
                'facilityname' => $params['testingFacilityName'],
                // 'testingpointname' => $params['testingPointName'],
                'testingpointtype' => $params['testingPointType'],
                'testingpointtype_other' => $params['testingPointTypeOther'],
                'physicaladdress' => $params['location'],
                'level' => $params['level'],
                'level_other' => $params['levelOther'],
                // 'level_name' => $params['levelName'],
                'affiliation' => $params['affiliation'],
                'affiliation_other' => $params['affiliationOther'],
                'NumberofTester' => (isset($params['NumberofTester']) && $params['NumberofTester'] > 0 ? $params['NumberofTester'] : 0),
                // 'avgMonthTesting' => (isset($params['avgMonthTesting']) && $params['avgMonthTesting'] > 0 ? $params['avgMonthTesting'] : 0),
                'name_auditor_lead' => $params['name_auditor_lead'],
                'name_auditor2' => $params['name_auditor2'],
                'PERSONAL_C_1_1_HIV_TRAINING' => $params['personal_c_1_1'],
                'PERSONAL_C_1_2_HIV_TESTING_REGISTER' => $params['personal_c_1_2'],
                'PERSONAL_C_1_3_EQA_PT' => $params['personal_c_1_3'],
                'PERSONAL_C_1_4_QC_PROCESS' => $params['personal_c_1_4'],
                'PERSONAL_C_1_5_SAFETY_MANAGEMENT' => $params['personal_c_1_5'],
                'PERSONAL_C_1_6_REFRESHER_TRAINING' => $params['personal_c_1_6'],
                'PERSONAL_C_1_7_HIV_COMPETENCY_TESTING' => $params['personal_c_1_7'],
                'PERSONAL_C_1_8_NATIONAL_CERTIFICATION' => $params['personal_c_1_8'],
                'PERSONAL_C_1_9_CERTIFIED_TESTERS' => $params['personal_c_1_9'],
                'PERSONAL_C_1_10_RECERTIFIED' => $params['personal_c_1_10'],
                'PHYSICAL_C_2_1_DESIGNATED_HIV_AREA' => $params['physical_c_2_1'],
                'PHYSICAL_C_2_2_CLEAN_TESTING_AREA' => $params['physical_c_2_2'],
                'PHYSICAL_C_2_3_SUFFICIENT_LIGHT_AVAILABILITY' => $params['physical_c_2_3'],
                'PHYSICAL_C_2_4_TEST_KIT_STORAGE' => $params['physical_c_2_4'],
                'PHYSICAL_C_2_5_SUFFICIENT_SECURE_STORAGE' => $params['physical_c_2_5'],
                'SAFETY_C_3_1_IMPLEMENT_SAFETY_PRACTICES' => $params['safety_c_3_1'],
                'SAFETY_C_3_2_ACCIDENTAL_EXPOSURE' => $params['safety_c_3_2'],
                'SAFETY_C_3_3_PRACTICE_SAFETY_PRACTICES' => $params['safety_c_3_3'],
                'SAFETY_C_3_4_PPE_AVAILABILITY' => $params['safety_c_3_4'],
                'SAFETY_C_3_5_PPE_USED_PROPERLY' => $params['safety_c_3_5'],
                'SAFETY_C_3_6_WATER_SOAP_AVAILABILITY' => $params['safety_c_3_6'],
                'SAFETY_C_3_7_DISINFECTANT_AVAILABLE' => $params['safety_c_3_7'],
                'SAFETY_C_3_8_DISINFECTANT_LABELED_PROPERLY' => $params['safety_c_3_8'],
                'SAFETY_C_3_9_SEGREGATION_OF_WASTE' => $params['safety_c_3_9'],
                'SAFETY_C_3_10_INFECTIOUS_WASTE_EMPTIED' => $params['safety_c_3_10'],
                'PRE_C_4_1_NATIONAL_GUIDELINES' => $params['pre_c_4_1'],
                'PRE_C_4_2_HIV_TESTING_ALGORITHM' => $params['pre_c_4_2'],
                'PRE_C_4_3_TEST_PROCEDURES_ACCESSIBLE' => $params['pre_c_4_3'],
                'PRE_C_4_4_TEST_PROCEDURES_ACCURATE' => $params['pre_c_4_4'],
                'PRE_C_4_5_APPROVED_KITS_AVAILABLE' => $params['pre_c_4_5'],
                'PRE_C_4_6_HIV_KITS_EXPIRATION' => $params['pre_c_4_6'],
                'PRE_C_4_7_KIT_SUPPLIES_AVAILABILITY' => $params['pre_c_4_7'],
                'PRE_C_4_8_STOCK_MANAGEMENT' => $params['pre_c_4_8'],
                'PRE_C_4_9_DOCUMENTED_INVENTORY' => $params['pre_c_4_9'],
                'PRE_C_4_10_SOPS_BLOOD_COLLECTION' => $params['pre_c_4_10'],
                'PRE_C_4_11_BLOOD_COLLECTION_SUPPLIES' => $params['pre_c_4_11'],
                'PRE_C_4_12_CLIENT_IDENTIFICATION' => $params['pre_c_4_12'],
                'TEST_C_5_1_PROCEDURES_TESTING_ALGORITHM' => $params['test_c_5_1'],
                'TEST_C_5_2_TIMERS_AVAILABILITY' => $params['test_c_5_2'],
                'TEST_C_5_3_SAMPLE_DEVICE_ACCURACY' => $params['test_c_5_3'],
                'TEST_C_5_4_TESTING_PROCEDURE_FOLLOWED' => $params['test_c_5_4'],
                'TEST_C_5_5_QUALITY_CONTROL' => $params['test_c_5_5'],
                'TEST_C_5_6_QC_RESULTS_RECORDED' => $params['test_c_5_6'],
                'TEST_C_5_7_INCORRECT_QC_RESULTS' => $params['test_c_5_7'],
                'TEST_C_5_8_APPROPRIATE_STEPS_TAKEN' => $params['test_c_5_8'],
                'TEST_C_5_9_REVIEW_QC_RECORDS' => $params['test_c_5_9'],
                'POST_C_6_1_STANDARDIZED_HIV_REGISTER' => $params['post_C_6_1'],
                'POST_C_6_2_ELEMENTS_CAPTURED_CORRECTLY' => $params['post_C_6_2'],
                'POST_C_6_3_PAGE_TOTAL_SUMMARY' => $params['post_C_6_3'],
                'POST_C_6_4_INVALID_TEST_RESULT_RECORDED' => $params['post_C_6_4'],
                'POST_C_6_5_APPROPRIATE_STEPS_TAKEN' => $params['post_C_6_5'],
                'POST_C_6_6_REGISTERS_REVIEWED' => $params['post_C_6_6'],
                'POST_C_6_7_DOCUMENTS_SECURELY_KEPT' => $params['post_C_6_7'],
                'POST_C_6_8_REGISTER_SECURE_LOCATION' => $params['post_C_6_8'],
                'POST_C_6_9_REGISTERS_PROPERLY_LABELED' => $params['post_C_6_9'],
                'EQA_C_7_1_PT_ENROLLMENT' => $params['eqa_c_7_1'],
                'EQA_C_7_2_TESTING_EQAPT_SAMPLES' => $params['eqa_c_7_2'],
                'EQA_C_7_3_REVIEW_BEFORE_SUBMISSION' => $params['eqa_c_7_3'],
                'EQA_C_7_4_FEEDBACK_RECEIVED_REVIEWED' => $params['eqa_c_7_4'],
                'EQA_C_7_5_IMPLEMENT_CORRECTIVE_ACTION' => $params['eqa_c_7_5'],
                'EQA_C_7_6_RECEIVE_PERIODIC_VISITS' => $params['eqa_c_7_6'],
                'EQA_C_7_7_FEEDBACK_PROVIDED_DOCUMENTED' => $params['eqa_c_7_7'],
                'EQA_C_7_8_TESTERS_RETRAINED_IN_VISITS' => $params['eqa_c_7_8'],
                'RTRI_C_8_1_TESTERS_RECEIVED_RTRI_TRAINING' => $params['rtri_C_8_1'],
                'RTRI_C_8_2_TESTERS_DEMONSTRATED_COMPETENCY' => $params['rtri_C_8_2'],
                'RTRI_C_8_3_JOBAIDS_READILY_AVAILABLE' => $params['rtri_C_8_3'],
                'RTRI_C_8_4_SUFFICIENT_SUPPLY_AVAILABLE' => $params['rtri_C_8_4'],
                'RTRI_C_8_5_RTRI_KIT_STORAGE' => $params['rtri_C_8_5'],
                'RTRI_C_8_6_RTRI_TESTING_PROCEDURE_FOLLOWED' => $params['rtri_C_8_6'],
                'RTRI_C_8_7_RTRI_TESTING_RESULTS_DOCUMENTED' => $params['rtri_C_8_7'],
                'RTRI_C_8_8_QC_ROUTINELY_USED' => $params['rtri_C_8_8'],
                'RTRI_C_8_9_QC_RESULTS_RECORDED' => $params['rtri_C_8_9'],
                'RTRI_C_8_10_INCORRECT_QC_DOCUMENTED' => $params['rtri_C_8_10'],
                'RTRI_C_8_11_INVALID_RTRI_RESULTS' => $params['rtri_C_8_11'],
                'Latitude' => $params['latitude'],
                'Longitude' => $params['longitude'],
                
                'staffaudited' => $params['staffAuditedName'],
                'durationaudit' => $params['durationaudit'],
                //'FINAL_AUDIT_SCORE' => $params['totalPointsScored'],
                //'MAX_AUDIT_SCORE' => $params['totalScoreExpect'],
                //'AUDIT_SCORE_PERCENTAGE' => $params['auditScorePercentage'],
                'correctiveaction' => $summationData
            );
            // \Zend\Debug\Debug::dump($data);die;

            $result = $this->update($data, array('id' =>$formId));
            
            return $formId;
        }
        
    }
    public function fetchSpiV5FormAuditNo(){
        $logincontainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_5'))
                               ->columns(array(new Expression('DISTINCT(auditroundno) as auditroundno'),'rowCount' => new Expression("COUNT('auditroundno')")))
                               ->group('auditroundno')
                               ->order("auditroundno ASC");
        if(isset($logincontainer->token) && count($logincontainer->token) > 0){
            $sQuery = $sQuery->where('spiv3.token IN ("' . implode('", "', $logincontainer->token) . '")');
        }
        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        return $rResult;
    }
    
    public function fetchSpiV3FormFacilityAuditNo($params){
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                                ->columns(array(new Expression('DISTINCT(auditroundno) as auditroundno'),'rowCount' => new Expression("COUNT('auditroundno')")))
                                ->where(array($params['fieldName']=>$params['val']))
                                ->group('auditroundno')
                                ->order("auditroundno ASC");
        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        
        return $rResult;
    }
    
    public function mergeFacilityName($params){
        $result = 0;
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $facilityDb= new \Application\Model\SpiRtFacilitiesTable($this->adapter);
        if(isset($params['editFacilityName']) && trim($params['editFacilityName'])!= ''){
            $facilityQuery = $sql->select()->from(array('spirt3' => 'spi_rt_3_facilities'))->columns(array('facility_name'))
                                           ->where(array('spirt3.facility_name'=>$params['dafaultFacilityName']));
            $facilityQueryStr = $sql->getSqlStringForSqlObject($facilityQuery);
            $facilityResult = $dbAdapter->query($facilityQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
            if($facilityResult){
                $data = array(
                              'facility_id'=>$params['facilityId'],
                              'facility_name'=>$params['editFacilityName']
                            );
                $facilityDb->update($data,array('facility_name'=>$params['dafaultFacilityName']));
            }
            
            $aQuery = $sql->select()->from(array('spiv5' => 'spi_form_v_5'))->columns(array('facilityname'))
                                    ->where(array('spiv5.facilityname'=>$params['dafaultFacilityName']));
            $aQueryStr = $sql->getSqlStringForSqlObject($aQuery);
            $aResult = $dbAdapter->query($aQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
            if($aResult){
                $data = array(
                              'facilityid'=>$params['facilityId'],
                              'facilityname'=>$params['editFacilityName']
                            );
                $this->update($data,array('facilityname'=>$params['dafaultFacilityName']));
            }
        }
        $c = count($params['upFaciltyName']);
        for($i=0;$i<$c;$i++){
            $aQuery = $sql->select()->from(array('spiv5' => 'spi_form_v_5'))->columns(array('facilityname','id'))
                                    ->where(array('spiv5.facilityname'=>$params['upFaciltyName'][$i]));
            $aQueryStr = $sql->getSqlStringForSqlObject($aQuery);
            $aResult = $dbAdapter->query($aQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
            
            if(count($aResult)>0){
                for($k=0;$k<count($aResult);$k++){
                    $data = array(
                       'facilityid'=>$params['facilityId'],
                       'facilityname'=>$params['editFacilityName']
                    );
                    $result =  $this->update($data,array('id'=>$aResult[$k]['id']));
                }
            }
            //Update status in Facility table
            $facilityQuery = $sql->select()->from(array('spirt3' => 'spi_rt_3_facilities'))->columns(array('facility_name'))
                                           ->where(array('spirt3.facility_name'=>$params['upFaciltyName'][$i]));
            $facilityQueryStr = $sql->getSqlStringForSqlObject($facilityQuery);
            $facilityResult = $dbAdapter->query($facilityQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
            if($facilityResult){
                $facilityDb->update(array('status'=>'deleted'),array('facility_name'=>$params['upFaciltyName'][$i]));
            }
        }
        return $result;
    }
    
    //get all faciltiy name
    public function fetchAllFacilityNames(){
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $uQuery = $sql->select()->from(array('spiv5' => 'spi_form_v_5'))->columns(array('facilityname'=>new Expression("DISTINCT facilityname")));
        $uQueryStr = $sql->getSqlStringForSqlObject($uQuery);
        $uResult = $dbAdapter->query($uQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        
        //$aQuery = $sql->select()->from(array('spirt5' => 'spi_rt_5_facilities'))
        $aQuery = $sql->select()->from(array('spirt3' => 'spi_rt_3_facilities'))
                                ->where('spirt3.status != "deleted"');
        $aQueryStr = $sql->getSqlStringForSqlObject($aQuery);
        $aResult = $dbAdapter->query($aQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        
        return array('uniqueName'=>$uResult,'allName'=>$aResult);
    }
    
    public function fetchAllTestingPointsBasedOnFacility($parameters,$acl){
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
	
        $aColumns = array('facilityid','facilityname','testingpointname',"DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')",'AUDIT_SCORE_PERCENTAGE');
        $orderColumns = array('facilityid','facilityname','testingpointname','assesmentofaudit','AUDIT_SCORE_PERCENTAGE');

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
        $sQuery = $sql->select()->from('spi_form_v_3')->columns(array('id','facilityid','facilityname','testingpointname','testingpointtype','assesmentofaudit','AUDIT_SCORE_PERCENTAGE'));
        
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
		 $row[] = round($aRow['AUDIT_SCORE_PERCENTAGE'],2);
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
            $query = $sql->select()->from(array('spiv5'=>'spi_form_v_5'))
                                   ->columns(array('id','assesmentofaudit'))
                                   ->where(array('spiv5.facilityname'=>$params['facilityName'],'spiv5.status'=>'approved'));
            $queryStr = $sql->getSqlStringForSqlObject($query);
            $audits = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
            
            //$aQuery = $sql->select()->from(array('spiv5_fclt' => 'spi_rt_5_facilities'))
            $aQuery = $sql->select()->from(array('spiv3_fclt' => 'spi_rt_3_facilities'))
                                    ->columns(array('facility_name','email'))
                                    ->where(array('spiv3_fclt.facility_name'=>$params['facilityName']));
            $aQueryStr = $sql->getSqlStringForSqlObject($aQuery);
            $aResult = $dbAdapter->query($aQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
        }
      return array('audits'=>$audits,'facilityProfile'=>$aResult);
    }
    
    public function getSpiV3PendingCount(){
        $logincontainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                                ->where('spiv3.status = "pending"');
        if(isset($logincontainer->token) && count($logincontainer->token) > 0){
            $sQuery = $sQuery->where('spiv3.token IN ("' . implode('", "', $logincontainer->token) . '")');
        }
        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        return count($rResult);
    }
    
    public function deleteAuditRowData($params){
        $result = 0;
        if (trim($params['deleteId']) != "" && trim($params['deleteId'])!= '') {
            $data = array('status' => 'deleted');
            $result = $this->update($data, array('id' => $params['deleteId']));
        }
      return $result;
    }
   
    
    public function fetchAllApprovedSubmissionsDetailsBasedOnAuditDate($parameters,$acl){
        $logincontainer = new Container('credo');
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
	
        $aColumns = array('facilityname','auditroundno','assesmentofaudit' , 'testingpointname' ,'testingpointtype','level','affiliation','AUDIT_SCORE_PERCENTAGE','status');
        $orderColumns = array('facilityname','auditroundno','assesmentofaudit', 'testingpointname' ,'testingpointtype','level','affiliation','AUDIT_SCORE_PERCENTAGE','status');

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
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                                ->where('spiv3.status != "deleted"');
       
        $tQuery =  $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                                 ->where('spiv3.status != "deleted"');
                                 
        if(isset($parameters['assesmentOfAuditDate']) && $parameters['assesmentOfAuditDate']!=''){
            $sQuery = $sQuery->where("spiv3.assesmentofaudit='".$this->dateFormat($parameters['assesmentOfAuditDate'])."'");
            $tQuery = $tQuery->where("spiv3.assesmentofaudit='".$this->dateFormat($parameters['assesmentOfAuditDate'])."'");
        }
        if(isset($logincontainer->token) && count($logincontainer->token) > 0){
            $sQuery = $sQuery->where('spiv3.token IN ("' . implode('", "', $logincontainer->token) . '")');
            $tQuery = $tQuery->where('spiv3.token IN ("' . implode('", "', $logincontainer->token) . '")');
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
        
        $tQueryStr = $sql->getSqlStringForSqlObject($tQuery); // Get the string of the Sql, instead of the Select-instance
        $tResult = $dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);
        $iTotal = count($tResult);
        $output = array(
               "sEcho" => intval($parameters['sEcho']),
               "iTotalRecords" => $iTotal,
               "iTotalDisplayRecords" => $iFilteredTotal,
               "aaData" => array()
        );
        
        $role = $logincontainer->roleCode;
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
         $downloadPdf="";
         
         
         if(isset($aRow['level_other']) && $aRow['level_other'] != ""){
             $level = " - " .$aRow['level_other'];
         }else{
             $level = '';
         }
         
         $row[] = $aRow['facilityname'];
         $row[] = $aRow['auditroundno'];
         $row[] = $commonService->humanDateFormat($aRow['assesmentofaudit']);
         $row[] = (isset($aRow['testingpointname']) && $aRow['testingpointname'] != "" ? $aRow['testingpointname'] : $aRow['testingpointtype']);
         $row[] = $aRow['testingpointtype'];
         $row[] = $aRow['level'].$level;
         $row[] = $aRow['affiliation'];
         $row[] = round($aRow['AUDIT_SCORE_PERCENTAGE'],2);
         $row[] = ucwords($aRow['status']);
         //$print = '<a href="/spi-v3/print/' . $aRow['id'] . '" target="_blank" style="white-space:nowrap;"><i class="fa fa-print"></i> Print</a>';
       
         
        if($downloadPdfAction){
            $downloadPdf = '<br><a href="javascript:void(0);" onclick="downloadPdf('.$aRow['id'].')" style="white-space:nowrap;"><i class="fa fa-download"></i> PDF</a>';
        }
         //$pending = '<br><a href="/spi-v3/edit/' . $aRow['id'] . '" style="white-space:nowrap;"><i class="fa fa-pencil"></i> Edit</a>';
         $row[] = $downloadPdf;
         $output['aaData'][] = $row;
        }
        return $output;
    }
    
    public function fetchSpiV3FormUniqueTokens(){
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $tokenQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                                    ->columns(array(new Expression('DISTINCT(token) as token')))
                                    ->group('token')
                                    ->order("token ASC");
        $tokenQueryStr = $sql->getSqlStringForSqlObject($tokenQuery);
        return $dbAdapter->query($tokenQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    }
    
    public function fetchViewDataDetails($parameters){
        $logincontainer = new Container('credo');
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
	if($parameters['source'] == 'hv') {
            $aColumns = array("DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')",'facilityname','avgMonthTesting','NumberofTester');
            $orderColumns = array('assesmentofaudit','facilityname','avgMonthTesting','NumberofTester','AUDIT_SCORE_PERCENTAGE');
        }else if($parameters['source'] == 'la'){
            $aColumns = array("DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')",'facilityname','avgMonthTesting','AUDIT_SCORE_PERCENTAGE');
            $orderColumns = array('assesmentofaudit','facilityname','avgMonthTesting','AUDIT_SCORE_PERCENTAGE');
        }else if($parameters['source'] == 'ad'){
            $aColumns = array("DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')","DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')");
            $orderColumns = array('assesmentofaudit','assesmentofaudit');
        }else if($parameters['source'] == 'apall' || $parameters['source'] == 'apl180' || $parameters['source'] == 'ap'){
            $aColumns = array('facilityid','facilityname','AUDIT_SCORE_PERCENTAGE',"DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')",'testingpointtype','testingpointtype_other','level','affiliation','AUDIT_SCORE_PERCENTAGE','AUDIT_SCORE_PERCENTAGE');
            $orderColumns = array('facilityid','facilityname','AUDIT_SCORE_PERCENTAGE','assesmentofaudit','testingpointtype','level','affiliation','AUDIT_SCORE_PERCENTAGE','AUDIT_SCORE_PERCENTAGE');
        }else if($parameters['source'] == 'apspi'){
            $aColumns = array("DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')",'PERSONAL_SCORE','PHYSICAL_SCORE','SAFETY_SCORE','PRETEST_SCORE','TEST_SCORE','POST_SCORE','EQA_SCORE');
            $orderColumns = array('assesmentofaudit','PERSONAL_SCORE','PHYSICAL_SCORE','SAFETY_SCORE','PRETEST_SCORE','TEST_SCORE','POST_SCORE','EQA_SCORE');
        }

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
        if (isset($parameters['drange']) && ($parameters['drange'] != "")) {
            $dateField = explode(" ", $parameters['drange']);
            if (isset($dateField[0]) && trim($dateField[0]) != "") {
                $start_date = $this->dateFormat($dateField[0]);                
            }
            if (isset($dateField[2]) && trim($dateField[2]) != "") {
                $end_date = $this->dateFormat($dateField[2]);
            }
        }
    
        if($parameters['source'] == 'ad'){
            //For Audit Dates
            $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_5'))
                                    ->columns(array(new Expression('DISTINCT(assesmentofaudit) as assesmentofaudit'),'totalDataPoints' => new \Laminas\Db\Sql\Expression("COUNT(*)")))
                                    ->where(array('spiv3.status'=>'approved'))
                                    ->group('spiv3.assesmentofaudit');
            $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_5'))
                                    ->columns(array(new Expression('DISTINCT(assesmentofaudit) as assesmentofaudit'),'totalDataPoints' => new \Laminas\Db\Sql\Expression("COUNT(*)")))
                                    ->where(array('spiv3.status'=>'approved'))
                                    ->group('spiv3.assesmentofaudit');
        }else if($parameters['source'] == 'apall' || $parameters['source'] == 'apl180' || $parameters['source'] == 'ap'){
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
            //For Audit Performance Row
            $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_5'))
                                    ->columns(array('facilityid','facilityname','auditroundno','assesmentofaudit','testingpointtype','testingpointtype_other','level','affiliation','AUDIT_SCORE_PERCENTAGE'))
                                    ->where(array('spiv3.status'=>'approved'));
            
            $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_5'))
                                    ->columns(array('facilityid','facilityname','auditroundno','assesmentofaudit','testingpointtype','testingpointtype_other','level','affiliation','AUDIT_SCORE_PERCENTAGE'))
                                    ->where(array('spiv3.status'=>'approved'));
                                    
            if($parameters['source'] == 'apl180'){
                $sQuery = $sQuery->where("(`assesmentofaudit` BETWEEN CURDATE() - INTERVAL 180 DAY AND CURDATE())");
                $tQuery = $tQuery->where("(`assesmentofaudit` BETWEEN CURDATE() - INTERVAL 180 DAY AND CURDATE())");
            }
            if(isset($parameters['testPoint']) && trim($parameters['testPoint'])!=''){
               $sQuery = $sQuery->where("spiv3.testingpointtype='".$parameters['testPoint']."'");
               $tQuery = $tQuery->where("spiv3.testingpointtype='".$parameters['testPoint']."'");
               if(isset($parameters['testPointName']) && trim($parameters['testPointName'])!= ''){
                    if(trim($parameters['testPoint'])!= 'other'){
                       $sQuery = $sQuery->where("spiv3.testingpointname='".$parameters['testPointName']."'");
                       $tQuery = $tQuery->where("spiv3.testingpointname='".$parameters['testPointName']."'");
                    }else{
                       $sQuery = $sQuery->where("spiv3.testingpointtype_other='".$parameters['testPointName']."'");
                       $tQuery = $tQuery->where("spiv3.testingpointtype_other='".$parameters['testPointName']."'");
                    }
               }
            }
            if(isset($parameters['auditRndNo']) && $parameters['auditRndNo']!=''){
               $sQuery = $sQuery->where("spiv3.auditroundno='".$parameters['auditRndNo']."'");
               $tQuery = $tQuery->where("spiv3.auditroundno='".$parameters['auditRndNo']."'");
            }
            if(isset($parameters['scoreLevel']) && $parameters['scoreLevel']!=''){
              if($parameters['scoreLevel'] == 0){
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE < 40");
                $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE < 40");
              }else if($parameters['scoreLevel'] == 1){
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 59");
                $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 59");
              }else if($parameters['scoreLevel'] == 2){
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 79");
                $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 79");
              }else if($parameters['scoreLevel'] == 3){
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 89");
                $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 89");
              }else if($parameters['scoreLevel'] == 4){
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 90");
                $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 90");
              }
            }
            if(isset($parameters['level']) && $parameters['level']!=''){
               $sQuery = $sQuery->where("spiv3.level='".$parameters['level']."'");
               $tQuery = $tQuery->where("spiv3.level='".$parameters['level']."'");
            }
            if(isset($parameters['province']) && $parameters['province']!=''){
                $provinces = explode(",",$parameters['province']);
                $sQuery = $sQuery->where('spiv3.level_name IN ("' . implode('", "', $provinces) . '")');
                $tQuery = $tQuery->where('spiv3.level_name IN ("' . implode('", "', $provinces) . '")');
            }
            if(isset($parameters['affiliation']) && $parameters['affiliation']!=''){
              $sQuery = $sQuery->where("spiv3.affiliation='".$parameters['affiliation']."'");
              $tQuery = $tQuery->where("spiv3.affiliation='".$parameters['affiliation']."'");
            }
        }else if($parameters['source'] == 'apspi'){
            //For Audit Performance
            $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_5'))
                                    ->columns(array('assesmentofaudit','PERSONAL_SCORE' => new Expression('AVG(PERSONAL_SCORE)'),'PHYSICAL_SCORE' => new Expression('AVG(PHYSICAL_SCORE)'),'SAFETY_SCORE' => new Expression('AVG(SAFETY_SCORE)'),'PRETEST_SCORE' => new Expression('AVG(PRETEST_SCORE)'),'TEST_SCORE' => new Expression('AVG(TEST_SCORE)'),'POST_SCORE' => new Expression('AVG(POST_SCORE)'),'EQA_SCORE' => new Expression('AVG(EQA_SCORE)')))
                                    ->group('spiv3.assesmentofaudit');
            $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_5'))
                                    ->columns(array('assesmentofaudit','PERSONAL_SCORE' => new Expression('AVG(PERSONAL_SCORE)'),'PHYSICAL_SCORE' => new Expression('AVG(PHYSICAL_SCORE)'),'SAFETY_SCORE' => new Expression('AVG(SAFETY_SCORE)'),'PRETEST_SCORE' => new Expression('AVG(PRETEST_SCORE)'),'TEST_SCORE' => new Expression('AVG(TEST_SCORE)'),'POST_SCORE' => new Expression('AVG(POST_SCORE)'),'EQA_SCORE' => new Expression('AVG(EQA_SCORE)')))
                                    ->group('spiv3.assesmentofaudit');
            
            if(isset($parameters['roundno']) && $parameters['roundno']!=''){
              $xplodRoundNo = explode(",",$parameters['roundno']);
              $sQuery = $sQuery->where('spiv3.auditroundno IN ("' . implode('", "', $xplodRoundNo) . '")');
              $tQuery = $tQuery->where('spiv3.auditroundno IN ("' . implode('", "', $xplodRoundNo) . '")');
            }
        }else{
            //For Others
            $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_5'))
                                    ->columns(array('assesmentofaudit','facilityname','testingpointtype','avgMonthTesting','NumberofTester','AUDIT_SCORE_PERCENTAGE'));
       
            $tQuery =  $sql->select()->from(array('spiv3' => 'spi_form_v_5'))
                                     ->columns(array('assesmentofaudit','facilityname','testingpointtype','avgMonthTesting','NumberofTester','AUDIT_SCORE_PERCENTAGE'));
        }
        
        if(isset($logincontainer->token) && count($logincontainer->token) > 0){
            $sQuery = $sQuery->where('spiv3.token IN ("' . implode('", "', $logincontainer->token) . '")');
            $tQuery = $tQuery->where('spiv3.token IN ("' . implode('", "', $logincontainer->token) . '")');
        }
        
        if (trim($start_date) != "" && trim($end_date) != "") {
            $sQuery = $sQuery->where(array("spiv3.assesmentofaudit >='" . $start_date ."'", "spiv3.assesmentofaudit <='" . $end_date."'"));
            $tQuery = $tQuery->where(array("spiv3.assesmentofaudit >='" . $start_date ."'", "spiv3.assesmentofaudit <='" . $end_date."'"));
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
        // echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);
        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->getSqlStringForSqlObject($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        
        $tQueryStr = $sql->getSqlStringForSqlObject($tQuery); // Get the string of the Sql, instead of the Select-instance
        $tResult = $dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);
        $iTotal = count($tResult);
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
        $commonService = new \Application\Service\CommonService();
        //$personalScoreArray = array();
        //$physicalScoreArray = array();
        //$safetyScoreArray = array();
        //$preTestScoreArray = array();
        //$testScoreArray = array();
        //$postTestScoreArray = array();
        //$eqaScoreArray = array();
        //$personalScore = 0;
        //$physicalScore = 0;
        //$safetyScore = 0;
        //$preTestScore = 0;
        //$testScore = 0;
        //$postTestScore = 0;
        //$eqaScore = 0;
        $auditScore = 0;
        $levelZero = array();
        $levelOne = array();
        $levelTwo = array();
        $levelThree = array();
        $levelFour = array();
        foreach ($rResult as $aRow) {
          $row = array();
            if($parameters['source'] == 'hv' || $parameters['source'] == 'la' || $parameters['source'] == 'apall' || $parameters['source'] == 'apl180' || $parameters['source'] == 'ap'){
                $auditScore+=$aRow['AUDIT_SCORE_PERCENTAGE'];
                if(isset($aRow['AUDIT_SCORE_PERCENTAGE']) && $aRow['AUDIT_SCORE_PERCENTAGE'] < 40){
                  $level = 0;
                  $levelZero[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
                }else if(isset($aRow['AUDIT_SCORE_PERCENTAGE']) && $aRow['AUDIT_SCORE_PERCENTAGE'] >= 40 && $aRow['AUDIT_SCORE_PERCENTAGE'] <60){
                  $level = 1;
                  $levelOne[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
                }else if(isset($aRow['AUDIT_SCORE_PERCENTAGE']) && $aRow['AUDIT_SCORE_PERCENTAGE'] >= 60 && $aRow['AUDIT_SCORE_PERCENTAGE'] <80){
                  $level = 2;
                  $levelTwo[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
                }else if(isset($aRow['AUDIT_SCORE_PERCENTAGE']) && $aRow['AUDIT_SCORE_PERCENTAGE'] >= 80 && $aRow['AUDIT_SCORE_PERCENTAGE'] <90){
                  $level = 3;
                  $levelThree[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
                }else if(isset($aRow['AUDIT_SCORE_PERCENTAGE']) && $aRow['AUDIT_SCORE_PERCENTAGE'] >= 90){
                  $level = 4;
                  $levelFour[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
                }
           }
           if($parameters['source'] == 'hv') {   
                $row[] = $commonService->humanDateFormat($aRow['assesmentofaudit']);
                $row[] = ucwords($aRow['facilityname']);
                $row[] = (isset($aRow['testingpointname']) && $aRow['testingpointname'] != "" ? $aRow['testingpointname'] : $aRow['testingpointtype']);
                $row[] = (isset($aRow['avgMonthTesting']) ? $aRow['avgMonthTesting'] : 0);
                $row[] = (isset($aRow['NumberofTester']) ? $aRow['NumberofTester'] : 0);
                $row[] = $level;
          }else if($parameters['source'] == 'la') {
            $row[] = $commonService->humanDateFormat($aRow['assesmentofaudit']);
            $row[] = ucwords($aRow['facilityname']);
            $row[] = (isset($aRow['testingpointname']) && $aRow['testingpointname'] != "" ? $aRow['testingpointname'] : $aRow['testingpointtype']);
            $row[] = round($aRow['AUDIT_SCORE_PERCENTAGE'],2);
            $row[] = (isset($aRow['avgMonthTesting']) ? $aRow['avgMonthTesting'] : 0);
          }else if($parameters['source'] == 'ad') {
            $row[] = $commonService->humanDateFormat($aRow['assesmentofaudit']);
            $row[] = $aRow['totalDataPoints'];
          }else if($parameters['source'] == 'apall' || $parameters['source'] == 'apl180' || $parameters['source'] == 'ap') {
            $row[] = $aRow['facilityid'];
            $row[] = ucwords($aRow['facilityname']);
            $row[] = $aRow['auditroundno'];
            $row[] = $commonService->humanDateFormat($aRow['assesmentofaudit']);
            $row[] = ucwords($aRow['testingpointtype']);
            $row[] = ($aRow['testingpointtype'] == 'other')?ucwords($aRow['testingpointtype_other']):ucwords($aRow['testingpointname']);
            $row[] = ucwords($aRow['level']);
            $row[] = ucwords($aRow['affiliation']);
            $row[] = $level;
            $row[] = round($aRow['AUDIT_SCORE_PERCENTAGE'],2);
          }else if($parameters['source'] == 'apspi') {
            $row[] = $commonService->humanDateFormat($aRow['assesmentofaudit']);
            $row[] = round($aRow['PERSONAL_SCORE'],2);
            $row[] = round($aRow['PHYSICAL_SCORE'],2);
            $row[] = round($aRow['SAFETY_SCORE'],2);
            $row[] = round($aRow['PRETEST_SCORE'],2);
            $row[] = round($aRow['TEST_SCORE'],2);
            $row[] = round($aRow['POST_SCORE'],2);
            $row[] = round($aRow['EQA_SCORE'],2);
            //$personalScoreArray[] = $aRow['PERSONAL_SCORE'];
            //$physicalScoreArray[] = $aRow['PHYSICAL_SCORE'];
            //$safetyScoreArray[] = $aRow['SAFETY_SCORE'];
            //$preTestScoreArray[] = $aRow['PRETEST_SCORE'];
            //$testScoreArray[] = $aRow['TEST_SCORE'];
            //$postTestScoreArray[] = $aRow['POST_SCORE'];
            //$eqaScoreArray[] = $aRow['EQA_SCORE'];
            //$personalScore+=$aRow['PERSONAL_SCORE'];
            //$physicalScore+=$aRow['PHYSICAL_SCORE'];
            //$safetyScore+=$aRow['SAFETY_SCORE'];
            //$preTestScore+=$aRow['PRETEST_SCORE'];
            //$testScore+=$aRow['TEST_SCORE'];
            //$postTestScore+=$aRow['POST_SCORE'];
            //$eqaScore+=$aRow['EQA_SCORE'];
          }
         $output['aaData'][] = $row;
        }
         $output['avgAuditScore'] = (count($rResult) > 0) ? round($auditScore/count($rResult),2) : 0;
         $output['levelZeroCount'] = count($levelZero);
         $output['levelOneCount'] = count($levelOne);
         $output['levelTwoCount'] = count($levelTwo);
         $output['levelThreeCount'] = count($levelThree);
         $output['levelFourCount'] = count($levelFour);
        return $output;
    }
    
    public function fetchAllTestingPointTypeV5(){
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $query = $sql->select()->from(array('spiv5' => 'spi_form_v_5'))
                                    ->columns(array(new Expression('DISTINCT(testingpointtype) as testingPointType')))
                                    ->group('testingpointtype')
                                    ->order("testingpointtype ASC");
        $queryStr = $sql->getSqlStringForSqlObject($query);
        
        return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    }
    
    public function fetchTestingPointTypeNamesByTypeV5($params){
        $typeResult = array();
        if(isset($params['testingPointType']) && trim($params['testingPointType'])!= ''){
            if($params['testingPointType'] == 'other'){
                $column = 'DISTINCT(testingpointtype_other) as testingpointName';
            }
            else{
               $column = 'DISTINCT(testingpointtype) as testingpointName'; 
            }
            $dbAdapter = $this->adapter;
            $sql = new Sql($dbAdapter);
            $query = $sql->select()->from(array('spiv5' => 'spi_form_v_5'))
                                         ->columns(array(new Expression($column)));
            if(isset($params['testingPointType']) && trim($params['testingPointType'])!= '' && $params['testingPointType'] == 'other'){
                $query = $query->where('testingpointtype_other IS NOT NULL');
            }else{
                $query = $query->where(array('testingpointtype'=>$params['testingPointType']));
            }
            $queryStr = $sql->getSqlStringForSqlObject($query);
            //print_r($queryStr);
            $typeResult = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        }
      return $typeResult;
    }
    
    public function fetchSpiV5FormUniqueLevelNames(){
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $query = $sql->select()->from(array('spiv3' => 'spi_form_v_5'))->columns(array('facilityname'))
                               ->join(array('f'=>'spi_rt_3_facilities'),'f.id=spiv3.facility');
                            //    ->group("province")
                            //    ->order("province ASC");
        $queryStr = $sql->getSqlStringForSqlObject($query);
        //$query = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
        //                       ->columns(array(new Expression('DISTINCT(level_name) as level_name')))
        //                       ->order("level_name ASC");
        //$queryStr = $sql->getSqlStringForSqlObject($query);
        return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    }
    public function fetchDistrictData($params)
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $query = $sql->select()->from(array('f' => 'spi_rt_3_facilities'))
                                ->group('district');
        if (is_array($params['province'])){
            $query = $query->where('f.province IN ("' . implode('", "', $params['province']) . '") AND f.district!=""');
        }else{
            $query = $query->where('f.province="'.$params['province'].'" AND f.district!=""');
        }
        $queryStr = $sql->getSqlStringForSqlObject($query);
        return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    }
    
    public function addValidateSpiv3Data($params)
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $vId = explode(",",$params['validateId']);
        $newQuery =  $sql->select()->from(array('spiv3' => 'spi_form_v_3_temp'))
                            ->where('spiv3.id IN ("' . implode('", "', $vId) . '")');
        $newQueryStr = $sql->getSqlStringForSqlObject($newQuery); // Get the string of the Sql, instead of the Select-instance
        $totalResult = $dbAdapter->query($newQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        if($totalResult){
            foreach($totalResult as $newResult){
                $insert = $sql->insert('spi_form_v_3');
                if(isset($newResult['testingpointname']) && trim($newResult['testingpointname'])==""){
                    $newResult['testingpointname']=$newResult['testingpointtype'];
                }
                $newResult['instanceID'] = isset($newResult['instanceID']) ? $newResult['instanceID'] :"";
                $newResult['instanceName'] = isset($newResult['instanceName']) ? $newResult['instanceName'] :"";
        
                $par = array(
                        'token' => $params['token'],
                        'content' => $newResult['content'],
                        'formId' => $newResult['formId'],
                        'formVersion' => $newResult['formVersion'],
                        'meta-instance-id' => $newResult['instanceID'],
                        'meta-model-version' => $newResult['meta-model-version'],
                        'meta-ui-version' => $newResult['meta-ui-version'],
                        'meta-submission-date' => $newResult['meta-submission-date'],
                        'meta-is-complete' => $newResult['meta-is-complete'],
                        'meta-date-marked-as-complete' => $newResult['meta-date-marked-as-complete'],
                        'start' => $newResult['start'],
                        'end' => $newResult['end'],
                        'today' => $newResult['today'],
                        'deviceid' => $newResult['deviceid'],
                        'subscriberid' => $newResult['subscriberid'],
                        'text_image' => $newResult['text_image'],
                        'info1' => $newResult['info1'],
                        'info2' => $newResult['info2'],
                        'assesmentofaudit' => $newResult['assesmentofaudit'],
                        'auditroundno' => $newResult['auditroundno'],
                        'facilityname' => $newResult['facilityname'],
                        'facilityid' => $newResult['facilityid'],
                        'testingpointname' => $newResult['testingpointname'],
                        'testingpointtype' => $newResult['testingpointtype'],
                        'testingpointtype_other' => $newResult['testingpointtype_other'],
                        'locationaddress' => $newResult['locationaddress'],
                        'level' => $newResult['level'],
                        'level_other' => $newResult['level_other'],
                        'level_name' => $newResult['level_name'],
                        'affiliation' => $newResult['affiliation'],
                        'affiliation_other' => $newResult['affiliation_other'],
                        'NumberofTester' => (isset($newResult['NumberofTester']) && $newResult['NumberofTester'] > 0 ? $newResult['NumberofTester'] : 0),
                        'avgMonthTesting' => (isset($newResult['avgMonthTesting']) && $newResult['avgMonthTesting'] > 0 ? $newResult['avgMonthTesting'] : 0),
                        'name_auditor_lead' => $newResult['name_auditor_lead'],
                        'name_auditor2' => $newResult['name_auditor2'],
                        'info4' => $newResult['info4'],
                        'INSTANCE' => $newResult['INSTANCE'],
                        'PERSONAL_Q_1_1' => $newResult['PERSONAL_Q_1_1'],
                        'PERSONAL_C_1_1' => $newResult['PERSONAL_C_1_1'],
                        'PERSONAL_Q_1_2' => $newResult['PERSONAL_Q_1_2'],
                        'PERSONAL_C_1_2' => $newResult['PERSONAL_C_1_2'],
                        'PERSONAL_Q_1_3' => $newResult['PERSONAL_Q_1_3'],
                        'PERSONAL_C_1_3' => $newResult['PERSONAL_C_1_3'],
                        'PERSONAL_Q_1_4' => $newResult['PERSONAL_Q_1_4'],
                        'PERSONAL_C_1_4' => $newResult['PERSONAL_C_1_4'],
                        'PERSONAL_Q_1_5' => $newResult['PERSONAL_Q_1_5'],
                        'PERSONAL_C_1_5' => $newResult['PERSONAL_C_1_5'],
                        'PERSONAL_Q_1_6' => $newResult['PERSONAL_Q_1_6'],
                        'PERSONAL_C_1_6' => $newResult['PERSONAL_C_1_6'],
                        'PERSONAL_Q_1_7' => $newResult['PERSONAL_Q_1_7'],
                        'PERSONAL_C_1_7' => $newResult['PERSONAL_C_1_7'],
                        'PERSONAL_Q_1_8' => $newResult['PERSONAL_Q_1_8'],
                        'PERSONAL_C_1_8' => $newResult['PERSONAL_C_1_8'],
                        'PERSONAL_Q_1_9' => $newResult['PERSONAL_Q_1_9'],
                        'PERSONAL_C_1_9' => $newResult['PERSONAL_C_1_9'],
                        'PERSONAL_Q_1_10' => $newResult['PERSONAL_Q_1_10'],
                        'PERSONAL_C_1_10' => $newResult['PERSONAL_C_1_10'],
                        'PERSONAL_SCORE' => $newResult['PERSONAL_SCORE'],
                        'PERSONAL_Display' => $newResult['PERSONAL_Display'],
                        'PERSONALPHOTO' => $newResult['PERSONALPHOTO'],
                        'PHYSICAL_Q_2_1' => $newResult['PHYSICAL_Q_2_1'],
                        'PHYSICAL_C_2_1' => $newResult['PHYSICAL_C_2_1'],
                        'PHYSICAL_Q_2_2' => $newResult['PHYSICAL_Q_2_2'],
                        'PHYSICAL_C_2_2' => $newResult['PHYSICAL_C_2_2'],
                        'PHYSICAL_Q_2_3' => $newResult['PHYSICAL_Q_2_3'],
                        'PHYSICAL_C_2_3' => $newResult['PHYSICAL_C_2_3'],
                        'PHYSICAL_Q_2_4' => $newResult['PHYSICAL_Q_2_4'],
                        'PHYSICAL_C_2_4' => $newResult['PHYSICAL_C_2_4'],
                        'PHYSICAL_Q_2_5' => $newResult['PHYSICAL_Q_2_5'],
                        'PHYSICAL_C_2_5' => $newResult['PHYSICAL_C_2_5'],
                        'PHYSICAL_SCORE' => $newResult['PHYSICAL_SCORE'],
                        'PHYSICAL_Display' => $newResult['PHYSICAL_Display'],
                        'PHYSICALPHOTO' => $newResult['PHYSICALPHOTO'],
                        'SAFETY_Q_3_1' => $newResult['SAFETY_Q_3_1'],
                        'SAFETY_C_3_1' => $newResult['SAFETY_C_3_1'],
                        'SAFETY_Q_3_2' => $newResult['SAFETY_Q_3_2'],
                        'SAFETY_C_3_2' => $newResult['SAFETY_C_3_2'],
                        'SAFETY_Q_3_3' => $newResult['SAFETY_Q_3_3'],
                        'SAFETY_C_3_3' => $newResult['SAFETY_C_3_3'],
                        'SAFETY_Q_3_4' => $newResult['SAFETY_Q_3_4'],
                        'SAFETY_C_3_4' => $newResult['SAFETY_C_3_4'],
                        'SAFETY_Q_3_5' => $newResult['SAFETY_Q_3_5'],
                        'SAFETY_C_3_5' => $newResult['SAFETY_C_3_5'],
                        'SAFETY_Q_3_6' => $newResult['SAFETY_Q_3_6'],
                        'SAFETY_C_3_6' => $newResult['SAFETY_C_3_6'],
                        'SAFETY_Q_3_7' => $newResult['SAFETY_Q_3_7'],
                        'SAFETY_C_3_7' => $newResult['SAFETY_C_3_7'],
                        'SAFETY_Q_3_8' => $newResult['SAFETY_Q_3_8'],
                        'SAFETY_C_3_8' => $newResult['SAFETY_C_3_8'],
                        'SAFETY_Q_3_9' => $newResult['SAFETY_Q_3_9'],
                        'SAFETY_C_3_9' => $newResult['SAFETY_C_3_9'],
                        'SAFETY_Q_3_10' => $newResult['SAFETY_Q_3_10'],
                        'SAFETY_C_3_10' => $newResult['SAFETY_C_3_10'],
                        'SAFETY_Q_3_11' => $newResult['SAFETY_Q_3_11'],
                        'SAFETY_C_3_11' => $newResult['SAFETY_C_3_11'],
                        'SAFETY_SCORE' => $newResult['SAFETY_SCORE'],
                        'SAFETY_DISPLAY' => $newResult['SAFETY_DISPLAY'],
                        'SAFETYPHOTO' => $newResult['SAFETYPHOTO'],
                        'PRE_Q_4_1' => $newResult['PRE_Q_4_1'],
                        'PRE_C_4_1' => $newResult['PRE_C_4_1'],
                        'PRE_Q_4_2' => $newResult['PRE_Q_4_2'],
                        'PRE_C_4_2' => $newResult['PRE_C_4_2'],
                        'PRE_Q_4_3' => $newResult['PRE_Q_4_3'],
                        'PRE_C_4_3' => $newResult['PRE_C_4_3'],
                        'PRE_Q_4_4' => $newResult['PRE_Q_4_4'],
                        'PRE_C_4_4' => $newResult['PRE_C_4_4'],
                        'PRE_Q_4_5' => $newResult['PRE_Q_4_5'],
                        'PRE_C_4_5' => $newResult['PRE_C_4_5'],
                        'PRE_Q_4_6' => $newResult['PRE_Q_4_6'],
                        'PRE_C_4_6' => $newResult['PRE_C_4_6'],
                        'PRE_Q_4_7' => $newResult['PRE_Q_4_7'],
                        'PRE_C_4_7' => $newResult['PRE_C_4_7'],
                        'PRE_Q_4_8' => $newResult['PRE_Q_4_8'],
                        'PRE_C_4_8' => $newResult['PRE_C_4_8'],
                        'PRE_Q_4_9' => $newResult['PRE_Q_4_9'],
                        'PRE_C_4_9' => $newResult['PRE_C_4_9'],
                        'PRE_Q_4_10' => $newResult['PRE_Q_4_10'],
                        'PRE_C_4_10' => $newResult['PRE_C_4_10'],
                        'PRE_Q_4_11' => $newResult['PRE_Q_4_11'],
                        'PRE_C_4_11' => $newResult['PRE_C_4_11'],
                        'PRE_Q_4_12' => $newResult['PRE_Q_4_12'],
                        'PRE_C_4_12' => $newResult['PRE_C_4_12'],
                        'PRETEST_SCORE' => $newResult['PRETEST_SCORE'],
                        'PRETEST_Display' => $newResult['PRETEST_Display'],
                        'PRETESTPHOTO' => $newResult['PRETESTPHOTO'],
                        'TEST_Q_5_1' => $newResult['TEST_Q_5_1'],
                        'TEST_C_5_1' => $newResult['TEST_C_5_1'],
                        'TEST_Q_5_2' => $newResult['TEST_Q_5_2'],
                        'TEST_C_5_2' => $newResult['TEST_C_5_2'],
                        'TEST_Q_5_3' => $newResult['TEST_Q_5_3'],
                        'TEST_C_5_3' => $newResult['TEST_C_5_3'],
                        'TEST_Q_5_4' => $newResult['TEST_Q_5_4'],
                        'TEST_C_5_4' => $newResult['TEST_C_5_4'],
                        'TEST_Q_5_5' => $newResult['TEST_Q_5_5'],
                        'TEST_C_5_5' => $newResult['TEST_C_5_5'],
                        'TEST_Q_5_6' => $newResult['TEST_Q_5_6'],
                        'TEST_C_5_6' => $newResult['TEST_C_5_6'],
                        'TEST_Q_5_7' => $newResult['TEST_Q_5_7'],
                        'TEST_C_5_7' => $newResult['TEST_C_5_7'],
                        'TEST_Q_5_8' => $newResult['TEST_Q_5_8'],
                        'TEST_C_5_8' => $newResult['TEST_C_5_8'],
                        'TEST_Q_5_9' => $newResult['TEST_Q_5_9'],
                        'TEST_C_5_9' => $newResult['TEST_C_5_9'],
                        'TEST_SCORE' => $newResult['TEST_SCORE'],
                        'TEST_DISPLAY' => $newResult['TEST_DISPLAY'],
                        'TESTPHOTO' => $newResult['TESTPHOTO'],
                        'POST_Q_6_1' => $newResult['POST_Q_6_1'],
                        'POST_C_6_1' => $newResult['POST_C_6_1'],
                        'POST_Q_6_2' => $newResult['POST_Q_6_2'],
                        'POST_C_6_2' => $newResult['POST_C_6_2'],
                        'POST_Q_6_3' => $newResult['POST_Q_6_3'],
                        'POST_C_6_3' => $newResult['POST_C_6_3'],
                        'POST_Q_6_4' => $newResult['POST_Q_6_4'],
                        'POST_C_6_4' => $newResult['POST_C_6_4'],
                        'POST_Q_6_5' => $newResult['POST_Q_6_5'],
                        'POST_C_6_5' => $newResult['POST_C_6_5'],
                        'POST_Q_6_6' => $newResult['POST_Q_6_6'],
                        'POST_C_6_6' => $newResult['POST_C_6_6'],
                        'POST_Q_6_7' => $newResult['POST_Q_6_7'],
                        'POST_C_6_7' => $newResult['POST_C_6_7'],
                        'POST_Q_6_8' => $newResult['POST_Q_6_8'],
                        'POST_C_6_8' => $newResult['POST_C_6_8'],
                        'POST_Q_6_9' => $newResult['POST_Q_6_9'],
                        'POST_C_6_9' => $newResult['POST_C_6_9'],
                        'POST_SCORE' => $newResult['POST_SCORE'],
                        'POST_DISPLAY' => $newResult['POST_DISPLAY'],
                        'POSTTESTPHOTO' => $newResult['POSTTESTPHOTO'],
                        'EQA_Q_7_1' => $newResult['EQA_Q_7_1'],
                        'EQA_C_7_1' => $newResult['EQA_C_7_1'],
                        'EQA_Q_7_2' => $newResult['EQA_Q_7_2'],
                        'EQA_C_7_2' => $newResult['EQA_C_7_2'],
                        'EQA_Q_7_3' => $newResult['EQA_Q_7_3'],
                        'EQA_C_7_3' => $newResult['EQA_C_7_3'],
                        'EQA_Q_7_4' => $newResult['EQA_Q_7_4'],
                        'EQA_C_7_4' => $newResult['EQA_C_7_4'],
                        'EQA_Q_7_5' => $newResult['EQA_Q_7_5'],
                        'EQA_C_7_5' => $newResult['EQA_C_7_5'],
                        'EQA_Q_7_6' => $newResult['EQA_Q_7_6'],
                        'EQA_C_7_6' => $newResult['EQA_C_7_6'],
                        'EQA_Q_7_7' => $newResult['EQA_Q_7_7'],
                        'EQA_C_7_7' => $newResult['EQA_C_7_7'],
                        'EQA_Q_7_8' => $newResult['EQA_Q_7_8'],
                        'EQA_C_7_8' => $newResult['EQA_C_7_8'],
                        'sampleretesting' => $newResult['sampleretesting'],
                        'EQA_Q_7_9' => $newResult['EQA_Q_7_9'],
                        'EQA_C_7_9' => $newResult['EQA_C_7_9'],
                        'EQA_Q_7_10' => $newResult['EQA_Q_7_10'],
                        'EQA_C_7_10' => $newResult['EQA_C_7_10'],
                        'EQA_Q_7_11' => $newResult['EQA_Q_7_11'],
                        'EQA_C_7_11' => $newResult['EQA_C_7_11'],
                        'EQA_Q_7_12' => $newResult['EQA_Q_7_12'],
                        'EQA_C_7_12' => $newResult['EQA_C_7_12'],
                        'EQA_Q_7_13' => $newResult['EQA_Q_7_13'],
                        'EQA_C_7_13' => $newResult['EQA_C_7_13'],
                        'EQA_Q_7_14' => $newResult['EQA_Q_7_14'],
                        'EQA_C_7_14' => $newResult['EQA_C_7_14'],
                        'EQA_MAX_SCORE' => $newResult['EQA_MAX_SCORE'],
                        'EQA_REQ' => $newResult['EQA_REQ'],
                        'EQA_OPT' => $newResult['EQA_OPT'],
                        'EQA_SCORE' => $newResult['EQA_SCORE'],
                        'EQA_DISPLAY' => $newResult['EQA_DISPLAY'],
                        'EQAPHOTO' => $newResult['EQAPHOTO'],
                        'FINAL_AUDIT_SCORE' => $newResult['FINAL_AUDIT_SCORE'],
                        'MAX_AUDIT_SCORE' => $newResult['MAX_AUDIT_SCORE'],
                        'AUDIT_SCORE_PERCENTAGE' => $newResult['AUDIT_SCORE_PERCENTAGE'],
                        'staffaudited' => $newResult['staffaudited'],
                        'durationaudit' => $newResult['durationaudit'],
                        'personincharge' => $newResult['personincharge'],
                        'endofsurvey' => $newResult['endofsurvey'],
                        'info5' => $newResult['info5'],
                        'info6' => $newResult['info6'],
                        'info10' => $newResult['info10'],
                        'info11' => $newResult['info11'],
                        'summarypage' => $newResult['summarypage'],
                        'SUMMARY_NOT_AVL' => $newResult['SUMMARY_NOT_AVL'],
                        'info12' => $newResult['info12'],
                        'info17' => $newResult['info17'],
                        'info21' => $newResult['info21'],
                        'info22' => $newResult['info22'],
                        'info23' => $newResult['info23'],
                        'info24' => $newResult['info24'],
                        'info25' => $newResult['info25'],
                        'info26' => $newResult['info26'],
                        'info27' => $newResult['info27'],
                        'correctiveaction' => $newResult['correctiveaction'],
                        'sitephoto' => $newResult['sitephoto'],
                        'Latitude' => $newResult['Latitude'],
                        'Longitude' => $newResult['Longitude'],
                        'Altitude' => $newResult['Altitude'],
                        'Accuracy' => $newResult['Accuracy'],
                        'auditorSignature' => $newResult['auditorSignature'],
                        'instanceID' => $newResult['instanceID'],
                        'instanceName' => $newResult['instanceName'],
                        'status'=>$newResult['status']
                    );
                    $dbAdapter = $this->adapter;
                    $insert->values($par);
                    $selectString = $sql->getSqlStringForSqlObject($insert);
                    $results = $dbAdapter->query($selectString, $dbAdapter::QUERY_MODE_EXECUTE);
                    if($results->getGeneratedValue()>0){
                        $spiv3Temp = new \Application\Model\SpiFormVer3TempTable($this->adapter);
                        $spiv3Temp->delete(array('id'=>$newResult['id']));
                    }
            }
        }
    }
    
    public function updateSpiv5FacilityInfo($id,$params){
        if($id > 0){
	    $data = array(
		'facilityid'=>$params['facilityId'],
		'facilityname'=>$params['facilityName']
	    );
	  $this->update($data,array('facility'=>$id));
	}
      return $id;
    }
}

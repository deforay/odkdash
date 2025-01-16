<?php

namespace Application\Model;

use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Session\Container;
use Laminas\Db\Adapter\Adapter;
use Application\Model\GlobalTable;
use Application\Model\EventLogTable;
use Application\Service\CommonService;
use Application\Model\SpiRtFacilitiesTable;
use Laminas\Db\TableGateway\AbstractTableGateway;

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
class SpiFormVer6Table extends AbstractTableGateway
{

    protected $table = 'spi_form_v_6';
    protected $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function getAllLabels()
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_v6_form_labels'));
        $sQueryStr = $sql->buildSqlString($sQuery);
        //echo $sQueryStr;//die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        $response = [];
        foreach ($rResult as $row) {
            $response[$row['field']] = array($row['short_label'], $row['label']);
        }
        return $response;
    }

    public function saveData($params)
    {

        if ($params == null || $params == "" || (is_array($params) && count($params) == 0)) {
            exit;
        }

        $dbAdapter = $this->adapter;

        CommonService::saveFormDump($dbAdapter, $params);

        //get global values
        $globalDB = new GlobalTable($dbAdapter);
        $globalValue = $globalDB->getGlobalValue('approve_status');
        $approveStatus = $globalValue == 'yes' ? 'approved' : 'pending';

        //error_log(json_encode($params, true));

        //error_log($params['data']);

        foreach ($params['data'] as $datar) {
            $par = [];
            $data = [];
            foreach ($datar as $key => $val) {
                $key = preg_replace('/[\*]+/', '', $key);
                $key = str_replace("lab_geopoint:", "", $key);
                if (is_array($val)) {
                    $val = json_encode($val);
                }
                $data[$key] = $val;
            }

            try {

                $sql = new Sql($this->adapter);

                $insert = $sql->insert('spi_form_v_6');


                $data['instanceID'] ??= "";
                $data['instanceName'] ??= "";

                $par = [
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
                    'district' => $data['district'],
                    'testingpointtype' => $data['testingpointtype'],
                    'testingpointtype_other' => $data['testingpointtype_other'] ?? null,
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
                    'AUDIT_SCORE_PERCENTAGE_ROUNDED' => $data['AUDIT_SCORE_PERCENTAGE_ROUNDED'] ?? $data['AUDIT_SCORE_PERCANTAGE_ROUNDED'] ?? null,
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

                    'DO_SURVEILLANCE' => $data['DO_SURVEILLANCE'],
                    'S0_Q_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY' => $data['S0_Q_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY'],
                    'S0_C_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY' => $data['S0_C_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY'],
                    'S0_Q_2_COUNSELORS_FOLLOWING_PROTOCOL' => $data['S0_Q_2_COUNSELORS_FOLLOWING_PROTOCOL'],
                    'S0_C_2_COUNSELORS_FOLLOWING_PROTOCOL' => $data['S0_C_2_COUNSELORS_FOLLOWING_PROTOCOL'],
                    'S0_Q_3_TESTS_RECORDED_RECENCY' => $data['S0_Q_3_TESTS_RECORDED_RECENCY'],
                    'S0_C_3_TESTS_RECORDED_RECENCY' => $data['S0_C_3_TESTS_RECORDED_RECENCY'],
                    'S0_Q_4_PROCESS_DOCUMENTED' => $data['S0_Q_4_PROCESS_DOCUMENTED'],
                    'S0_C_4_PROCESS_DOCUMENTED' => $data['S0_C_4_PROCESS_DOCUMENTED'],
                    'S0_Q_5_RESULTS_RETURNED_IN_TWO_WEEKS' => $data['S0_Q_5_RESULTS_RETURNED_IN_TWO_WEEKS'],
                    'S0_C_5_RESULTS_RETURNED_IN_TWO_WEEKS' => $data['S0_C_5_RESULTS_RETURNED_IN_TWO_WEEKS'],
                    'S0_Q_6_PROTOCOL_VIOLATION_DOCUMENTED' => $data['S0_Q_6_PROTOCOL_VIOLATION_DOCUMENTED'],
                    'S0_C_6_PROTOCOL_VIOLATION_DOCUMENTED' => $data['S0_C_6_PROTOCOL_VIOLATION_DOCUMENTED'],
                    'S0_Q_7_DOCUMENTING_PROTOCOL_ERRORS' => $data['S0_Q_7_DOCUMENTING_PROTOCOL_ERRORS'],
                    'S0_C_7_DOCUMENTING_PROTOCOL_ERRORS' => $data['S0_C_7_DOCUMENTING_PROTOCOL_ERRORS'],
                    'D0_N_1_DIAGNOSED_HIV_ABOVE_15' => $data['D0_N_1_DIAGNOSED_HIV_ABOVE_15'],
                    'D0_D_1_DIAGNOSED_HIV_ABOVE_15' => $data['D0_D_1_DIAGNOSED_HIV_ABOVE_15'],
                    'D0_S_1_DIAGNOSED_HIV_ABOVE_15' => $data['D0_S_1_DIAGNOSED_HIV_ABOVE_15'],
                    'D0_N_2_CANDIDATE_SCREENED_FOR_PARTICIPATION' => $data['D0_N_2_CANDIDATE_SCREENED_FOR_PARTICIPATION'],
                    'D0_D_2_CANDIDATE_SCREENED_FOR_PARTICIPATION' => $data['D0_D_2_CANDIDATE_SCREENED_FOR_PARTICIPATION'],
                    'D0_S_2_CANDIDATE_SCREENED_FOR_PARTICIPATION' => $data['D0_S_2_CANDIDATE_SCREENED_FOR_PARTICIPATION'],
                    'D0_N_3_ELIGIBLE_DURING_REVIEW_PERIOD' => $data['D0_N_3_ELIGIBLE_DURING_REVIEW_PERIOD'],
                    'D0_D_3_ELIGIBLE_DURING_REVIEW_PERIOD' => $data['D0_D_3_ELIGIBLE_DURING_REVIEW_PERIOD'],
                    'D0_S_3_ELIGIBLE_DURING_REVIEW_PERIOD' => $data['D0_S_3_ELIGIBLE_DURING_REVIEW_PERIOD'],
                    'D0_N_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD' => $data['D0_N_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD'],
                    'D0_D_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD' => $data['D0_D_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD'],
                    'D0_S_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD' => $data['D0_S_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD'],
                    'D0_N_5_DOCUMENTED_AND_REFUSED' => $data['D0_N_5_DOCUMENTED_AND_REFUSED'],
                    'D0_D_5_DOCUMENTED_AND_REFUSED' => $data['D0_D_5_DOCUMENTED_AND_REFUSED'],
                    'D0_S_5_DOCUMENTED_AND_REFUSED' => $data['D0_S_5_DOCUMENTED_AND_REFUSED'],
                    'D0_N_6_PARTICIAPANTS_ENROLLED_IN_RTRI' => $data['D0_N_6_PARTICIAPANTS_ENROLLED_IN_RTRI'],
                    'D0_D_6_PARTICIAPANTS_ENROLLED_IN_RTRI' => $data['D0_D_6_PARTICIAPANTS_ENROLLED_IN_RTRI'],
                    'D0_S_6_PARTICIAPANTS_ENROLLED_IN_RTRI' => $data['D0_S_6_PARTICIAPANTS_ENROLLED_IN_RTRI'],
                    'D0_N_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI' => $data['D0_N_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI'],
                    'D0_D_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI' => $data['D0_D_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI'],
                    'D0_S_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI' => $data['D0_S_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI'],
                    'D0_N_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI' => $data['D0_N_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI'],
                    'D0_D_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI' => $data['D0_D_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI'],
                    'D0_S_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI' => $data['D0_S_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI'],
                    'status' => $approveStatus,
                ];
                $dbAdapter = $this->adapter;
                $insert->values($par);
                $selectString = $sql->buildSqlString($insert);
                /** @var \Laminas\Db\Adapter\Driver\ResultInterface $results */
                $results = $dbAdapter->query($selectString, $dbAdapter::QUERY_MODE_EXECUTE);

                if ($approveStatus == 'approved') {
                    //$facilityDb = new SpiRt5FacilitiesTable($dbAdapter);
                    $facilityDb = new SpiRtFacilitiesTable($dbAdapter);
                    $facilityResult = $facilityDb->addFacilityBasedOnForm($results->getGeneratedValue(), 5);
                }
            } catch (\Exception $e) {
                error_log($e->getMessage());
                error_log($e->getTraceAsString());
            }
        }
    }

    public function saveOdkCentralData($params, $correctiveActions, $projectId, $formId)
    {
        if ($params == null || $params == "" || empty($params)) {
            exit;
        }

        $dbAdapter = $this->adapter;

        CommonService::saveFormDump($dbAdapter, $params);

        //get global values
        $globalDB = new GlobalTable($dbAdapter);
        $globalValue = $globalDB->getGlobalValue('approve_status');
        $approveStatus = $globalValue == 'yes' ? 'approved' : 'pending';

        //error_log(json_encode($params,true));

        foreach ($params as $submissionData) {

            if (empty($submissionData)) {
                continue;
            }

            $par = $data = [];

            $submissionData['SPIRRT'] = $submissionData['SPIRRT'] ?? $submissionData['SPIRT'];
            $submissionData['EQA'] = $submissionData['EQA'] ?? $submissionData['EXTERNALQA'];
            $submissionData['RTRI_SECTION'] = $submissionData['INFECTIONSUR'] ?? $submissionData['RTRI_SECTION'];
            $submissionData['lab_geopoint'] ??= $submissionData['TESTSITE']['lab_geopoint'] ?? null;

            $data['uuid'] = $submissionData['meta']['instanceID'];
            $data['content'] = 'record';
            $data['formId'] = $formId;
            $data['formVersion'] = $submissionData['__system']["formVersion"];
            $data['meta-instance-id'] = $submissionData['meta']['instanceID'];
            $data['meta-model-version'] = '';
            $data['meta-ui-version'] = '';
            $data['meta-submission-date'] = $submissionData['__system']["submissionDate"];
            $data['meta-is-complete'] = '1';
            $data['meta-date-marked-as-complete'] =  explode("T", $submissionData["end"])[0];
            $data['start'] = $submissionData['start'] ?? null;
            $data['end'] = $submissionData['end'] ?? null;
            $data['today'] = $submissionData['today'] ?? null;
            $data['deviceid'] = $submissionData['deviceid'] ?? null;
            $data['subscriberid'] = $submissionData['subscriberid'] ?? null;
            $data['text_image'] = $submissionData['text_image'] ?? null;
            $data['info1'] = $submissionData['info1'] ?? null;
            $data['info2'] = $submissionData['TESTSITE']['info2'] ?? null;
            $data['assesmentofaudit'] = $submissionData['TESTSITE']['assesmentofaudit'] ?? null;
            $data['auditEndTime'] = $submissionData['preclosure_questions']['auditEndTime'] ?? null;
            $data['auditStartTime'] = $submissionData['TESTSITE']['auditStartTime'] ?? null;
            $data['auditroundno'] = $submissionData['TESTSITE']['auditroundno'] ?? null;
            $data['facilityname'] = $submissionData['TESTSITE']['facilityname'] ?? null;
            $data['facilityid'] = $submissionData['TESTSITE']['facilityid'] ?? null;
            $data['district'] = $submissionData['TESTSITE']['district'] ?? null;
            $data['testingpointname'] = $submissionData['TESTSITE']['testingpointname'] ?? null;
            $data['testingpointtype'] = $submissionData['TESTSITE']['testingpointtype'];
            $data['testingpointtype_other'] = $submissionData['TESTSITE']['testingpointtype_other'] ?? null;
            $data['physicaladdress'] = $submissionData['TESTSITE']['physicaladdress'];
            $data['level'] = $submissionData['TESTSITE']['level'] ?? null;
            $data['level_other'] = $submissionData['TESTSITE']['level_other'] ?? null;

            $data['affiliation'] = $submissionData['TESTSITE']['affiliation'] ?? null;
            $data['affiliation_other'] = $submissionData['TESTSITE']['affiliation_other'] ?? null;
            $data['NumberofTester'] = (isset($submissionData['TESTSITE']['NumberofTester']) && $submissionData['TESTSITE']['NumberofTester'] > 0 ? $submissionData['TESTSITE']['NumberofTester'] : 0);
            $data['client_tested_HIV'] = $submissionData['TESTSITE']['client_tested_HIV'] ?? null;
            $data['client_tested_HIV_PM'] = $submissionData['TESTSITE']['client_tested_HIV_PM'] ?? null;
            $data['client_tested_HIV_PQ'] = $submissionData['TESTSITE']['client_tested_HIV_PQ'] ?? null;
            $data['client_newly_HIV'] = $submissionData['TESTSITE']['client_newly_HIV'] ?? null;
            $data['client_newly_HIV_PM'] = $submissionData['TESTSITE']['client_newly_HIV_PM'];
            $data['client_newly_HIV_PQ'] = $submissionData['TESTSITE']['client_newly_HIV_PQ'];
            $data['client_negative_HIV'] = $submissionData['TESTSITE']['client_negative_HIV'];
            $data['client_negative_HIV_PM'] = $submissionData['TESTSITE']['client_negative_HIV_PM'];
            $data['client_negative_HIV_PQ'] = $submissionData['TESTSITE']['client_negative_HIV_PQ'];
            $data['client_positive_HIV_RTRI'] = $submissionData['TESTSITE']['client_positive_HIV_RTRI'];
            $data['client_positive_HIV_RTRI_PM'] = $submissionData['TESTSITE']['client_positive_HIV_RTRI_PM'];
            $data['client_positive_HIV_RTRI_PQ'] = $submissionData['TESTSITE']['client_positive_HIV_RTRI_PQ'];
            $data['client_recent_RTRI'] = $submissionData['TESTSITE']['client_recent_RTRI'];
            $data['client_recent_RTRI_PM'] = $submissionData['TESTSITE']['client_recent_RTRI_PM'];
            $data['client_recent_RTRI_PQ'] = $submissionData['TESTSITE']['client_recent_RTRI_PQ'];
            $data['name_auditor_lead'] = $submissionData['TESTSITE']['name_auditor_lead'];
            $data['name_auditor2'] = $submissionData['TESTSITE']['nameOfAuditor2'];
            $data['info4'] = $submissionData['SPIRRT']['info4'];
            $data['INSTANCE'] = $submissionData['TESTSITE']['INSTANCE'];


            $data['PERSONAL_Q_1_1_HIV_TRAINING'] = $submissionData['SPIRRT']['PERSONAL_Q_1_1_HIV_TRAINING'] ?? $submissionData['SPIRRT']['PERSONAL_Q_1_1'] ?? null;
            $data['PERSONAL_C_1_1_HIV_TRAINING'] = $submissionData['SPIRRT']['PERSONAL_C_1_1_HIV_TRAINING'] ?? $submissionData['SPIRRT']['PERSONAL_C_1_1'] ?? null;
            $data['PERSONAL_Q_1_2_HIV_TESTING_REGISTER'] = $submissionData['SPIRRT']['PERSONAL_Q_1_2_HIV_TESTING_REGISTER'] ?? $submissionData['SPIRRT']['PERSONAL_Q_1_2'] ?? null;
            $data['PERSONAL_C_1_2_HIV_TESTING_REGISTER'] = $submissionData['SPIRRT']['PERSONAL_C_1_2_HIV_TESTING_REGISTER'] ?? $submissionData['SPIRRT']['PERSONAL_C_1_2'] ?? null;
            $data['PERSONAL_Q_1_3_EQA_PT'] = $submissionData['SPIRRT']['PERSONAL_Q_1_3_EQA_PT'] ?? $submissionData['SPIRRT']['PERSONAL_Q_1_3'] ?? null;
            $data['PERSONAL_C_1_3_EQA_PT'] = $submissionData['SPIRRT']['PERSONAL_C_1_3_EQA_PT'] ?? $submissionData['SPIRRT']['PERSONAL_C_1_3'] ?? null;
            $data['PERSONAL_Q_1_4_QC_PROCESS'] = $submissionData['SPIRRT']['PERSONAL_Q_1_4_QC_PROCESS'] ?? $submissionData['SPIRRT']['PERSONAL_Q_1_4'] ?? null;
            $data['PERSONAL_C_1_4_QC_PROCESS'] = $submissionData['SPIRRT']['PERSONAL_C_1_4_QC_PROCESS'] ?? $submissionData['SPIRRT']['PERSONAL_C_1_4'] ?? null;
            $data['PERSONAL_Q_1_5_SAFETY_MANAGEMENT'] = $submissionData['SPIRRT']['PERSONAL_Q_1_5_SAFETY_MANAGEMENT'] ?? $submissionData['SPIRRT']['PERSONAL_Q_1_5'] ?? null;
            $data['PERSONAL_C_1_5_SAFETY_MANAGEMENT'] = $submissionData['SPIRRT']['PERSONAL_C_1_5_SAFETY_MANAGEMENT'] ?? $submissionData['SPIRRT']['PERSONAL_C_1_5'] ?? null;
            $data['PERSONAL_Q_1_6_REFRESHER_TRAINING'] = $submissionData['SPIRRT']['PERSONAL_Q_1_6_REFRESHER_TRAINING'] ?? $submissionData['SPIRRT']['PERSONAL_Q_1_6'] ?? null;
            $data['PERSONAL_C_1_6_REFRESHER_TRAINING'] = $submissionData['SPIRRT']['PERSONAL_C_1_6_REFRESHER_TRAINING'] ?? $submissionData['SPIRRT']['PERSONAL_C_1_6'] ?? null;
            $data['PERSONAL_Q_1_7_HIV_COMPETENCY_TESTING'] = $submissionData['SPIRRT']['PERSONAL_Q_1_7_HIV_COMPETENCY_TESTING'] ?? $submissionData['SPIRRT']['PERSONAL_Q_1_7'] ?? null;
            $data['PERSONAL_C_1_7_HIV_COMPETENCY_TESTING'] = $submissionData['SPIRRT']['PERSONAL_C_1_7_HIV_COMPETENCY_TESTING'] ?? $submissionData['SPIRRT']['PERSONAL_C_1_7'] ?? null;
            $data['PERSONAL_Q_1_8_NATIONAL_CERTIFICATION'] = $submissionData['SPIRRT']['PERSONAL_Q_1_8_NATIONAL_CERTIFICATION'] ?? $submissionData['SPIRRT']['PERSONAL_Q_1_8'] ?? null;
            $data['PERSONAL_C_1_8_NATIONAL_CERTIFICATION'] = $submissionData['SPIRRT']['PERSONAL_C_1_8_NATIONAL_CERTIFICATION'] ?? $submissionData['SPIRRT']['PERSONAL_C_1_8'] ?? null;
            $data['PERSONAL_Q_1_9_CERTIFIED_TESTERS'] = $submissionData['SPIRRT']['PERSONAL_Q_1_9_CERTIFIED_TESTERS'] ?? $submissionData['SPIRRT']['PERSONAL_Q_1_9'] ?? null;
            $data['PERSONAL_C_1_9_CERTIFIED_TESTERS'] = $submissionData['SPIRRT']['PERSONAL_C_1_9_CERTIFIED_TESTERS'] ?? $submissionData['SPIRRT']['PERSONAL_C_1_9'] ?? null;
            $data['PERSONAL_Q_1_10_RECERTIFIED'] = $submissionData['SPIRRT']['PERSONAL_Q_1_10_RECERTIFIED'] ?? $submissionData['SPIRRT']['PERSONAL_Q_1_10'] ?? null;
            $data['PERSONAL_C_1_10_RECERTIFIED'] = $submissionData['SPIRRT']['PERSONAL_C_1_10_RECERTIFIED'] ?? $submissionData['SPIRRT']['PERSONAL_C_1_10'] ?? null;
            $data['PERSONAL_SCORE'] = $submissionData['SPIRRT']['PERSONAL_SCORE'];
            $data['PERSONAL_Display'] = $submissionData['SPIRRT']['PERSONAL_Display'];
            $data['PERSONALPHOTO'] = $submissionData['SPIRRT']['PERSONALPHOTO'];
            $data['PHYSICAL_Q_2_1_DESIGNATED_HIV_AREA'] = $submissionData['PHYSICAL']['PHYSICAL_Q_2_1_DESIGNATED_HIV_AREA'] ?? $submissionData['PHYSICAL']['PHYSICAL_Q_2_1'] ?? null;
            $data['PHYSICAL_C_2_1_DESIGNATED_HIV_AREA'] = $submissionData['PHYSICAL']['PHYSICAL_C_2_1_DESIGNATED_HIV_AREA'] ?? $submissionData['PHYSICAL']['PHYSICAL_C_2_1'] ?? null;
            $data['PHYSICAL_Q_2_2_CLEAN_TESTING_AREA'] = $submissionData['PHYSICAL']['PHYSICAL_Q_2_2_CLEAN_TESTING_AREA'] ?? $submissionData['PHYSICAL']['PHYSICAL_Q_2_2'] ?? null;
            $data['PHYSICAL_C_2_2_CLEAN_TESTING_AREA'] = $submissionData['PHYSICAL']['PHYSICAL_C_2_2_CLEAN_TESTING_AREA'] ?? $submissionData['PHYSICAL']['PHYSICAL_C_2_2'] ?? null;
            $data['PHYSICAL_Q_2_3_SUFFICIENT_LIGHT_AVAILABILITY'] = $submissionData['PHYSICAL']['PHYSICAL_Q_2_3_SUFFICIENT_LIGHT_AVAILABILITY'] ?? $submissionData['PHYSICAL']['PHYSICAL_Q_2_3'] ?? null;
            $data['PHYSICAL_C_2_3_SUFFICIENT_LIGHT_AVAILABILITY'] = $submissionData['PHYSICAL']['PHYSICAL_C_2_3_SUFFICIENT_LIGHT_AVAILABILITY'] ?? $submissionData['PHYSICAL']['PHYSICAL_C_2_3'] ?? null;
            $data['PHYSICAL_Q_2_4_TEST_KIT_STORAGE'] = $submissionData['PHYSICAL']['PHYSICAL_Q_2_4_TEST_KIT_STORAGE'] ?? $submissionData['PHYSICAL']['PHYSICAL_Q_2_4'] ?? null;
            $data['PHYSICAL_C_2_4_TEST_KIT_STORAGE'] = $submissionData['PHYSICAL']['PHYSICAL_C_2_4_TEST_KIT_STORAGE'] ?? $submissionData['PHYSICAL']['PHYSICAL_C_2_4'] ?? null;
            $data['PHYSICAL_Q_2_5_SUFFICIENT_SECURE_STORAGE'] = $submissionData['PHYSICAL']['PHYSICAL_Q_2_5_SUFFICIENT_SECURE_STORAGE'] ?? $submissionData['PHYSICAL']['PHYSICAL_Q_2_5'] ?? null;
            $data['PHYSICAL_C_2_5_SUFFICIENT_SECURE_STORAGE'] = $submissionData['PHYSICAL']['PHYSICAL_C_2_5_SUFFICIENT_SECURE_STORAGE'] ?? $submissionData['PHYSICAL']['PHYSICAL_C_2_5'] ?? null;
            $data['PHYSICAL_SCORE'] = $submissionData['PHYSICAL']['PHYSICAL_SCORE'];
            $data['PHYSICAL_Display'] = $submissionData['PHYSICAL']['PHYSICAL_Display'];
            $data['PHYSICALPHOTO'] = $submissionData['PHYSICAL']['PHYSICALPHOTO'];
            $data['SAFETY_Q_3_1_IMPLEMENT_SAFETY_PRACTICES'] = $submissionData['SAFETY']['SAFETY_Q_3_1_IMPLEMENT_SAFETY_PRACTICES'] ?? $submissionData['SAFETY']['SAFETY_Q_3_1'] ?? null;
            $data['SAFETY_C_3_1_IMPLEMENT_SAFETY_PRACTICES'] = $submissionData['SAFETY']['SAFETY_C_3_1_IMPLEMENT_SAFETY_PRACTICES'] ?? $submissionData['SAFETY']['SAFETY_C_3_1'] ?? null;
            $data['SAFETY_Q_3_2_ACCIDENTAL_EXPOSURE'] = $submissionData['SAFETY']['SAFETY_Q_3_2_ACCIDENTAL_EXPOSURE'] ?? $submissionData['SAFETY']['SAFETY_Q_3_2'] ?? null;
            $data['SAFETY_C_3_2_ACCIDENTAL_EXPOSURE'] = $submissionData['SAFETY']['SAFETY_C_3_2_ACCIDENTAL_EXPOSURE'] ?? $submissionData['SAFETY']['SAFETY_C_3_2'] ?? null;
            $data['SAFETY_Q_3_3_PRACTICE_SAFETY_PRACTICES'] = $submissionData['SAFETY']['SAFETY_Q_3_3_PRACTICE_SAFETY_PRACTICES'] ?? $submissionData['SAFETY']['SAFETY_Q_3_3'] ?? null;
            $data['SAFETY_C_3_3_PRACTICE_SAFETY_PRACTICES'] = $submissionData['SAFETY']['SAFETY_C_3_3_PRACTICE_SAFETY_PRACTICES'] ?? $submissionData['SAFETY']['SAFETY_C_3_3'] ?? null;
            $data['SAFETY_Q_3_4_PPE_AVAILABILITY'] = $submissionData['SAFETY']['SAFETY_Q_3_4_PPE_AVAILABILITY'] ?? $submissionData['SAFETY']['SAFETY_Q_3_4'] ?? null;
            $data['SAFETY_C_3_4_PPE_AVAILABILITY'] = $submissionData['SAFETY']['SAFETY_C_3_4_PPE_AVAILABILITY'] ?? $submissionData['SAFETY']['SAFETY_C_3_4'] ?? null;
            $data['SAFETY_Q_3_5_PPE_USED_PROPERLY'] = $submissionData['SAFETY']['SAFETY_Q_3_5_PPE_USED_PROPERLY'] ?? $submissionData['SAFETY']['SAFETY_Q_3_5'] ?? null;
            $data['SAFETY_C_3_5_PPE_USED_PROPERLY'] = $submissionData['SAFETY']['SAFETY_C_3_5_PPE_USED_PROPERLY'] ?? $submissionData['SAFETY']['SAFETY_C_3_5'] ?? null;
            $data['SAFETY_Q_3_6_WATER_SOAP_AVAILABILITY'] = $submissionData['SAFETY']['SAFETY_Q_3_6_WATER_SOAP_AVAILABILITY'] ?? $submissionData['SAFETY']['SAFETY_Q_3_6'] ?? null;
            $data['SAFETY_C_3_6_WATER_SOAP_AVAILABILITY'] = $submissionData['SAFETY']['SAFETY_C_3_6_WATER_SOAP_AVAILABILITY'] ?? $submissionData['SAFETY']['SAFETY_C_3_6'] ?? null;
            $data['SAFETY_Q_3_7_DISINFECTANT_AVAILABLE'] = $submissionData['SAFETY']['SAFETY_Q_3_7_DISINFECTANT_AVAILABLE'] ?? $submissionData['SAFETY']['SAFETY_Q_3_7'] ?? null;
            $data['SAFETY_C_3_7_DISINFECTANT_AVAILABLE'] = $submissionData['SAFETY']['SAFETY_C_3_7_DISINFECTANT_AVAILABLE'] ?? $submissionData['SAFETY']['SAFETY_C_3_7'] ?? null;
            $data['SAFETY_Q_3_8_DISINFECTANT_LABELED_PROPERLY'] = $submissionData['SAFETY']['SAFETY_Q_3_8_DISINFECTANT_LABELED_PROPERLY'] ?? $submissionData['SAFETY']['SAFETY_Q_3_8'] ?? null;
            $data['SAFETY_C_3_8_DISINFECTANT_LABELED_PROPERLY'] = $submissionData['SAFETY']['SAFETY_C_3_8_DISINFECTANT_LABELED_PROPERLY'] ?? $submissionData['SAFETY']['SAFETY_C_3_8'] ?? null;
            $data['SAFETY_Q_3_9_SEGREGATION_OF_WASTE'] = $submissionData['SAFETY']['SAFETY_Q_3_9_SEGREGATION_OF_WASTE'] ?? $submissionData['SAFETY']['SAFETY_Q_3_9'] ?? null;
            $data['SAFETY_C_3_9_SEGREGATION_OF_WASTE'] = $submissionData['SAFETY']['SAFETY_C_3_9_SEGREGATION_OF_WASTE'] ?? $submissionData['SAFETY']['SAFETY_C_3_9'] ?? null;
            $data['SAFETY_Q_3_10_INFECTIOUS_WASTE_EMPTIED'] = $submissionData['SAFETY']['SAFETY_Q_3_10_INFECTIOUS_WASTE_EMPTIED'] ?? $submissionData['SAFETY']['SAFETY_Q_3_10'] ?? null;
            $data['SAFETY_C_3_10_INFECTIOUS_WASTE_EMPTIED'] = $submissionData['SAFETY']['SAFETY_C_3_10_INFECTIOUS_WASTE_EMPTIED'] ?? $submissionData['SAFETY']['SAFETY_C_3_10'] ?? null;
            $data['SAFETY_SCORE'] = $submissionData['SAFETY']['SAFETY_SCORE'];
            $data['SAFETY_DISPLAY'] = $submissionData['SAFETY']['SAFETY_DISPLAY'];
            $data['SAFETYPHOTO'] = $submissionData['SAFETY']['SAFETYPHOTO'];
            $data['PRE_Q_4_1_NATIONAL_GUIDELINES'] = $submissionData['PRETEST']['PRE_Q_4_1_NATIONAL_GUIDELINES'] ?? $submissionData['PRETEST']['PRE_Q_4_1'] ?? null;
            $data['PRE_C_4_1_NATIONAL_GUIDELINES'] = $submissionData['PRETEST']['PRE_C_4_1_NATIONAL_GUIDELINES'] ?? $submissionData['PRETEST']['PRE_C_4_1'] ?? null;
            $data['PRE_Q_4_2_HIV_TESTING_ALGORITHM'] = $submissionData['PRETEST']['PRE_Q_4_2_HIV_TESTING_ALGORITHM'] ?? $submissionData['PRETEST']['PRE_Q_4_2'] ?? null;
            $data['PRE_C_4_2_HIV_TESTING_ALGORITHM'] = $submissionData['PRETEST']['PRE_C_4_2_HIV_TESTING_ALGORITHM'] ?? $submissionData['PRETEST']['PRE_C_4_2'] ?? null;
            $data['PRE_Q_4_3_TEST_PROCEDURES_ACCESSIBLE'] = $submissionData['PRETEST']['PRE_Q_4_3_TEST_PROCEDURES_ACCESSIBLE'] ?? $submissionData['PRETEST']['PRE_Q_4_3'] ?? null;
            $data['PRE_C_4_3_TEST_PROCEDURES_ACCESSIBLE'] = $submissionData['PRETEST']['PRE_C_4_3_TEST_PROCEDURES_ACCESSIBLE'] ?? $submissionData['PRETEST']['PRE_C_4_3'] ?? null;
            $data['PRE_Q_4_4_TEST_PROCEDURES_ACCURATE'] = $submissionData['PRETEST']['PRE_Q_4_4_TEST_PROCEDURES_ACCURATE'] ?? $submissionData['PRETEST']['PRE_Q_4_4'] ?? null;
            $data['PRE_C_4_4_TEST_PROCEDURES_ACCURATE'] = $submissionData['PRETEST']['PRE_C_4_4_TEST_PROCEDURES_ACCURATE'] ?? $submissionData['PRETEST']['PRE_C_4_4'] ?? null;
            $data['PRE_Q_4_5_APPROVED_KITS_AVAILABLE'] = $submissionData['PRETEST']['PRE_Q_4_5_APPROVED_KITS_AVAILABLE'] ?? $submissionData['PRETEST']['PRE_Q_4_5'] ?? null;
            $data['PRE_C_4_5_APPROVED_KITS_AVAILABLE'] = $submissionData['PRETEST']['PRE_C_4_5_APPROVED_KITS_AVAILABLE'] ?? $submissionData['PRETEST']['PRE_C_4_5'] ?? null;
            $data['PRE_Q_4_6_HIV_KITS_EXPIRATION'] = $submissionData['PRETEST']['PRE_Q_4_6_HIV_KITS_EXPIRATION'] ?? $submissionData['PRETEST']['PRE_Q_4_6'] ?? null;
            $data['PRE_C_4_6_HIV_KITS_EXPIRATION'] = $submissionData['PRETEST']['PRE_C_4_6_HIV_KITS_EXPIRATION'] ?? $submissionData['PRETEST']['PRE_C_4_6'] ?? null;
            $data['PRE_Q_4_7_KIT_SUPPLIES_AVAILABILITY'] = $submissionData['PRETEST']['PRE_Q_4_7_KIT_SUPPLIES_AVAILABILITY'] ?? $submissionData['PRETEST']['PRE_Q_4_7'] ?? null;
            $data['PRE_C_4_7_KIT_SUPPLIES_AVAILABILITY'] = $submissionData['PRETEST']['PRE_C_4_7_KIT_SUPPLIES_AVAILABILITY'] ?? $submissionData['PRETEST']['PRE_C_4_7'] ?? null;
            $data['PRE_Q_4_8_STOCK_MANAGEMENT'] = $submissionData['PRETEST']['PRE_Q_4_8_STOCK_MANAGEMENT'] ?? $submissionData['PRETEST']['PRE_Q_4_8'] ?? null;
            $data['PRE_C_4_8_STOCK_MANAGEMENT'] = $submissionData['PRETEST']['PRE_C_4_8_STOCK_MANAGEMENT'] ?? $submissionData['PRETEST']['PRE_C_4_8'] ?? null;
            $data['PRE_Q_4_9_DOCUMENTED_INVENTORY'] = $submissionData['PRETEST']['PRE_Q_4_9_DOCUMENTED_INVENTORY'] ?? $submissionData['PRETEST']['PRE_Q_4_9'] ?? null;
            $data['PRE_C_4_9_DOCUMENTED_INVENTORY'] = $submissionData['PRETEST']['PRE_C_4_9_DOCUMENTED_INVENTORY'] ?? $submissionData['PRETEST']['PRE_C_4_9'] ?? null;
            $data['PRE_Q_4_10_SOPS_BLOOD_COLLECTION'] = $submissionData['PRETEST']['PRE_Q_4_10_SOPS_BLOOD_COLLECTION'] ?? $submissionData['PRETEST']['PRE_Q_4_10'] ?? null;
            $data['PRE_C_4_10_SOPS_BLOOD_COLLECTION'] = $submissionData['PRETEST']['PRE_C_4_10_SOPS_BLOOD_COLLECTION'] ?? $submissionData['PRETEST']['PRE_C_4_10'] ?? null;
            $data['PRE_Q_4_11_BLOOD_COLLECTION_SUPPLIES'] = $submissionData['PRETEST']['PRE_Q_4_11_BLOOD_COLLECTION_SUPPLIES'] ?? $submissionData['PRETEST']['PRE_Q_4_11'] ?? null;
            $data['PRE_C_4_11_BLOOD_COLLECTION_SUPPLIES'] = $submissionData['PRETEST']['PRE_C_4_11_BLOOD_COLLECTION_SUPPLIES'] ?? $submissionData['PRETEST']['PRE_C_4_11'] ?? null;
            $data['PRE_Q_4_12_CLIENT_IDENTIFICATION'] = $submissionData['PRETEST']['PRE_Q_4_12_CLIENT_IDENTIFICATION'] ?? $submissionData['PRETEST']['PRE_Q_4_12'] ?? null;
            $data['PRE_C_4_12_CLIENT_IDENTIFICATION'] = $submissionData['PRETEST']['PRE_C_4_12_CLIENT_IDENTIFICATION'] ?? $submissionData['PRETEST']['PRE_C_4_12'] ?? null;
            $data['PRE_Q_4_13_CLIENT_ID_RECORDED'] = $submissionData['PRETEST']['PRE_Q_4_13_CLIENT_ID_RECORDED'] ?? $submissionData['PRETEST']['PRE_Q_4_13'] ?? null;
            $data['PRE_C_4_13_CLIENT_ID_RECORDED'] = $submissionData['PRETEST']['PRE_C_4_13_CLIENT_ID_RECORDED'] ?? $submissionData['PRETEST']['PRE_C_4_13'] ?? null;
            $data['PRETEST_SCORE'] = $submissionData['PRETEST']['PRETEST_SCORE'];
            $data['PRETEST_Display'] = $submissionData['PRETEST']['PRETEST_Display'];
            $data['PRETESTPHOTO'] = $submissionData['PRETEST']['PRETESTPHOTO'];
            $data['TEST_Q_5_1_PROCEDURES_TESTING_ALGORITHM'] = $submissionData['TEST']['TEST_Q_5_1_PROCEDURES_TESTING_ALGORITHM'] ?? $submissionData['TEST']['TEST_Q_5_1'] ?? null;
            $data['TEST_C_5_1_PROCEDURES_TESTING_ALGORITHM'] = $submissionData['TEST']['TEST_C_5_1_PROCEDURES_TESTING_ALGORITHM'] ?? $submissionData['TEST']['TEST_C_5_1'] ?? null;
            $data['TEST_Q_5_2_TIMERS_AVAILABILITY'] = $submissionData['TEST']['TEST_Q_5_2_TIMERS_AVAILABILITY'] ?? $submissionData['TEST']['TEST_Q_5_2'] ?? null;
            $data['TEST_C_5_2_TIMERS_AVAILABILITY'] = $submissionData['TEST']['TEST_C_5_2_TIMERS_AVAILABILITY'] ?? $submissionData['TEST']['TEST_C_5_2'] ?? null;
            $data['TEST_Q_5_3_SAMPLE_DEVICE_ACCURACY'] = $submissionData['TEST']['TEST_Q_5_3_SAMPLE_DEVICE_ACCURACY'] ?? $submissionData['TEST']['TEST_Q_5_3'] ?? null;
            $data['TEST_C_5_3_SAMPLE_DEVICE_ACCURACY'] = $submissionData['TEST']['TEST_C_5_3_SAMPLE_DEVICE_ACCURACY'] ?? $submissionData['TEST']['TEST_C_5_3'] ?? null;
            $data['TEST_Q_5_4_TESTING_PROCEDURE_FOLLOWED'] = $submissionData['TEST']['TEST_Q_5_4_TESTING_PROCEDURE_FOLLOWED'] ?? $submissionData['TEST']['TEST_Q_5_4'] ?? null;
            $data['TEST_C_5_4_TESTING_PROCEDURE_FOLLOWED'] = $submissionData['TEST']['TEST_C_5_4_TESTING_PROCEDURE_FOLLOWED'] ?? $submissionData['TEST']['TEST_C_5_4'] ?? null;
            $data['TEST_Q_5_5_QUALITY_CONTROL'] = $submissionData['TEST']['TEST_Q_5_5_QUALITY_CONTROL'] ?? $submissionData['TEST']['TEST_Q_5_5'] ?? null;
            $data['TEST_C_5_5_QUALITY_CONTROL'] = $submissionData['TEST']['TEST_C_5_5_QUALITY_CONTROL'] ?? $submissionData['TEST']['TEST_C_5_5'] ?? null;
            $data['TEST_Q_5_6_QC_RESULTS_RECORDED'] = $submissionData['TEST']['TEST_Q_5_6_QC_RESULTS_RECORDED'] ?? $submissionData['TEST']['TEST_Q_5_6'] ?? null;
            $data['TEST_C_5_6_QC_RESULTS_RECORDED'] = $submissionData['TEST']['TEST_C_5_6_QC_RESULTS_RECORDED'] ?? $submissionData['TEST']['TEST_C_5_6'] ?? null;
            $data['TEST_Q_5_7_INCORRECT_QC_RESULTS'] = $submissionData['TEST']['TEST_Q_5_7_INCORRECT_QC_RESULTS'] ?? $submissionData['TEST']['TEST_Q_5_7'] ?? null;
            $data['TEST_C_5_7_INCORRECT_QC_RESULTS'] = $submissionData['TEST']['TEST_C_5_7_INCORRECT_QC_RESULTS'] ?? $submissionData['TEST']['TEST_C_5_7'] ?? null;
            $data['TEST_Q_5_8_APPROPRIATE_STEPS_TAKEN'] = $submissionData['TEST']['TEST_Q_5_8_APPROPRIATE_STEPS_TAKEN'] ?? $submissionData['TEST']['TEST_Q_5_8'] ?? null;
            $data['TEST_C_5_8_APPROPRIATE_STEPS_TAKEN'] = $submissionData['TEST']['TEST_C_5_8_APPROPRIATE_STEPS_TAKEN'] ?? $submissionData['TEST']['TEST_C_5_8'] ?? null;
            $data['TEST_Q_5_9_REVIEW_QC_RECORDS'] = $submissionData['TEST']['TEST_Q_5_9_REVIEW_QC_RECORDS'] ?? $submissionData['TEST']['TEST_Q_5_9'] ?? null;
            $data['TEST_C_5_9_REVIEW_QC_RECORDS'] = $submissionData['TEST']['TEST_C_5_9_REVIEW_QC_RECORDS'] ?? $submissionData['TEST']['TEST_C_5_9'] ?? null;
            $data['TEST_SCORE'] = $submissionData['TEST']['TEST_SCORE'];
            $data['TEST_DISPLAY'] = $submissionData['TEST']['TEST_DISPLAY'];
            $data['TESTPHOTO'] = $submissionData['TEST']['TESTPHOTO'];
            $data['POST_Q_6_1_STANDARDIZED_HIV_REGISTER'] = $submissionData['POSTTEST']['POST_Q_6_1_STANDARDIZED_HIV_REGISTER'] ?? $submissionData['POSTTEST']['POST_Q_6_1'] ?? null;
            $data['POST_C_6_1_STANDARDIZED_HIV_REGISTER'] = $submissionData['POSTTEST']['POST_C_6_1_STANDARDIZED_HIV_REGISTER'] ?? $submissionData['POSTTEST']['POST_C_6_1'] ?? null;
            $data['POST_Q_6_2_ELEMENTS_CAPTURED_CORRECTLY'] = $submissionData['POSTTEST']['POST_Q_6_2_ELEMENTS_CAPTURED_CORRECTLY'] ?? $submissionData['POSTTEST']['POST_Q_6_2'] ?? null;
            $data['POST_C_6_2_ELEMENTS_CAPTURED_CORRECTLY'] = $submissionData['POSTTEST']['POST_C_6_2_ELEMENTS_CAPTURED_CORRECTLY'] ?? $submissionData['POSTTEST']['POST_C_6_2'] ?? null;
            $data['POST_Q_6_3_PAGE_TOTAL_SUMMARY'] = $submissionData['POSTTEST']['POST_Q_6_3_PAGE_TOTAL_SUMMARY'] ?? $submissionData['POSTTEST']['POST_Q_6_3'] ?? null;
            $data['POST_C_6_3_PAGE_TOTAL_SUMMARY'] = $submissionData['POSTTEST']['POST_C_6_3_PAGE_TOTAL_SUMMARY'] ?? $submissionData['POSTTEST']['POST_C_6_3'] ?? null;
            $data['POST_Q_6_4_INVALID_TEST_RESULT_RECORDED'] = $submissionData['POSTTEST']['POST_Q_6_4_INVALID_TEST_RESULT_RECORDED'] ?? $submissionData['POSTTEST']['POST_Q_6_4'] ?? null;
            $data['POST_C_6_4_INVALID_TEST_RESULT_RECORDED'] = $submissionData['POSTTEST']['POST_C_6_4_INVALID_TEST_RESULT_RECORDED'] ?? $submissionData['POSTTEST']['POST_C_6_4'] ?? null;
            $data['POST_Q_6_5_APPROPRIATE_STEPS_TAKEN'] = $submissionData['POSTTEST']['POST_Q_6_5_APPROPRIATE_STEPS_TAKEN'] ?? $submissionData['POSTTEST']['POST_Q_6_5'] ?? null;
            $data['POST_C_6_5_APPROPRIATE_STEPS_TAKEN'] = $submissionData['POSTTEST']['POST_C_6_5_APPROPRIATE_STEPS_TAKEN'] ?? $submissionData['POSTTEST']['POST_C_6_5'] ?? null;
            $data['POST_Q_6_6_REGISTERS_REVIEWED'] = $submissionData['POSTTEST']['POST_Q_6_6_REGISTERS_REVIEWED'] ?? $submissionData['POSTTEST']['POST_Q_6_6'] ?? null;
            $data['POST_C_6_6_REGISTERS_REVIEWED'] = $submissionData['POSTTEST']['POST_C_6_6_REGISTERS_REVIEWED'] ?? $submissionData['POSTTEST']['POST_C_6_6'] ?? null;
            $data['POST_Q_6_7_DOCUMENTS_SECURELY_KEPT'] = $submissionData['POSTTEST']['POST_Q_6_7_DOCUMENTS_SECURELY_KEPT'] ?? $submissionData['POSTTEST']['POST_Q_6_7'] ?? null;
            $data['POST_C_6_7_DOCUMENTS_SECURELY_KEPT'] = $submissionData['POSTTEST']['POST_C_6_7_DOCUMENTS_SECURELY_KEPT'] ?? $submissionData['POSTTEST']['POST_C_6_7'] ?? null;
            $data['POST_Q_6_8_REGISTER_SECURE_LOCATION'] = $submissionData['POSTTEST']['POST_Q_6_8_REGISTER_SECURE_LOCATION'] ?? $submissionData['POSTTEST']['POST_Q_6_8'] ?? null;
            $data['POST_C_6_8_REGISTER_SECURE_LOCATION'] = $submissionData['POSTTEST']['POST_C_6_8_REGISTER_SECURE_LOCATION'] ?? $submissionData['POSTTEST']['POST_C_6_8'] ?? null;
            $data['POST_Q_6_9_REGISTERS_PROPERLY_LABELED'] = $submissionData['POSTTEST']['POST_Q_6_9_REGISTERS_PROPERLY_LABELED'] ?? $submissionData['POSTTEST']['POST_Q_6_9'] ?? null;
            $data['POST_C_6_9_REGISTERS_PROPERLY_LABELED'] = $submissionData['POSTTEST']['POST_C_6_9_REGISTERS_PROPERLY_LABELED'] ?? $submissionData['POSTTEST']['POST_C_6_9'] ?? null;
            $data['POST_SCORE'] = $submissionData['POSTTEST']['POST_SCORE'];
            $data['POST_DISPLAY'] = $submissionData['POSTTEST']['POST_DISPLAY'];
            $data['POSTTESTPHOTO'] = $submissionData['POSTTEST']['POSTTESTPHOTO'];
            $data['EQA_Q_7_1_PT_ENROLLMENT'] = $submissionData['EQA']['EQA_Q_7_1_PT_ENROLLMENT'] ?? $submissionData['EQA']['EQA_Q_7_1'] ?? null;
            $data['EQA_C_7_1_PT_ENROLLMENT'] = $submissionData['EQA']['EQA_C_7_1_PT_ENROLLMENT'] ?? $submissionData['EQA']['EQA_C_7_1'] ?? null;
            $data['EQA_Q_7_2_TESTING_EQAPT_SAMPLES'] = $submissionData['EQA']['EQA_Q_7_2_TESTING_EQAPT_SAMPLES'] ?? $submissionData['EQA']['EQA_Q_7_2'] ?? null;
            $data['EQA_C_7_2_TESTING_EQAPT_SAMPLES'] = $submissionData['EQA']['EQA_C_7_2_TESTING_EQAPT_SAMPLES'] ?? $submissionData['EQA']['EQA_C_7_2'] ?? null;
            $data['EQA_Q_7_3_REVIEW_BEFORE_SUBMISSION'] = $submissionData['EQA']['EQA_Q_7_3_REVIEW_BEFORE_SUBMISSION'] ?? $submissionData['EQA']['EQA_Q_7_3'] ?? null;
            $data['EQA_C_7_3_REVIEW_BEFORE_SUBMISSION'] = $submissionData['EQA']['EQA_C_7_3_REVIEW_BEFORE_SUBMISSION'] ?? $submissionData['EQA']['EQA_C_7_3'] ?? null;
            $data['EQA_Q_7_4_FEEDBACK_RECEIVED_REVIEWED'] = $submissionData['EQA']['EQA_Q_7_4_FEEDBACK_RECEIVED_REVIEWED'] ?? $submissionData['EQA']['EQA_Q_7_4'] ?? null;
            $data['EQA_C_7_4_FEEDBACK_RECEIVED_REVIEWED'] = $submissionData['EQA']['EQA_C_7_4_FEEDBACK_RECEIVED_REVIEWED'] ?? $submissionData['EQA']['EQA_C_7_4'] ?? null;
            $data['EQA_Q_7_5_IMPLEMENT_CORRECTIVE_ACTION'] = $submissionData['EQA']['EQA_Q_7_5_IMPLEMENT_CORRECTIVE_ACTION'] ?? $submissionData['EQA']['EQA_Q_7_5'] ?? null;
            $data['EQA_C_7_5_IMPLEMENT_CORRECTIVE_ACTION'] = $submissionData['EQA']['EQA_C_7_5_IMPLEMENT_CORRECTIVE_ACTION'] ?? $submissionData['EQA']['EQA_C_7_5'] ?? null;
            $data['EQA_Q_7_6_RECEIVE_PERIODIC_VISITS'] = $submissionData['EQA']['EQA_Q_7_6_RECEIVE_PERIODIC_VISITS'] ?? $submissionData['EQA']['EQA_Q_7_6'] ?? null;
            $data['EQA_C_7_6_RECEIVE_PERIODIC_VISITS'] = $submissionData['EQA']['EQA_C_7_6_RECEIVE_PERIODIC_VISITS'] ?? $submissionData['EQA']['EQA_C_7_6'] ?? null;
            $data['EQA_Q_7_7_FEEDBACK_PROVIDED_DOCUMENTED'] = $submissionData['EQA']['EQA_Q_7_7_FEEDBACK_PROVIDED_DOCUMENTED'] ?? $submissionData['EQA']['EQA_Q_7_7'] ?? null;
            $data['EQA_C_7_7_FEEDBACK_PROVIDED_DOCUMENTED'] = $submissionData['EQA']['EQA_C_7_7_FEEDBACK_PROVIDED_DOCUMENTED'] ?? $submissionData['EQA']['EQA_C_7_7'] ?? null;
            $data['EQA_Q_7_8_TESTERS_RETRAINED_IN_VISITS'] = $submissionData['EQA']['EQA_Q_7_8_TESTERS_RETRAINED_IN_VISITS'] ?? $submissionData['EQA']['EQA_Q_7_8'] ?? null;
            $data['EQA_C_7_8_TESTERS_RETRAINED_IN_VISITS'] = $submissionData['EQA']['EQA_C_7_8_TESTERS_RETRAINED_IN_VISITS'] ?? $submissionData['EQA']['EQA_C_7_8'] ?? null;
            $data['performrtritesting'] = $submissionData['performrtritesting'] ?? $submissionData['TESTSITE']['isRTRIPerformed'] ?? null;
            $data['EQA_SCORE'] = $submissionData['EQA']['EQA_SCORE'];
            $data['EQA_DISPLAY'] = $submissionData['EQA']['EQA_DISPLAY'];
            $data['EQAPHOTO'] = $submissionData['EQA']['EQAPHOTO'];
            $data['RTRI_Q_8_1_TESTERS_RECEIVED_RTRI_TRAINING'] = $submissionData['RTRI_SECTION']['RTRI_Q_8_1_TESTERS_RECEIVED_RTRI_TRAINING'] ?? $submissionData['RTRI_SECTION']['RTRI_Q_8_1'] ?? null;
            $data['RTRI_C_8_1_TESTERS_RECEIVED_RTRI_TRAINING'] = $submissionData['RTRI_SECTION']['RTRI_C_8_1_TESTERS_RECEIVED_RTRI_TRAINING'] ?? $submissionData['RTRI_SECTION']['RTRI_C_8_1'] ?? null;
            $data['RTRI_Q_8_2_TESTERS_DEMONSTRATED_COMPETENCY'] = $submissionData['RTRI_SECTION']['RTRI_Q_8_2_TESTERS_DEMONSTRATED_COMPETENCY'] ?? $submissionData['RTRI_SECTION']['RTRI_Q_8_2'] ?? null;
            $data['RTRI_C_8_2_TESTERS_DEMONSTRATED_COMPETENCY'] = $submissionData['RTRI_SECTION']['RTRI_C_8_2_TESTERS_DEMONSTRATED_COMPETENCY'] ?? $submissionData['RTRI_SECTION']['RTRI_C_8_2'] ?? null;
            $data['RTRI_Q_8_3_JOBAIDS_READILY_AVAILABLE'] = $submissionData['RTRI_SECTION']['RTRI_Q_8_3_JOBAIDS_READILY_AVAILABLE'] ?? $submissionData['RTRI_SECTION']['RTRI_Q_8_3'] ?? null;
            $data['RTRI_C_8_3_JOBAIDS_READILY_AVAILABLE'] = $submissionData['RTRI_SECTION']['RTRI_C_8_3_JOBAIDS_READILY_AVAILABLE'] ?? $submissionData['RTRI_SECTION']['RTRI_C_8_3'] ?? null;
            $data['RTRI_Q_8_4_SUFFICIENT_SUPPLY_AVAILABLE'] = $submissionData['RTRI_SECTION']['RTRI_Q_8_4_SUFFICIENT_SUPPLY_AVAILABLE'] ?? $submissionData['RTRI_SECTION']['RTRI_Q_8_4'] ?? null;
            $data['RTRI_C_8_4_SUFFICIENT_SUPPLY_AVAILABLE'] = $submissionData['RTRI_SECTION']['RTRI_C_8_4_SUFFICIENT_SUPPLY_AVAILABLE'] ?? $submissionData['RTRI_SECTION']['RTRI_C_8_4'] ?? null;
            $data['RTRI_Q_8_5_RTRI_KIT_STORAGE'] = $submissionData['RTRI_SECTION']['RTRI_Q_8_5_RTRI_KIT_STORAGE'] ?? $submissionData['RTRI_SECTION']['RTRI_Q_8_5'] ?? null;
            $data['RTRI_C_8_5_RTRI_KIT_STORAGE'] = $submissionData['RTRI_SECTION']['RTRI_C_8_5_RTRI_KIT_STORAGE'] ?? $submissionData['RTRI_SECTION']['RTRI_C_8_5'] ?? null;
            $data['RTRI_Q_8_6_RTRI_TESTING_PROCEDURE_FOLLOWED'] = $submissionData['RTRI_SECTION']['RTRI_Q_8_6_RTRI_TESTING_PROCEDURE_FOLLOWED'] ?? $submissionData['RTRI_SECTION']['RTRI_Q_8_6'] ?? null;
            $data['RTRI_C_8_6_RTRI_TESTING_PROCEDURE_FOLLOWED'] = $submissionData['RTRI_SECTION']['RTRI_C_8_6_RTRI_TESTING_PROCEDURE_FOLLOWED'] ?? $submissionData['RTRI_SECTION']['RTRI_C_8_6'] ?? null;
            $data['RTRI_Q_8_7_RTRI_TESTING_RESULTS_DOCUMENTED'] = $submissionData['RTRI_SECTION']['RTRI_Q_8_7_RTRI_TESTING_RESULTS_DOCUMENTED'] ?? $submissionData['RTRI_SECTION']['RTRI_Q_8_7'] ?? null;
            $data['RTRI_C_8_7_RTRI_TESTING_RESULTS_DOCUMENTED'] = $submissionData['RTRI_SECTION']['RTRI_C_8_7_RTRI_TESTING_RESULTS_DOCUMENTED'] ?? $submissionData['RTRI_SECTION']['RTRI_C_8_7'] ?? null;
            $data['RTRI_Q_8_8_QC_ROUTINELY_USED'] = $submissionData['RTRI_SECTION']['RTRI_Q_8_8_QC_ROUTINELY_USED'] ?? $submissionData['RTRI_SECTION']['RTRI_Q_8_8'] ?? null;
            $data['RTRI_C_8_8_QC_ROUTINELY_USED'] = $submissionData['RTRI_SECTION']['RTRI_C_8_8_QC_ROUTINELY_USED'] ?? $submissionData['RTRI_SECTION']['RTRI_C_8_8'] ?? null;
            $data['RTRI_Q_8_9_QC_RESULTS_RECORDED'] = $submissionData['RTRI_SECTION']['RTRI_Q_8_9_QC_RESULTS_RECORDED'] ?? $submissionData['RTRI_SECTION']['RTRI_Q_8_9'] ?? null;
            $data['RTRI_C_8_9_QC_RESULTS_RECORDED'] = $submissionData['RTRI_SECTION']['RTRI_C_8_9_QC_RESULTS_RECORDED'] ?? $submissionData['RTRI_SECTION']['RTRI_C_8_9'] ?? null;
            $data['RTRI_Q_8_10_INCORRECT_QC_DOCUMENTED'] = $submissionData['RTRI_SECTION']['RTRI_Q_8_10_INCORRECT_QC_DOCUMENTED'] ?? $submissionData['RTRI_SECTION']['RTRI_Q_8_10'] ?? null;
            $data['RTRI_C_8_10_INCORRECT_QC_DOCUMENTED'] = $submissionData['RTRI_SECTION']['RTRI_C_8_10_INCORRECT_QC_DOCUMENTED'] ?? $submissionData['RTRI_SECTION']['RTRI_C_8_10'] ?? null;
            $data['RTRI_Q_8_11_INVALID_RTRI_RESULTS'] = $submissionData['RTRI_SECTION']['RTRI_Q_8_11_INVALID_RTRI_RESULTS'] ?? $submissionData['RTRI_SECTION']['RTRI_Q_8_11'] ?? null;
            $data['RTRI_C_8_11_INVALID_RTRI_RESULTS'] = $submissionData['RTRI_SECTION']['RTRI_C_8_11_INVALID_RTRI_RESULTS'] ?? $submissionData['RTRI_SECTION']['RTRI_C_8_11'] ?? null;
            $data['RTRI_SCORE'] = $submissionData['RTRI_SECTION']['RTRI_SCORE'] ?? null;
            $data['RTRI_DISPLAY'] = $submissionData['RTRI_SECTION']['RTRI_DISPLAY'] ?? null;
            $data['RTRIPHOTO'] = $submissionData['RTRI_SECTION']['RTRIPHOTO'] ?? null;
            $data['AuditRequiredScore'] = $submissionData['AuditRequiredScore'];
            $data['FINAL_AUDIT_SCORE'] = $submissionData['FINAL_AUDIT_SCORE'];
            $data['MAX_AUDIT_SCORE'] = $submissionData['MAX_AUDIT_SCORE'];
            $data['AUDIT_SCORE_PERCENTAGE'] = $submissionData['AUDIT_SCORE_PERCENTAGE'] ?? $submissionData['AUDIT_SCORE_PERCANTAGE'] ?? null;
            $data['AUDIT_SCORE_PERCENTAGE_ROUNDED'] = $submissionData['AUDIT_SCORE_PERCENTAGE_ROUNDED'] ?? $submissionData['AUDIT_SCORE_PERCANTAGE_ROUNDED'] ?? null;
            $data['staffaudited'] = $submissionData['preclosure_questions']['staffaudited'];
            $data['durationaudit'] = $submissionData['preclosure_questions']['durationaudit'];
            $data['personincharge'] = $submissionData['preclosure_questions']['personincharge'];
            $data['endofsurvey'] = $submissionData['endofsurvey'];
            $data['sitecode'] = $submissionData['preclosure_questions']['sitecode'];

            $data['info5'] = $submissionData['scoring']['info5'];
            $data['info6'] = $submissionData['scoring']['info6'];
            $data['info10'] = $submissionData['scoring']['info10'];
            $data['info11'] = $submissionData['scoring']['info11'];
            $data['SUMMARY_NOT_AVL'] = $submissionData['SUMMARY_NOT_AVL'] ?? null;
            $data['info12'] = $submissionData['SUMMARY']['info12'];
            $data['info177'] = '';
            $data['info178'] = '';
            $data['info179'] = '';
            $data['info180'] = '';
            $data['info181'] = '';
            $data['info182'] = '';
            $data['info183'] = '';
            $data['info17a'] = $submissionData['Summary_cont_a']['info17a'] ?? null;
            $data['info21'] = $submissionData['Summary_cont_a']['info21'] ?? null;
            $data['info22'] = $submissionData['Summary_cont_a']['info22'] ?? null;
            $data['info23'] = $submissionData['Summary_cont_a']['info23'] ?? null;
            $data['info24'] = $submissionData['Summary_cont_a']['info24'] ?? null;
            $data['info25'] = $submissionData['Summary_cont_a']['info25'] ?? null;
            $data['info26'] = $submissionData['Summary_cont_a']['info26'] ?? null;
            // Check for new corrective action fields in raw data
            $newCorrectiveActions = [];

            if (!empty($submissionData['SPIRRT']['ENABLE_PERSONAL_IMM_CORACT']) && $submissionData['SPIRRT']['ENABLE_PERSONAL_IMM_CORACT'] == 'yes') {
                $filteredActions = CommonService::filterNonNullValues($submissionData['SPIRRT']['PERSONAL_CORRECTIVE_ACTIONS_IMMEDIATE'] ?? []);
                if (!empty($filteredActions)) {
                    $newCorrectiveActions['PERSONAL_CORRECTIVE_ACTIONS_IMMEDIATE'] = $filteredActions;
                }
            }
            if (!empty($submissionData['SPIRRT']['ENABLE_PERSONAL_FUP_CORACT']) && $submissionData['SPIRRT']['ENABLE_PERSONAL_FUP_CORACT'] == 'yes') {
                $filteredActions = CommonService::filterNonNullValues($submissionData['SPIRRT']['PERSONAL_CORRECTIVE_ACTIONS_FOLLOWUP'] ?? []);
                if (!empty($filteredActions)) {
                    $newCorrectiveActions['PERSONAL_CORRECTIVE_ACTIONS_FOLLOWUP'] = $filteredActions;
                }
            }
            if (!empty($submissionData['PHYSICAL']['ENABLE_PHYSICAL_IMM_CORACT']) && $submissionData['PHYSICAL']['ENABLE_PHYSICAL_IMM_CORACT'] == 'yes') {
                $filteredActions = CommonService::filterNonNullValues($submissionData['PHYSICAL']['PHYSICAL_CORRECTIVE_ACTIONS_IMMEDIATE'] ?? []);
                if (!empty($filteredActions)) {
                    $newCorrectiveActions['PHYSICAL_CORRECTIVE_ACTIONS_IMMEDIATE'] = $filteredActions;
                }
            }
            if (!empty($submissionData['PHYSICAL']['ENABLE_PHYSICAL_FUP_CORACT']) && $submissionData['PHYSICAL']['ENABLE_PHYSICAL_FUP_CORACT'] == 'yes') {
                $filteredActions = CommonService::filterNonNullValues($submissionData['PHYSICAL']['PHYSICAL_CORRECTIVE_ACTIONS_FOLLOWUP'] ?? []);
                if (!empty($filteredActions)) {
                    $newCorrectiveActions['PHYSICAL_CORRECTIVE_ACTIONS_FOLLOWUP'] = $filteredActions;
                }
            }
            if (!empty($submissionData['SAFETY']['ENABLE_SAFETY_IMM_CORACT']) && $submissionData['SAFETY']['ENABLE_SAFETY_IMM_CORACT'] == 'yes') {
                $filteredActions = CommonService::filterNonNullValues($submissionData['SAFETY']['SAFETY_CORRECTIVE_ACTIONS_IMMEDIATE'] ?? []);
                if (!empty($filteredActions)) {
                    $newCorrectiveActions['SAFETY_CORRECTIVE_ACTIONS_IMMEDIATE'] = $filteredActions;
                }
            }
            if (!empty($submissionData['SAFETY']['ENABLE_SAFETY_FUP_CORACT']) && $submissionData['SAFETY']['ENABLE_SAFETY_FUP_CORACT'] == 'yes') {
                $filteredActions = CommonService::filterNonNullValues($submissionData['SAFETY']['SAFETY_CORRECTIVE_ACTIONS_FOLLOWUP'] ?? []);
                if (!empty($filteredActions)) {
                    $newCorrectiveActions['SAFETY_CORRECTIVE_ACTIONS_FOLLOWUP'] = $filteredActions;
                }
            }
            if (!empty($submissionData['PRETEST']['ENABLE_PRETEST_IMM_CORACT']) && $submissionData['PRETEST']['ENABLE_PRETEST_IMM_CORACT'] == 'yes') {
                $filteredActions = CommonService::filterNonNullValues($submissionData['PRETEST']['PRETEST_CORRECTIVE_ACTIONS_IMMEDIATE'] ?? []);
                if (!empty($filteredActions)) {
                    $newCorrectiveActions['PRETEST_CORRECTIVE_ACTIONS_IMMEDIATE'] = $filteredActions;
                }
            }
            if (!empty($submissionData['PRETEST']['ENABLE_PRETEST_FUP_CORACT']) && $submissionData['PRETEST']['ENABLE_PRETEST_FUP_CORACT'] == 'yes') {
                $filteredActions = CommonService::filterNonNullValues($submissionData['PRETEST']['PRETEST_CORRECTIVE_ACTIONS_FOLLOWUP'] ?? []);
                if (!empty($filteredActions)) {
                    $newCorrectiveActions['PRETEST_CORRECTIVE_ACTIONS_FOLLOWUP'] = $filteredActions;
                }
            }
            if (!empty($submissionData['TEST']['ENABLE_TEST_IMM_CORACT']) && $submissionData['TEST']['ENABLE_TEST_IMM_CORACT'] == 'yes') {
                $filteredActions = CommonService::filterNonNullValues($submissionData['TEST']['TEST_CORRECTIVE_ACTIONS_IMMEDIATE'] ?? []);
                if (!empty($filteredActions)) {
                    $newCorrectiveActions['TEST_CORRECTIVE_ACTIONS_IMMEDIATE'] = $filteredActions;
                }
            }
            if (!empty($submissionData['TEST']['ENABLE_TEST_FUP_CORACT']) && $submissionData['TEST']['ENABLE_TEST_FUP_CORACT'] == 'yes') {
                $filteredActions = CommonService::filterNonNullValues($submissionData['TEST']['TEST_CORRECTIVE_ACTIONS_FOLLOWUP'] ?? []);
                if (!empty($filteredActions)) {
                    $newCorrectiveActions['TEST_CORRECTIVE_ACTIONS_FOLLOWUP'] = $filteredActions;
                }
            }
            if (!empty($submissionData['POSTTEST']['ENABLE_POSTTEST_IMM_CORACT']) && $submissionData['POSTTEST']['ENABLE_POSTTEST_IMM_CORACT'] == 'yes') {
                $filteredActions = CommonService::filterNonNullValues($submissionData['POSTTEST']['POSTTEST_CORRECTIVE_ACTIONS_IMMEDIATE'] ?? []);
                if (!empty($filteredActions)) {
                    $newCorrectiveActions['POSTTEST_CORRECTIVE_ACTIONS_IMMEDIATE'] = $filteredActions;
                }
            }
            if (!empty($submissionData['POSTTEST']['ENABLE_POSTTEST_FUP_CORACT']) && $submissionData['POSTTEST']['ENABLE_POSTTEST_FUP_CORACT'] == 'yes') {
                $filteredActions = CommonService::filterNonNullValues($submissionData['POSTTEST']['POSTTEST_CORRECTIVE_ACTIONS_FOLLOWUP'] ?? []);
                if (!empty($filteredActions)) {
                    $newCorrectiveActions['POSTTEST_CORRECTIVE_ACTIONS_FOLLOWUP'] = $filteredActions;
                }
            }
            if (!empty($submissionData['EQA']['ENABLE_EQA_IMM_CORACT']) && $submissionData['EQA']['ENABLE_EQA_IMM_CORACT'] == 'yes') {
                $filteredActions = CommonService::filterNonNullValues($submissionData['EQA']['EQA_CORRECTIVE_ACTIONS_IMMEDIATE'] ?? []);
                if (!empty($filteredActions)) {
                    $newCorrectiveActions['EQA_CORRECTIVE_ACTIONS_IMMEDIATE'] = $filteredActions;
                }
            }
            if (!empty($submissionData['EQA']['ENABLE_EQA_FUP_CORACT']) && $submissionData['EQA']['ENABLE_EQA_FUP_CORACT'] == 'yes') {
                $filteredActions = CommonService::filterNonNullValues($submissionData['EQA']['EQA_CORRECTIVE_ACTIONS_FOLLOWUP'] ?? []);
                if (!empty($filteredActions)) {
                    $newCorrectiveActions['EQA_CORRECTIVE_ACTIONS_FOLLOWUP'] = $filteredActions;
                }
            }
            if (!empty($submissionData['RTRI_SECTION']['ENABLE_RTRI_IMM_CORACT']) && $submissionData['RTRI_SECTION']['ENABLE_RTRI_IMM_CORACT'] == 'yes') {
                $filteredActions = CommonService::filterNonNullValues($submissionData['RTRI_SECTION']['RTRI_CORRECTIVE_ACTIONS_IMMEDIATE'] ?? []);
                if (!empty($filteredActions)) {
                    $newCorrectiveActions['RTRI_CORRECTIVE_ACTIONS_IMMEDIATE'] = $filteredActions;
                }
            }
            if (!empty($submissionData['RTRI_SECTION']['ENABLE_RTRI_FUP_CORACT']) && $submissionData['RTRI_SECTION']['ENABLE_RTRI_FUP_CORACT'] == 'yes') {
                $filteredActions = CommonService::filterNonNullValues($submissionData['RTRI_SECTION']['RTRI_CORRECTIVE_ACTIONS_FOLLOWUP'] ?? []);
                if (!empty($filteredActions)) {
                    $newCorrectiveActions['RTRI_CORRECTIVE_ACTIONS_FOLLOWUP'] = $filteredActions;
                }
            }

            if (!empty($newCorrectiveActions)) {
                // If new corrective action fields are present, store them
                $data['correctiveaction'] = json_encode($newCorrectiveActions);
            } elseif (!empty($correctiveActions) && isset($correctiveActions[$submissionData['meta']['instanceID']])) {
                // Otherwise, fallback to old corrective actions
                $data['correctiveaction'] = json_encode($correctiveActions[$submissionData['meta']['instanceID']]);
            } else {
                $data['correctiveaction'] = json_encode([]);
            }

            $data['sitephoto'] = $submissionData['sitephoto'] ?? null;
            $data['sitephoto2'] = $submissionData['sitephoto2'] ?? null;
            $data['Latitude'] = $submissionData['lab_geopoint']["coordinates"][1] ?? null;
            $data['Longitude'] = $submissionData['lab_geopoint']["coordinates"][0] ?? null;
            $data['Altitude'] = $submissionData['lab_geopoint']["coordinates"][2] ?? null;
            $data['Accuracy'] = $submissionData['lab_geopoint']["properties"]['accuracy'] ?? null;
            $data['auditorSignature'] = $submissionData['auditorSignature'] ?? null;
            $data['instanceID'] = $submissionData['meta']['instanceID'];
            $data['instanceName'] = $submissionData['meta']['instanceName'];

            $data['DO_SURVEILLANCE'] = $submissionData['DO_SURVEILLANCE'];
            if (!empty($submissionData['SURVEILLANCE_STUDY_PROTOCOL'])) {
                $data['S0_Q_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL']['S0_Q_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY'];
                $data['S0_C_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL']['S0_C_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY'];
                $data['S0_Q_2_COUNSELORS_FOLLOWING_PROTOCOL'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL']['S0_Q_2_COUNSELORS_FOLLOWING_PROTOCOL'];
                $data['S0_C_2_COUNSELORS_FOLLOWING_PROTOCOL'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL']['S0_C_2_COUNSELORS_FOLLOWING_PROTOCOL'];
                $data['S0_Q_3_TESTS_RECORDED_RECENCY'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL']['S0_Q_3_TESTS_RECORDED_RECENCY'];
                $data['S0_C_3_TESTS_RECORDED_RECENCY'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL']['S0_C_3_TESTS_RECORDED_RECENCY'];
                $data['S0_Q_4_PROCESS_DOCUMENTED'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL']['S0_Q_4_PROCESS_DOCUMENTED'];
                $data['S0_C_4_PROCESS_DOCUMENTED'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL']['S0_C_4_PROCESS_DOCUMENTED'];
                $data['S0_Q_5_RESULTS_RETURNED_IN_TWO_WEEKS'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL']['S0_Q_5_RESULTS_RETURNED_IN_TWO_WEEKS'];
                $data['S0_C_5_RESULTS_RETURNED_IN_TWO_WEEKS'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL']['S0_C_5_RESULTS_RETURNED_IN_TWO_WEEKS'];
                $data['S0_Q_6_PROTOCOL_VIOLATION_DOCUMENTED'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL']['S0_Q_6_PROTOCOL_VIOLATION_DOCUMENTED'];
                $data['S0_C_6_PROTOCOL_VIOLATION_DOCUMENTED'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL']['S0_C_6_PROTOCOL_VIOLATION_DOCUMENTED'];
                $data['S0_Q_7_DOCUMENTING_PROTOCOL_ERRORS'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL']['S0_Q_7_DOCUMENTING_PROTOCOL_ERRORS'];
                $data['S0_C_7_DOCUMENTING_PROTOCOL_ERRORS'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL']['S0_C_7_DOCUMENTING_PROTOCOL_ERRORS'];
            }
            if (!empty($submissionData['SURVEILLANCE_STUDY_PROTOCOL_INDICATORS'])) {
                $data['D0_N_1_DIAGNOSED_HIV_ABOVE_15'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL_INDICATORS']['D0_N_1_DIAGNOSED_HIV_ABOVE_15'];
                $data['D0_D_1_DIAGNOSED_HIV_ABOVE_15'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL_INDICATORS']['D0_D_1_DIAGNOSED_HIV_ABOVE_15'];
                $data['D0_S_1_DIAGNOSED_HIV_ABOVE_15'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL_INDICATORS']['D0_S_1_DIAGNOSED_HIV_ABOVE_15'];
                $data['D0_N_2_CANDIDATE_SCREENED_FOR_PARTICIPATION'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL_INDICATORS']['D0_N_2_CANDIDATE_SCREENED_FOR_PARTICIPATION'];
                $data['D0_D_2_CANDIDATE_SCREENED_FOR_PARTICIPATION'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL_INDICATORS']['D0_D_2_CANDIDATE_SCREENED_FOR_PARTICIPATION'];
                $data['D0_S_2_CANDIDATE_SCREENED_FOR_PARTICIPATION'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL_INDICATORS']['D0_S_2_CANDIDATE_SCREENED_FOR_PARTICIPATION'];
                $data['D0_N_3_ELIGIBLE_DURING_REVIEW_PERIOD'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL_INDICATORS']['D0_N_3_ELIGIBLE_DURING_REVIEW_PERIOD'];
                $data['D0_D_3_ELIGIBLE_DURING_REVIEW_PERIOD'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL_INDICATORS']['D0_D_3_ELIGIBLE_DURING_REVIEW_PERIOD'];
                $data['D0_S_3_ELIGIBLE_DURING_REVIEW_PERIOD'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL_INDICATORS']['D0_S_3_ELIGIBLE_DURING_REVIEW_PERIOD'];
                $data['D0_N_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL_INDICATORS']['D0_N_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD'];
                $data['D0_D_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL_INDICATORS']['D0_D_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD'];
                $data['D0_S_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL_INDICATORS']['D0_S_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD'];
                $data['D0_N_5_DOCUMENTED_AND_REFUSED'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL_INDICATORS']['D0_N_5_DOCUMENTED_AND_REFUSED'];
                $data['D0_D_5_DOCUMENTED_AND_REFUSED'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL_INDICATORS']['D0_D_5_DOCUMENTED_AND_REFUSED'];
                $data['D0_S_5_DOCUMENTED_AND_REFUSED'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL_INDICATORS']['D0_S_5_DOCUMENTED_AND_REFUSED'];
                $data['D0_N_6_PARTICIAPANTS_ENROLLED_IN_RTRI'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL_INDICATORS']['D0_N_6_PARTICIAPANTS_ENROLLED_IN_RTRI'];
                $data['D0_D_6_PARTICIAPANTS_ENROLLED_IN_RTRI'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL_INDICATORS']['D0_D_6_PARTICIAPANTS_ENROLLED_IN_RTRI'];
                $data['D0_S_6_PARTICIAPANTS_ENROLLED_IN_RTRI'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL_INDICATORS']['D0_S_6_PARTICIAPANTS_ENROLLED_IN_RTRI'];
                $data['D0_N_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL_INDICATORS']['D0_N_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI'];
                $data['D0_D_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL_INDICATORS']['D0_D_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI'];
                $data['D0_S_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL_INDICATORS']['D0_S_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI'];
                $data['D0_N_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL_INDICATORS']['D0_N_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI'];
                $data['D0_D_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL_INDICATORS']['D0_D_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI'];
                $data['D0_S_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI'] = $submissionData['SURVEILLANCE_STUDY_PROTOCOL_INDICATORS']['D0_S_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI'];
            }
            $data['status'] = $approveStatus;

            try {

                $sql = new Sql($this->adapter);

                $insert = $sql->insert('spi_form_v_6');
                if (isset($data['testingpointname']) && trim($data['testingpointname']) == "") {
                    $data['testingpointname'] = $data['testingpointtype'];
                }

                $data['instanceID'] = isset($data['instanceID']) ? $data['instanceID'] : "";
                $data['instanceName'] = isset($data['instanceName']) ? $data['instanceName'] : "";

                $par = array(
                    'token' => $projectId . ':' . $formId,
                    'uuid' => $data['uuid'],
                    'content' => $data['content'],
                    'formId' => $data['formId'],
                    'formVersion' => $data['formVersion'],
                    'meta-instance-id' => $data['meta-instance-id'],
                    'meta-model-version' => $data['meta-model-version'],
                    'meta-ui-version' => $data['meta-ui-version'],
                    'meta-submission-date' => $data['meta-submission-date'],
                    'meta-is-complete' => $data['meta-is-complete'],
                    'meta-date-marked-as-complete' => $data['meta-date-marked-as-complete'],
                    'start' => $data['start'],
                    'end' => $data['end'],
                    'today' => $data['today'] ?? null,
                    'deviceid' => $data['deviceid'] ?? null,
                    'subscriberid' => $data['subscriberid'] ?? null,
                    'text_image' => $data['text_image'] ?? null,
                    'info1' => $data['info1'] ?? null,
                    'info2' => $data['info2'] ?? null,
                    'assesmentofaudit' => $data['assesmentofaudit'] ?? null,
                    'auditEndTime' => $data['auditEndTime'] ?? null,
                    'auditStartTime' => $data['auditStartTime'] ?? null,
                    'auditroundno' => $data['auditroundno'] ?? null,
                    'facilityname' => $data['facilityname'] ?? null,
                    'facilityid' => $data['facilityid'] ?? null,
                    'district' => $data['district'] ?? null,
                    'testingpointname' => $data['testingpointname'] ?? null,
                    'testingpointtype' => $data['testingpointtype'] ?? null,
                    'testingpointtype_other' => $data['testingpointtype_other'] ?? null,
                    'physicaladdress' => $data['physicaladdress'] ?? null,
                    'level' => $data['level'] ?? null,
                    'level_other' => $data['level_other'] ?? null,

                    'affiliation' => $data['affiliation'],
                    'affiliation_other' => $data['affiliation_other'] ?? null,
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
                    'name_auditor2' => $data['nameOfAuditor2'] ?? null,
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
                    'TEST_SCORE' => $data['TEST_SCORE'] ?? null,
                    'TEST_DISPLAY' => $data['TEST_DISPLAY'] ?? null,
                    'TESTPHOTO' => $data['TESTPHOTO'] ?? null,
                    'POST_Q_6_1_STANDARDIZED_HIV_REGISTER' => $data['POST_Q_6_1_STANDARDIZED_HIV_REGISTER'] ?? null,
                    'POST_C_6_1_STANDARDIZED_HIV_REGISTER' => $data['POST_C_6_1_STANDARDIZED_HIV_REGISTER'] ?? null,
                    'POST_Q_6_2_ELEMENTS_CAPTURED_CORRECTLY' => $data['POST_Q_6_2_ELEMENTS_CAPTURED_CORRECTLY'] ?? null,
                    'POST_C_6_2_ELEMENTS_CAPTURED_CORRECTLY' => $data['POST_C_6_2_ELEMENTS_CAPTURED_CORRECTLY'] ?? null,
                    'POST_Q_6_3_PAGE_TOTAL_SUMMARY' => $data['POST_Q_6_3_PAGE_TOTAL_SUMMARY'] ?? null,
                    'POST_C_6_3_PAGE_TOTAL_SUMMARY' => $data['POST_C_6_3_PAGE_TOTAL_SUMMARY'] ?? null,
                    'POST_Q_6_4_INVALID_TEST_RESULT_RECORDED' => $data['POST_Q_6_4_INVALID_TEST_RESULT_RECORDED'] ?? null,
                    'POST_C_6_4_INVALID_TEST_RESULT_RECORDED' => $data['POST_C_6_4_INVALID_TEST_RESULT_RECORDED'] ?? null,
                    'POST_Q_6_5_APPROPRIATE_STEPS_TAKEN' => $data['POST_Q_6_5_APPROPRIATE_STEPS_TAKEN'] ?? null,
                    'POST_C_6_5_APPROPRIATE_STEPS_TAKEN' => $data['POST_C_6_5_APPROPRIATE_STEPS_TAKEN'] ?? null,
                    'POST_Q_6_6_REGISTERS_REVIEWED' => $data['POST_Q_6_6_REGISTERS_REVIEWED'] ?? null,
                    'POST_C_6_6_REGISTERS_REVIEWED' => $data['POST_C_6_6_REGISTERS_REVIEWED'] ?? null,
                    'POST_Q_6_7_DOCUMENTS_SECURELY_KEPT' => $data['POST_Q_6_7_DOCUMENTS_SECURELY_KEPT'] ?? null,
                    'POST_C_6_7_DOCUMENTS_SECURELY_KEPT' => $data['POST_C_6_7_DOCUMENTS_SECURELY_KEPT'] ?? null,
                    'POST_Q_6_8_REGISTER_SECURE_LOCATION' => $data['POST_Q_6_8_REGISTER_SECURE_LOCATION'] ?? null,
                    'POST_C_6_8_REGISTER_SECURE_LOCATION' => $data['POST_C_6_8_REGISTER_SECURE_LOCATION'] ?? null,
                    'POST_Q_6_9_REGISTERS_PROPERLY_LABELED' => $data['POST_Q_6_9_REGISTERS_PROPERLY_LABELED'] ?? null,
                    'POST_C_6_9_REGISTERS_PROPERLY_LABELED' => $data['POST_C_6_9_REGISTERS_PROPERLY_LABELED'] ?? null,
                    'POST_SCORE' => $data['POST_SCORE'] ?? null,
                    'POST_DISPLAY' => $data['POST_DISPLAY'] ?? null,
                    'POSTTESTPHOTO' => $data['POSTTESTPHOTO'] ?? null,
                    'EQA_Q_7_1_PT_ENROLLMENT' => $data['EQA_Q_7_1_PT_ENROLLMENT'] ?? null,
                    'EQA_C_7_1_PT_ENROLLMENT' => $data['EQA_C_7_1_PT_ENROLLMENT'] ?? null,
                    'EQA_Q_7_2_TESTING_EQAPT_SAMPLES' => $data['EQA_Q_7_2_TESTING_EQAPT_SAMPLES'] ?? null,
                    'EQA_C_7_2_TESTING_EQAPT_SAMPLES' => $data['EQA_C_7_2_TESTING_EQAPT_SAMPLES'] ?? null,
                    'EQA_Q_7_3_REVIEW_BEFORE_SUBMISSION' => $data['EQA_Q_7_3_REVIEW_BEFORE_SUBMISSION'] ?? null,
                    'EQA_C_7_3_REVIEW_BEFORE_SUBMISSION' => $data['EQA_C_7_3_REVIEW_BEFORE_SUBMISSION'] ?? null,
                    'EQA_Q_7_4_FEEDBACK_RECEIVED_REVIEWED' => $data['EQA_Q_7_4_FEEDBACK_RECEIVED_REVIEWED'] ?? null,
                    'EQA_C_7_4_FEEDBACK_RECEIVED_REVIEWED' => $data['EQA_C_7_4_FEEDBACK_RECEIVED_REVIEWED'] ?? null,
                    'EQA_Q_7_5_IMPLEMENT_CORRECTIVE_ACTION' => $data['EQA_Q_7_5_IMPLEMENT_CORRECTIVE_ACTION'] ?? null,
                    'EQA_C_7_5_IMPLEMENT_CORRECTIVE_ACTION' => $data['EQA_C_7_5_IMPLEMENT_CORRECTIVE_ACTION'] ?? null,
                    'EQA_Q_7_6_RECEIVE_PERIODIC_VISITS' => $data['EQA_Q_7_6_RECEIVE_PERIODIC_VISITS'] ?? null,
                    'EQA_C_7_6_RECEIVE_PERIODIC_VISITS' => $data['EQA_C_7_6_RECEIVE_PERIODIC_VISITS'] ?? null,
                    'EQA_Q_7_7_FEEDBACK_PROVIDED_DOCUMENTED' => $data['EQA_Q_7_7_FEEDBACK_PROVIDED_DOCUMENTED'] ?? null,
                    'EQA_C_7_7_FEEDBACK_PROVIDED_DOCUMENTED' => $data['EQA_C_7_7_FEEDBACK_PROVIDED_DOCUMENTED'] ?? null,
                    'EQA_Q_7_8_TESTERS_RETRAINED_IN_VISITS' => $data['EQA_Q_7_8_TESTERS_RETRAINED_IN_VISITS'] ?? null,
                    'EQA_C_7_8_TESTERS_RETRAINED_IN_VISITS' => $data['EQA_C_7_8_TESTERS_RETRAINED_IN_VISITS'] ?? null,
                    'performrtritesting' => $data['performrtritesting'] ?? null,
                    'EQA_SCORE' => $data['EQA_SCORE'] ?? null,
                    'EQA_DISPLAY' => $data['EQA_DISPLAY'] ?? null,
                    'EQAPHOTO' => $data['EQAPHOTO'] ?? null,
                    'RTRI_Q_8_1_TESTERS_RECEIVED_RTRI_TRAINING' => $data['RTRI_Q_8_1_TESTERS_RECEIVED_RTRI_TRAINING'] ?? null,
                    'RTRI_C_8_1_TESTERS_RECEIVED_RTRI_TRAINING' => $data['RTRI_C_8_1_TESTERS_RECEIVED_RTRI_TRAINING'] ?? null,
                    'RTRI_Q_8_2_TESTERS_DEMONSTRATED_COMPETENCY' => $data['RTRI_Q_8_2_TESTERS_DEMONSTRATED_COMPETENCY'] ?? null,
                    'RTRI_C_8_2_TESTERS_DEMONSTRATED_COMPETENCY' => $data['RTRI_C_8_2_TESTERS_DEMONSTRATED_COMPETENCY'] ?? null,
                    'RTRI_Q_8_3_JOBAIDS_READILY_AVAILABLE' => $data['RTRI_Q_8_3_JOBAIDS_READILY_AVAILABLE'] ?? null,
                    'RTRI_C_8_3_JOBAIDS_READILY_AVAILABLE' => $data['RTRI_C_8_3_JOBAIDS_READILY_AVAILABLE'] ?? null,
                    'RTRI_Q_8_4_SUFFICIENT_SUPPLY_AVAILABLE' => $data['RTRI_Q_8_4_SUFFICIENT_SUPPLY_AVAILABLE'] ?? null,
                    'RTRI_C_8_4_SUFFICIENT_SUPPLY_AVAILABLE' => $data['RTRI_C_8_4_SUFFICIENT_SUPPLY_AVAILABLE'] ?? null,
                    'RTRI_Q_8_5_RTRI_KIT_STORAGE' => $data['RTRI_Q_8_5_RTRI_KIT_STORAGE'] ?? null,
                    'RTRI_C_8_5_RTRI_KIT_STORAGE' => $data['RTRI_C_8_5_RTRI_KIT_STORAGE'] ?? null,
                    'RTRI_Q_8_6_RTRI_TESTING_PROCEDURE_FOLLOWED' => $data['RTRI_Q_8_6_RTRI_TESTING_PROCEDURE_FOLLOWED'] ?? null,
                    'RTRI_C_8_6_RTRI_TESTING_PROCEDURE_FOLLOWED' => $data['RTRI_C_8_6_RTRI_TESTING_PROCEDURE_FOLLOWED'] ?? null,
                    'RTRI_Q_8_7_RTRI_TESTING_RESULTS_DOCUMENTED' => $data['RTRI_Q_8_7_RTRI_TESTING_RESULTS_DOCUMENTED'] ?? null,
                    'RTRI_C_8_7_RTRI_TESTING_RESULTS_DOCUMENTED' => $data['RTRI_C_8_7_RTRI_TESTING_RESULTS_DOCUMENTED'] ?? null,
                    'RTRI_Q_8_8_QC_ROUTINELY_USED' => $data['RTRI_Q_8_8_QC_ROUTINELY_USED'] ?? null,
                    'RTRI_C_8_8_QC_ROUTINELY_USED' => $data['RTRI_C_8_8_QC_ROUTINELY_USED'] ?? null,
                    'RTRI_Q_8_9_QC_RESULTS_RECORDED' => $data['RTRI_Q_8_9_QC_RESULTS_RECORDED'] ?? null,
                    'RTRI_C_8_9_QC_RESULTS_RECORDED' => $data['RTRI_C_8_9_QC_RESULTS_RECORDED'] ?? null,
                    'RTRI_Q_8_10_INCORRECT_QC_DOCUMENTED' => $data['RTRI_Q_8_10_INCORRECT_QC_DOCUMENTED'] ?? null,
                    'RTRI_C_8_10_INCORRECT_QC_DOCUMENTED' => $data['RTRI_C_8_10_INCORRECT_QC_DOCUMENTED'] ?? null,
                    'RTRI_Q_8_11_INVALID_RTRI_RESULTS' => $data['RTRI_Q_8_11_INVALID_RTRI_RESULTS'] ?? null,
                    'RTRI_C_8_11_INVALID_RTRI_RESULTS' => $data['RTRI_C_8_11_INVALID_RTRI_RESULTS'] ?? null,
                    'RTRI_SCORE' => $data['RTRI_SCORE'] ?? null,
                    'RTRI_DISPLAY' => $data['RTRI_DISPLAY'] ?? null,
                    'RTRIPHOTO' => $data['RTRIPHOTO'] ?? null,
                    'AuditRequiredScore' => $data['AuditRequiredScore'] ?? null,
                    'FINAL_AUDIT_SCORE' => $data['FINAL_AUDIT_SCORE'] ?? null,
                    'MAX_AUDIT_SCORE' => $data['MAX_AUDIT_SCORE'] ?? null,
                    'AUDIT_SCORE_PERCENTAGE' => $data['AUDIT_SCORE_PERCENTAGE'] ?? null,
                    'AUDIT_SCORE_PERCENTAGE_ROUNDED' => $data['AUDIT_SCORE_PERCENTAGE_ROUNDED'] ?? $data['AUDIT_SCORE_PERCANTAGE_ROUNDED'] ?? null,
                    'staffaudited' => $data['staffaudited'],
                    'durationaudit' => $data['durationaudit'],
                    'personincharge' => $data['personincharge'],
                    'endofsurvey' => $data['endofsurvey'],
                    'sitecode' => $data['sitecode'],

                    'info5' => $data['info5'],
                    'info6' => $data['info6'],
                    'info10' => $data['info10'],
                    'info11' => $data['info11'],
                    'SUMMARY_NOT_AVL' => $data['SUMMARY_NOT_AVL'] ?? null,
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
                    'correctiveaction' => $data['correctiveaction'] ?? null,
                    'sitephoto' => $data['sitephoto'] ?? null,
                    'sitephoto2' => $data['sitephoto2'] ?? null,
                    'Latitude' => $data['Latitude'] ?? null,
                    'Longitude' => $data['Longitude'] ?? null,
                    'Altitude' => $data['Altitude'] ?? null,
                    'Accuracy' => $data['Accuracy'] ?? null,
                    'auditorSignature' => $data['auditorSignature'] ?? null,
                    'instanceID' => $data['instanceID'] ?? null,
                    'instanceName' => $data['instanceName'] ?? null,

                    'DO_SURVEILLANCE' => $data['DO_SURVEILLANCE'] ?? null,
                    'S0_Q_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY' => $data['S0_Q_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY'] ?? null,
                    'S0_C_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY' => $data['S0_C_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY'] ?? null,
                    'S0_Q_2_COUNSELORS_FOLLOWING_PROTOCOL' => $data['S0_Q_2_COUNSELORS_FOLLOWING_PROTOCOL'] ?? null,
                    'S0_C_2_COUNSELORS_FOLLOWING_PROTOCOL' => $data['S0_C_2_COUNSELORS_FOLLOWING_PROTOCOL'] ?? null,
                    'S0_Q_3_TESTS_RECORDED_RECENCY' => $data['S0_Q_3_TESTS_RECORDED_RECENCY'] ?? null,
                    'S0_C_3_TESTS_RECORDED_RECENCY' => $data['S0_C_3_TESTS_RECORDED_RECENCY'] ?? null,
                    'S0_Q_4_PROCESS_DOCUMENTED' => $data['S0_Q_4_PROCESS_DOCUMENTED'] ?? null,
                    'S0_C_4_PROCESS_DOCUMENTED' => $data['S0_C_4_PROCESS_DOCUMENTED'] ?? null,
                    'S0_Q_5_RESULTS_RETURNED_IN_TWO_WEEKS' => $data['S0_Q_5_RESULTS_RETURNED_IN_TWO_WEEKS'] ?? null,
                    'S0_C_5_RESULTS_RETURNED_IN_TWO_WEEKS' => $data['S0_C_5_RESULTS_RETURNED_IN_TWO_WEEKS'] ?? null,
                    'S0_Q_6_PROTOCOL_VIOLATION_DOCUMENTED' => $data['S0_Q_6_PROTOCOL_VIOLATION_DOCUMENTED'] ?? null,
                    'S0_C_6_PROTOCOL_VIOLATION_DOCUMENTED' => $data['S0_C_6_PROTOCOL_VIOLATION_DOCUMENTED'] ?? null,
                    'S0_Q_7_DOCUMENTING_PROTOCOL_ERRORS' => $data['S0_Q_7_DOCUMENTING_PROTOCOL_ERRORS'] ?? null,
                    'S0_C_7_DOCUMENTING_PROTOCOL_ERRORS' => $data['S0_C_7_DOCUMENTING_PROTOCOL_ERRORS'] ?? null,
                    'D0_N_1_DIAGNOSED_HIV_ABOVE_15' => $data['D0_N_1_DIAGNOSED_HIV_ABOVE_15'] ?? null,
                    'D0_D_1_DIAGNOSED_HIV_ABOVE_15' => $data['D0_D_1_DIAGNOSED_HIV_ABOVE_15'] ?? null,
                    'D0_S_1_DIAGNOSED_HIV_ABOVE_15' => $data['D0_S_1_DIAGNOSED_HIV_ABOVE_15'] ?? null,
                    'D0_N_2_CANDIDATE_SCREENED_FOR_PARTICIPATION' => $data['D0_N_2_CANDIDATE_SCREENED_FOR_PARTICIPATION'] ?? null,
                    'D0_D_2_CANDIDATE_SCREENED_FOR_PARTICIPATION' => $data['D0_D_2_CANDIDATE_SCREENED_FOR_PARTICIPATION'] ?? null,
                    'D0_S_2_CANDIDATE_SCREENED_FOR_PARTICIPATION' => $data['D0_S_2_CANDIDATE_SCREENED_FOR_PARTICIPATION'] ?? null,
                    'D0_N_3_ELIGIBLE_DURING_REVIEW_PERIOD' => $data['D0_N_3_ELIGIBLE_DURING_REVIEW_PERIOD'] ?? null,
                    'D0_D_3_ELIGIBLE_DURING_REVIEW_PERIOD' => $data['D0_D_3_ELIGIBLE_DURING_REVIEW_PERIOD'] ?? null,
                    'D0_S_3_ELIGIBLE_DURING_REVIEW_PERIOD' => $data['D0_S_3_ELIGIBLE_DURING_REVIEW_PERIOD'] ?? null,
                    'D0_N_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD' => $data['D0_N_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD'] ?? null,
                    'D0_D_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD' => $data['D0_D_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD'] ?? null,
                    'D0_S_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD' => $data['D0_S_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD'] ?? null,
                    'D0_N_5_DOCUMENTED_AND_REFUSED' => $data['D0_N_5_DOCUMENTED_AND_REFUSED'] ?? null,
                    'D0_D_5_DOCUMENTED_AND_REFUSED' => $data['D0_D_5_DOCUMENTED_AND_REFUSED'] ?? null,
                    'D0_S_5_DOCUMENTED_AND_REFUSED' => $data['D0_S_5_DOCUMENTED_AND_REFUSED'] ?? null,
                    'D0_N_6_PARTICIAPANTS_ENROLLED_IN_RTRI' => $data['D0_N_6_PARTICIAPANTS_ENROLLED_IN_RTRI'] ?? null,
                    'D0_D_6_PARTICIAPANTS_ENROLLED_IN_RTRI' => $data['D0_D_6_PARTICIAPANTS_ENROLLED_IN_RTRI'] ?? null,
                    'D0_S_6_PARTICIAPANTS_ENROLLED_IN_RTRI' => $data['D0_S_6_PARTICIAPANTS_ENROLLED_IN_RTRI'] ?? null,
                    'D0_N_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI' => $data['D0_N_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI'] ?? null,
                    'D0_D_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI' => $data['D0_D_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI'] ?? null,
                    'D0_S_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI' => $data['D0_S_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI'] ?? null,
                    'D0_N_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI' => $data['D0_N_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI'] ?? null,
                    'D0_D_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI' => $data['D0_D_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI'] ?? null,
                    'D0_S_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI' => $data['D0_S_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI'] ?? null,
                    'status' => $approveStatus ?? null,
                    'central_project_id' => $projectId ?? null,
                    'central_form_id' => $formId ?? null,
                );
                $dbAdapter = $this->adapter;
                $sQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
                    ->where(array('spiv6.uuid' => $data["uuid"]));
                $sQueryStr = $sql->buildSqlString($sQuery);
                $sResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
                $currentFormId = null;
                if (empty($sResult) || $data["uuid"] != $sResult["uuid"]) {
                    $insert->values($par);
                    $selectString = $sql->buildSqlString($insert);
                    /** @var \Laminas\Db\Adapter\Driver\ResultInterface $results */
                    $results = $dbAdapter->query($selectString, $dbAdapter::QUERY_MODE_EXECUTE);
                    $currentFormId = $results->getGeneratedValue();
                } else {
                    //Lost manually updated data for auto syn update
                    //$this->update($par, array('uuid' => $data["uuid"]));
                }
                if (!empty($currentFormId) && $currentFormId > 0 && $approveStatus == 'approved') {
                    $facilityDb = new SpiRtFacilitiesTable($dbAdapter);
                    $facilityDb->addFacilityBasedOnForm($currentFormId, 6);
                }
                // }
            } catch (\Exception $e) {
                error_log($e->getMessage());
                error_log($e->getTraceAsString());
            }
        }
    }

    public function getPerformanceV6($params)
    {
        $loginContainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
            ->columns(array('assesmentofaudit', 'AUDIT_SCORE_PERCENTAGE'))
            ->where(array('spiv6.status' => 'approved'));
        if (isset($params['fieldName']) && trim($params['fieldName']) != '') {
            $sQuery = $sQuery->where(array($params['fieldName'] => $params['val']));
        }

        if (isset($params['roundno']) && $params['roundno'] != '') {
            $sQuery = $sQuery->where('spiv6.auditroundno IN ("' . implode('", "', $params['roundno']) . '")');
        }
        $startDate = '';
        $endDate = '';
        if (isset($params['dateRange']) && ($params['dateRange'] != "")) {
            $dateField = explode(" ", $params['dateRange']);
            if (isset($dateField[0]) && trim($dateField[0]) != "") {
                $startDate = CommonService::isoDateFormat($dateField[0]);
            }
            if (isset($dateField[2]) && trim($dateField[2]) != "") {
                $endDate = CommonService::isoDateFormat($dateField[2]);
            }
        }
        if (trim($startDate) != "" && trim($endDate) != "") {
            $sQuery = $sQuery->where(array("spiv6.assesmentofaudit >='" . $startDate . "'", "spiv6.assesmentofaudit <='" . $endDate . "'"));
        }

        if (isset($params['testPoint']) && trim($params['testPoint']) != '') {

            if (trim($params['testPoint']) != 'other') {
                $sQuery = $sQuery->where("spiv6.testingpointtype='" . $params['testPoint'] . "'");
            } else {
                $sQuery = $sQuery->where("spiv6.testingpointtype_other='" . $params['testPointName'] . "'");
            }
        }
        if (isset($params['level']) && $params['level'] != '') {
            $sQuery = $sQuery->where("spiv6.level='" . $params['level'] . "'");
        }
        if (isset($params['affiliation']) && $params['affiliation'] != '') {
            $sQuery = $sQuery->where("spiv6.affiliation='" . $params['affiliation'] . "'");
        }

        if (isset($params['scoreLevel']) && $params['scoreLevel'] != '') {
            if ($params['scoreLevel'] == 0) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE < 40");
            } elseif ($params['scoreLevel'] == 1) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 59");
            } elseif ($params['scoreLevel'] == 2) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 79");
            } elseif ($params['scoreLevel'] == 3) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 89");
            } elseif ($params['scoreLevel'] == 4) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 90");
            }
        }

        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where('spiv6.token IN ("' . implode('", "', $loginContainer->token) . '")');
        }
        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType != '') {
            // Mapping exists
            if ($loginContainer->userMappingType === 'country' && $params['country'] == '') {
                // User mapped by country: show mapped country data and all related provinces and districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('country', 'province', 'district'))
                    ->where('f.country IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'province' && $params['province'] == '') {
                // User mapped by province: show only mapped provinces
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('province'))
                    ->where('f.province IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'district' && $params['district'] == '') {
                // User mapped by district: show only mapped districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('district'))
                    ->where('f.district IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            }
        }
        if ((isset($params['country']) && $params['country'] != '') || (isset($params['province']) && $params['province'] != '') || (isset($params['district']) && $params['district'] != '')) {
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('country', 'province', 'district'));

            if (isset($params['country']) && $params['country'] != '' && is_array($params['country'])) {
                $sQuery = $sQuery->where('f.country IN ("' . implode('", "', $params['country']) . '")');
            }
            if (isset($params['province']) && $params['province'] != '' && is_array($params['province'])) {
                $sQuery = $sQuery->where('f.province IN ("' . implode('", "', $params['province']) . '")');
            }
            if (isset($params['district']) && $params['district'] != '' && is_array($params['district'])) {
                $sQuery = $sQuery->where('f.district IN ("' . implode('", "', $params['district']) . '")');
            }
        }

        $sQueryStr = $sql->buildSqlString($sQuery);
        // echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        $auditScore = 0;
        $levelZero = [];
        $levelOne = [];
        $levelTwo = [];
        $levelThree = [];
        $levelFour = [];
        $minDate = null;
        $maxDate = null;
        foreach ($rResult as $aRow) {
            $auditDate = $aRow['assesmentofaudit'];
            // If $minDate is null or the current $auditDate is smaller, update $minDate
            if ($minDate === null || $auditDate < $minDate) {
                $minDate = $auditDate;
            }

            // If $maxDate is null or the current $auditDate is larger, update $maxDate
            if ($maxDate === null || $auditDate > $maxDate) {
                $maxDate = $auditDate;
            }
            $scorePer = round($aRow['AUDIT_SCORE_PERCENTAGE']);
            $row = [];
            $auditScore += $aRow['AUDIT_SCORE_PERCENTAGE'];
            if (isset($scorePer) && $scorePer < 40) {
                $level = 0;
                $levelZero[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
            } elseif (isset($scorePer) && $scorePer >= 40 && $scorePer < 60) {
                $level = 1;
                $levelOne[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
            } elseif (isset($scorePer) && $scorePer >= 60 && $scorePer < 80) {
                $level = 2;
                $levelTwo[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
            } elseif (isset($scorePer) && $scorePer >= 80 && $scorePer < 90) {
                $level = 3;
                $levelThree[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
            } elseif (isset($scorePer) && $scorePer >= 90) {
                $level = 4;
                $levelFour[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
            }
        }
        $output['totalDataPoints'] = count($rResult);
        $output['oldestDate'] = $minDate;
        $output['newestDate'] = $maxDate;
        $output['level0'] = count($levelZero);
        $output['level1'] = count($levelOne);
        $output['level2'] = count($levelTwo);
        $output['level3'] = count($levelThree);
        $output['level4'] = count($levelFour);
        $result[] = $output;
        return $result;
    }

    public function getPerformanceLast30DaysV6($params)
    {
        $loginContainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);

        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime('-30 days', strtotime($startDate)));

        if (isset($params['dateRange']) && ($params['dateRange'] != "")) {
            $dateField = explode(" ", $params['dateRange']);
            if (isset($dateField[2]) && trim($dateField[2]) != "") {
                $startDate = CommonService::isoDateFormat($dateField[2]);
            }
            if (isset($dateField[0]) && trim($dateField[0]) != "") {
                $endDate = CommonService::isoDateFormat($dateField[0]);
            }
        }

        $sQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
            ->columns(array(
                'newestDate' => new Expression("'$startDate'"),
                'oldestDate' => new Expression("'$endDate'"),
                'totalDataPoints' => new Expression("COUNT(*)"),
                'level0' => new Expression("SUM(IF((AUDIT_SCORE_PERCENTAGE) < 40, 1,0))"),
                'level1' => new Expression("SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 40 and (AUDIT_SCORE_PERCENTAGE) < 60, 1,0))"),
                'level2' => new Expression("SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 60 and (AUDIT_SCORE_PERCENTAGE) < 80, 1,0))"),
                'level3' => new Expression("SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 80 and (AUDIT_SCORE_PERCENTAGE) < 90, 1,0))"),
                'level4' => new Expression("SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 90, 1,0))"),
            ))
            ->where("spiv6.status='approved'")
            ->where("(`assesmentofaudit` BETWEEN '" . $startDate . "' - INTERVAL DATEDIFF('" . $startDate . "','" . $endDate . "') DAY AND '" . $startDate . "')");
        //    $sQuery = $sQuery->where("(`assesmentofaudit` BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE())");
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where('spiv6.token IN ("' . implode('", "', $loginContainer->token) . '")');
        }
        if (isset($params['auditRndNo']) && $params['auditRndNo'] != '') {
            $sQuery = $sQuery->where("spiv6.auditroundno='" . $params['auditRndNo'] . "'");
        }
        if (isset($params['testPoint']) && trim($params['testPoint']) != '') {
            $sQuery = $sQuery->where("spiv6.testingpointtype='" . $params['testPoint'] . "'");
            if (trim($params['testPoint']) != 'other') {
                $sQuery = $sQuery->where("spiv6.testingpointname='" . $params['testPointName'] . "'");
            } else {
                $sQuery = $sQuery->where("spiv6.testingpointtype_other='" . $params['testPointName'] . "'");
            }
        }
        if (isset($params['level']) && $params['level'] != '') {
            $sQuery = $sQuery->where("spiv6.level='" . $params['level'] . "'");
        }
        if (isset($params['affiliation']) && $params['affiliation'] != '') {
            $sQuery = $sQuery->where("spiv6.affiliation='" . $params['affiliation'] . "'");
        }

        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType != '') {
            if ($loginContainer->userMappingType === 'country' && (isset($params['country']) && $params['country'] == '')) {
                // User mapped by country: show mapped country data and all related provinces and districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('country', 'province', 'district'))
                    ->where('f.country IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'province' && (isset($params['province']) && $params['province'] == '')) {
                // User mapped by province: show only mapped provinces
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('province'))
                    ->where('f.province IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'district' && (isset($params['district']) && $params['district'] == '')) {
                // User mapped by district: show only mapped districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('district'))
                    ->where('f.district IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            }
        }
        if ((isset($params['country']) && $params['country'] != '') || (isset($params['province']) && $params['province'] != '') || (isset($params['district']) && $params['district'] != '')) {
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('country', 'province', 'district'));

            // Apply filters dynamically
            if (isset($params['country']) && $params['country'] != '') {
                $sQuery = $sQuery->where('f.country IN (' . $params['country'] . ')');
            }
            if (isset($params['province']) && $params['province'] != '') {
                $sQuery = $sQuery->where('f.province IN (' . $params['province'] . ')');
            }
            if (isset($params['district']) && $params['district'] != '') {
                $sQuery = $sQuery->where('f.district IN (' . $params['district'] . ')');
            }
        }

        if (isset($params['scoreLevel']) && $params['scoreLevel'] != '') {
            if ($params['scoreLevel'] == 0) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE < 40");
            } elseif ($params['scoreLevel'] == 1) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 59");
            } elseif ($params['scoreLevel'] == 2) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 79");
            } elseif ($params['scoreLevel'] == 3) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 89");
            } elseif ($params['scoreLevel'] == 4) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 90");
            }
        }

        $sQueryStr = $sql->buildSqlString($sQuery);
        //die($sQueryStr);
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();

        return $rResult;
    }

    public function getPerformanceLast180DaysV6()
    {
        $loginContainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $today = date('Y-m-d');
        $last180Date = date('Y-m-d', strtotime('-180 days', strtotime($today)));
        $sQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
            ->columns(array(
                'newestDate' => new Expression("'$today'"),
                'oldestDate' => new Expression("'$last180Date'"),
                'totalDataPoints' => new Expression("COUNT(*)"),
                'level0' => new Expression("SUM(IF((AUDIT_SCORE_PERCENTAGE) < 40, 1,0))"),
                'level1' => new Expression("SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 40 and (AUDIT_SCORE_PERCENTAGE) < 60, 1,0))"),
                'level2' => new Expression("SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 60 and (AUDIT_SCORE_PERCENTAGE) < 80, 1,0))"),
                'level3' => new Expression("SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 80 and (AUDIT_SCORE_PERCENTAGE) < 90, 1,0))"),
                'level4' => new Expression("SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 90, 1,0))"),
            ))
            ->where(array('spiv6.status' => 'approved'))
            ->where("(`assesmentofaudit` BETWEEN CURDATE() - INTERVAL 180 DAY AND CURDATE())");
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where('spiv6.token IN ("' . implode('", "', $loginContainer->token) . '")');
        }
        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType != '') {
            if ($loginContainer->userMappingType === 'country') {
                // User mapped by country: show mapped country data and all related provinces and districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('country', 'province', 'district'))
                    ->where('f.country IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'province') {
                // User mapped by province: show only mapped provinces
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('province'))
                    ->where('f.province IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'district') {
                // User mapped by district: show only mapped districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('district'))
                    ->where('f.district IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            }
        }
        $sQueryStr = $sql->buildSqlString($sQuery);
        // echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();

        return $rResult;
    }

    public function getAllSubmissionsV6($sortOrder = 'DESC', $limit = 100)
    {
        $loginContainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
            ->where('spiv6.status != "deleted"')
            ->limit($limit)
            ->order(array("status DESC", "id $sortOrder"));
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where('spiv6.token IN ("' . implode('", "', $loginContainer->token) . '")');
        }
        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType != '') {
            if ($loginContainer->userMappingType === 'country') {
                // User mapped by country: show mapped country data and all related provinces and districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('country', 'province', 'district'))
                    ->where('f.country IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'province') {
                // User mapped by province: show only mapped provinces
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('province'))
                    ->where('f.province IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'district') {
                // User mapped by district: show only mapped districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('district'))
                    ->where('f.district IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            }
        }
        $sQueryStr = $sql->buildSqlString($sQuery);
        // echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        return $rResult;
    }

    public function getAllApprovedTestingVolumeV6($params)
    {
        $loginContainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'));
        // ->order(array("client_tested_HIV_PM DESC"));
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where('spiv6.token IN ("' . implode('", "', $loginContainer->token) . '")');
        }
        $startDate = '';
        $endDate = '';
        if (isset($params['dateRange']) && ($params['dateRange'] != "")) {
            $dateField = explode(" ", $params['dateRange']);
            if (isset($dateField[0]) && trim($dateField[0]) != "") {
                $startDate = CommonService::isoDateFormat($dateField[0]);
            }
            if (isset($dateField[2]) && trim($dateField[2]) != "") {
                $endDate = CommonService::isoDateFormat($dateField[2]);
            }
        }
        if (trim($startDate) != "" && trim($endDate) != "") {
            $sQuery = $sQuery->where(array("spiv6.assesmentofaudit >='" . $startDate . "'", "spiv6.assesmentofaudit <='" . $endDate . "'"));
        }

        if (isset($params['auditRndNo']) && $params['auditRndNo'] != '') {
            $sQuery = $sQuery->where("spiv6.auditroundno='" . $params['auditRndNo'] . "'");
        }
        if (isset($params['testPoint']) && trim($params['testPoint']) != '') {
            $sQuery = $sQuery->where("spiv6.testingpointtype='" . $params['testPoint'] . "'");
            // if(isset($params['testPointName']) && trim($params['testPointName'])!= ''){
            if (trim($params['testPoint']) != 'other') {
                $sQuery = $sQuery->where("spiv6.testingpointname='" . $params['testPointName'] . "'");
            } else {
                $sQuery = $sQuery->where("spiv6.testingpointtype_other='" . $params['testPointName'] . "'");
            }
            // }
        }
        if (isset($params['level']) && $params['level'] != '') {
            $sQuery = $sQuery->where("spiv6.level='" . $params['level'] . "'");
        }
        if (isset($params['affiliation']) && $params['affiliation'] != '') {
            $sQuery = $sQuery->where("spiv6.affiliation='" . $params['affiliation'] . "'");
        }

        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType != '') {
            if ($loginContainer->userMappingType === 'country' && (isset($params['country']) && $params['country'] == '')) {
                // User mapped by country: show mapped country data and all related provinces and districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('country', 'province', 'district'))
                    ->where('f.country IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'province' && (isset($params['country']) && $params['province'] == '')) {
                // User mapped by province: show only mapped provinces
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('province'))
                    ->where('f.province IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'district' && (isset($params['country']) && $params['district'] == '')) {
                // User mapped by district: show only mapped districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('district'))
                    ->where('f.district IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            }
        }
        if ((isset($params['country']) && $params['country'] != '') || (isset($params['province']) && $params['province'] != '') || (isset($params['district']) && $params['district'] != '')) {
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('country', 'province', 'district'));

            // Apply filters dynamically
            if (isset($params['country']) && $params['country'] != '' && is_array($params['country'])) {
                $sQuery = $sQuery->where('f.country IN ("' . implode('", "', $params['country']) . '")');
            }
            if (isset($params['province']) && $params['province'] != '' && is_array($params['province'])) {
                $sQuery = $sQuery->where('f.province IN ("' . implode('", "', $params['province']) . '")');
            }
            if (isset($params['district']) && $params['district'] != '' && is_array($params['district'])) {
                $sQuery = $sQuery->where('f.district IN ("' . implode('", "', $params['district']) . '")');
            }
        }

        if (isset($params['scoreLevel']) && $params['scoreLevel'] != '') {
            if ($params['scoreLevel'] == 0) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE < 40");
            } elseif ($params['scoreLevel'] == 1) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 59");
            } elseif ($params['scoreLevel'] == 2) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 79");
            } elseif ($params['scoreLevel'] == 3) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 89");
            } elseif ($params['scoreLevel'] == 4) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 90");
            }
        }
        $sQueryStr = $sql->buildSqlString($sQuery);
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        return $rResult;
    }

    public function fetchAllSubmissionsDetails($parameters, $acl)
    {
        $loginContainer = new Container('credo');
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */
        $queryContainer = new Container('query');
        $aColumns = array('spiv6.id', 'facilityname', 'auditroundno', "DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", 'testingpointtype', 'affiliation', 'AUDIT_SCORE_PERCENTAGE', 'spiv6.status');
        $orderColumns = array('spiv6.id', 'facilityname', 'auditroundno', 'assesmentofaudit', 'testingpointtype', 'affiliation', 'AUDIT_SCORE_PERCENTAGE', 'spiv3.status');

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
            for ($i = 0; $i < (int) $parameters['iSortingCols']; $i++) {
                if ($parameters['bSortable_' . (int) $parameters['iSortCol_' . $i]] == "true") {
                    $sOrder .= $orderColumns[(int) $parameters['iSortCol_' . $i]] . " " . ($parameters['sSortDir_' . $i]) . ",";
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
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' OR ";
                    } else {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' ";
                    }
                }
                $sWhereSub .= ")";
            }
            $sWhere .= $sWhereSub;
        }
        /* Individual column filtering */
        $counter = count($aColumns);

        /* Individual column filtering */
        for ($i = 0; $i < $counter; $i++) {
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
        $sql = new Sql($this->adapter);
        $startDate = "";
        $endDate = "";
        if (isset($parameters['dateRange']) && ($parameters['dateRange'] != "")) {
            $dateField = explode(" ", $parameters['dateRange']);
            //print_r($proceed_date);die;
            if (isset($dateField[0]) && trim($dateField[0]) != "") {
                $startDate = CommonService::isoDateFormat($dateField[0]);
            }
            if (isset($dateField[2]) && trim($dateField[2]) != "") {
                $endDate = CommonService::isoDateFormat($dateField[2]);
            }
        }

        $sQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
            ->where('spiv6.status != "deleted"');
        // ->join(array('f'=>'spi_rt_3_facilities'),
        //                 'f.id=spiv3.facility',
        //                         array('province','district'),'left');

        if ($parameters['auditRndNo'] != '') {
            $sQuery = $sQuery->where("spiv6.auditroundno='" . $parameters['auditRndNo'] . "'");
        }
        if (trim($startDate) != "" && trim($endDate) != "") {
            $sQuery = $sQuery->where(array("spiv6.assesmentofaudit >='" . $startDate . "'", "spiv6.assesmentofaudit <='" . $endDate . "'"));
        }
        // if(isset($parameters['testPoint']) && trim($parameters['testPoint'])!=''){
        //     $sQuery = $sQuery->where("spiv6.testingpointtype='".$parameters['testPoint']."'");
        //     if(isset($parameters['testPointName']) && trim($parameters['testPointName'])!= ''){
        //          if(trim($parameters['testPoint'])!= 'other'){
        //             $sQuery = $sQuery->where("spiv6.testingpointname='".$parameters['testPointName']."'");
        //          }else{
        //             $sQuery = $sQuery->where("spiv6.testingpointtype_other='".$parameters['testPointName']."'");
        //          }
        //     }
        // }
        if (isset($parameters['testPoint']) && trim($parameters['testPoint']) != '') {

            if (trim($parameters['testPoint']) != 'other') {
                $sQuery = $sQuery->where("spiv6.testingpointtype='" . $parameters['testPoint'] . "'");
            } else {
                $sQuery = $sQuery->where("spiv6.testingpointtype_other='" . $parameters['testPointName'] . "'");
            }
        }
        if ($parameters['affiliation'] != '') {
            $sQuery = $sQuery->where("spiv6.affiliation='" . $parameters['affiliation'] . "'");
        }

        if (isset($parameters['showAudits']) && $parameters['showAudits'] != '') {
            if ($parameters['showAudits'] == 'yes') {
                // Query where 'audit_printed' is set to 'yes'
                $sQuery = $sQuery->where("JSON_UNQUOTE(JSON_EXTRACT(spiv6.form_metadata, '$.audit_printed')) = 'yes'");
            } elseif ($parameters['showAudits'] == 'no') {
                // Query where 'audit_printed' is not 'yes' or not set
                $sQuery = $sQuery->where(
                    "(
                        JSON_UNQUOTE(JSON_EXTRACT(spiv6.form_metadata, '$.audit_printed')) != 'yes'
                        OR JSON_EXTRACT(spiv6.form_metadata, '$.audit_printed') IS NULL
                    )"
                );
            }
        }

        if (isset($parameters['scoreLevel']) && $parameters['scoreLevel'] != '') {
            if ($parameters['scoreLevel'] == 0) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE < 40");
            } elseif ($parameters['scoreLevel'] == 1) {
                $sQuery = $sQuery->where("ROUND(spiv6.AUDIT_SCORE_PERCENTAGE) >= 40 AND ROUND(spiv6.AUDIT_SCORE_PERCENTAGE) <= 59");
            } elseif ($parameters['scoreLevel'] == 2) {
                $sQuery = $sQuery->where("ROUND(spiv6.AUDIT_SCORE_PERCENTAGE) >= 60 AND ROUND(spiv6.AUDIT_SCORE_PERCENTAGE) <= 79");
            } elseif ($parameters['scoreLevel'] == 3) {
                $sQuery = $sQuery->where("ROUND(spiv6.AUDIT_SCORE_PERCENTAGE) >= 80 AND ROUND(spiv6.AUDIT_SCORE_PERCENTAGE) <= 89");
            } elseif ($parameters['scoreLevel'] == 4) {
                $sQuery = $sQuery->where("ROUND(spiv6.AUDIT_SCORE_PERCENTAGE) >= 90");
            }
        }

        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType != '') {
            // Mapping exists
            if ($loginContainer->userMappingType === 'country' && $parameters['country'] == '') {
                // User mapped by country: show mapped country data and all related provinces and districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility OR f.facility_name=spiv6.facilityname', array('country', 'province', 'district'))
                    ->where('f.country IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'province' && $parameters['province'] == '') {
                // User mapped by province: show only mapped provinces
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility OR f.facility_name=spiv6.facilityname', array('province'))
                    ->where('f.province IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'district' && $parameters['district'] == '') {
                // User mapped by district: show only mapped districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility OR f.facility_name=spiv6.facilityname', array('district'))
                    ->where('f.district IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            }
        }
        if (isset($parameters['country']) && $parameters['country'] != '') {
            $sQuery = $sQuery->join(array('f1' => 'spi_rt_3_facilities'), 'f1.id=spiv6.facility OR f1.facility_name=spiv6.facilityname', array('country'))
                ->where('f1.country IN (' . $parameters['country'] . ')');
        }
        if (isset($parameters['province']) && $parameters['province'] != '') {
            $sQuery = $sQuery->join(array('f2' => 'spi_rt_3_facilities'), 'f2.id=spiv6.facility OR f2.facility_name=spiv6.facilityname', array('province'))
                ->where('f2.province IN (' . $parameters['province'] . ')');
        }
        if (isset($parameters['district']) && $parameters['district'] != '') {
            $sQuery = $sQuery->join(array('f3' => 'spi_rt_3_facilities'), 'f3.id=spiv6.facility OR f3.facility_name=spiv6.facilityname', array('district'))
                ->where('f3.district IN (' . $parameters['district'] . ')');
        }


        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where('spiv6.token IN ("' . implode('", "', $loginContainer->token) . '")');
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

        $sQueryStr = $sql->buildSqlString($sQuery); // Get the string of the Sql, instead of the Select-instance
        //echo $sQueryStr;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->buildSqlString($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $tQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
            ->where('spiv6.status != "deleted"');
        //  ->join(array('f'=>'spi_rt_3_facilities'),
        //                 'f.id=spiv3.facility',
        //                 array('province','district'),'left');
        if ($parameters['auditRndNo'] != '') {
            $tQuery = $tQuery->where("spiv6.auditroundno='" . $parameters['auditRndNo'] . "'");
        }
        if (trim($startDate) != "" && trim($endDate) != "") {
            $tQuery = $tQuery->where(array("spiv6.assesmentofaudit >='" . $startDate . "'", "spiv6.assesmentofaudit <='" . $endDate . "'"));
        }
        /*if (isset($parameters['testPoint']) && trim($parameters['testPoint']) != '' && strtolower(trim($parameters['testPoint'])) == 'other') {
            $tQuery = $tQuery->where("spiv6.testingpointtype='" . $parameters['testPoint'] . "'");
        }

        if (strtolower(trim($parameters['testPoint'])) == 'other') {
            $tQuery = $tQuery->where("spiv6.testingpointtype_other='" . $parameters['testPointName'] . "'");
        }*/
        if (isset($parameters['testPoint']) && trim($parameters['testPoint']) != '') {

            if (trim($parameters['testPoint']) != 'other') {
                $tQuery = $tQuery->where("spiv6.testingpointtype='" . $parameters['testPoint'] . "'");
            } else {
                $tQuery = $tQuery->where("spiv6.testingpointtype_other='" . $parameters['testPointName'] . "'");
            }
        }
        if ($parameters['affiliation'] != '') {
            $tQuery = $tQuery->where("spiv6.affiliation='" . $parameters['affiliation'] . "'");
        }

        if (isset($parameters['scoreLevel']) && $parameters['scoreLevel'] != '') {
            if ($parameters['scoreLevel'] == 0) {
                $tQuery = $tQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE < 40");
            } elseif ($parameters['scoreLevel'] == 1) {
                $tQuery = $tQuery->where("ROUND(spiv6.AUDIT_SCORE_PERCENTAGE) >= 40 AND ROUND(spiv6.AUDIT_SCORE_PERCENTAGE) <= 59");
            } elseif ($parameters['scoreLevel'] == 2) {
                $tQuery = $tQuery->where("ROUND(spiv6.AUDIT_SCORE_PERCENTAGE) >= 60 AND ROUND(spiv6.AUDIT_SCORE_PERCENTAGE) <= 79");
            } elseif ($parameters['scoreLevel'] == 3) {
                $tQuery = $tQuery->where("ROUND(spiv6.AUDIT_SCORE_PERCENTAGE) >= 80 AND ROUND(spiv6.AUDIT_SCORE_PERCENTAGE) <= 89");
            } elseif ($parameters['scoreLevel'] == 4) {
                $tQuery = $tQuery->where("ROUND(spiv6.AUDIT_SCORE_PERCENTAGE) >= 90");
            }
        }
        if (!empty($loginContainer->token)) {
            $tQuery = $tQuery->where('spiv6.token IN ("' . implode('", "', $loginContainer->token) . '")');
        }
        $tQueryStr = $sql->buildSqlString($tQuery); // Get the string of the Sql, instead of the Select-instance
        $tResult = $dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);
        $iTotal = count($tResult);
        $output = array(
            "sEcho" => (int) $parameters['sEcho'],
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array(),
        );

        $role = $loginContainer->roleCode;
        $downloadPdfAction = (bool) $acl->isAllowed($role, 'Application\Controller\SpiV6Controller', 'download-pdf');

        $approveStatusAction = (bool) $acl->isAllowed($role, 'Application\Controller\SpiV6Controller', 'approve-status');

        $auditScore = 0;
        $levelZero = [];
        $levelOne = [];
        $levelTwo = [];
        $levelThree = [];
        $levelFour = [];
        foreach ($rResult as $aRow) {
            $row = [];
            $approve = '';
            $downloadPdf = "";
            $aRow['AUDIT_SCORE_PERCENTAGE'] = (float) ($aRow['AUDIT_SCORE_PERCENTAGE'] ?? 0);
            $scorePer = round($aRow['AUDIT_SCORE_PERCENTAGE']);
            $auditScore += $aRow['AUDIT_SCORE_PERCENTAGE'];
            if (isset($scorePer) && $scorePer < 40) {
                $levelZero[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
            } elseif (isset($scorePer) && $scorePer >= 40 && $scorePer < 60) {
                $levelOne[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
            } elseif (isset($scorePer) && $scorePer >= 60 && $scorePer < 80) {
                $levelTwo[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
            } elseif (isset($scorePer) && $scorePer >= 80 && $scorePer < 90) {
                $levelThree[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
            } elseif (isset($scorePer) && $scorePer >= 90) {
                $levelFour[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
            }
            $row['DT_RowId'] = $aRow['id'];
            $level = isset($aRow['level_other']) && $aRow['level_other'] != "" ? " - " . $aRow['level_other'] : '';
            $row[] = '';
            $row[] = $aRow['facilityname'];

            $row[] = $aRow['auditroundno'];
            $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
            //$row[] = (isset($aRow['testingpointname']) && $aRow['testingpointname'] != "" ? $aRow['testingpointname'] : $aRow['testingpointtype']);
            $row[] = $aRow['testingpointtype'];
            $row[] = $aRow['affiliation'];
            $row[] = round($aRow['AUDIT_SCORE_PERCENTAGE'] ?? 0, 2);
            $row[] = ucwords($aRow['status']);
            //$print = '<a href="/spi-v3/print/' . $aRow['id'] . '" target="_blank" style="white-space:nowrap;"><i class="fa fa-print"></i> Print</a>';
            if ($aRow['status'] == 'pending' && $approveStatusAction) {
                $approve = '<br><a href="javascript:void(0);" onclick="approveStatus(' . $aRow['id'] . ')"  style="white-space:nowrap;"><i class="fa fa-check"></i>  Approve</a>';
            }

            if ($downloadPdfAction) {
                $downloadPdf = '<br><a href="javascript:void(0);" onclick="downloadPdf(' . $aRow['id'] . ')" style="white-space:nowrap;"><i class="fa fa-download"></i> PDF</a>';
            }
            //$pending = '<br><a href="/spi-v3/edit/' . $aRow['id'] . '" style="white-space:nowrap;"><i class="fa fa-pencil"></i> Edit</a>';
            $row[] = $approve . " " . $downloadPdf;
            $row[] = $aRow['PERSONAL_SCORE'];
            $row[] = $aRow['PHYSICAL_SCORE'];
            $row[] = $aRow['SAFETY_SCORE'];
            $row[] = $aRow['PRETEST_SCORE'];
            $row[] = $aRow['TEST_SCORE'];
            $row[] = $aRow['POST_SCORE'];
            $row[] = $aRow['EQA_SCORE'];
            $row[] = $aRow['RTRI_SCORE'];

            $t = (float) $aRow['PERSONAL_SCORE'] + (float) $aRow['PHYSICAL_SCORE'] + (float) $aRow['SAFETY_SCORE'] + (float) $aRow['PRETEST_SCORE'] + (float) $aRow['TEST_SCORE'] + (float) $aRow['POST_SCORE'] + (float) $aRow['EQA_SCORE'];
            if (trim($aRow['performrtritesting']) == 'Yes') {
                $t += (float) $aRow['RTRI_SCORE'];
            }
            $row[] = $t . "(" . round($aRow['AUDIT_SCORE_PERCENTAGE'], 2) . ")";
            $output['aaData'][] = $row;
        }
        //get earliest date
        $eQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))->columns(array('assesmentofaudit'))->order('assesmentofaudit ASC');
        $eQueryStr = $sql->buildSqlString($eQuery); // Get the string of the Sql, instead of the Select-instance
        $eResult = $dbAdapter->query($eQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
        //get duplicate value
        $dpResult = $dbAdapter->query("SELECT `meta-instance-id`, COUNT(*) c FROM spi_form_v_6 GROUP BY `meta-instance-id` HAVING c > 1", $dbAdapter::QUERY_MODE_EXECUTE)->toArray();

        $output['avgAuditScore'] = (count($rResult) > 0) ? round($auditScore / count($rResult), 2) : 0;
        $output['levelZeroCount'] = count($levelZero);
        $output['levelOneCount'] = count($levelOne);
        $output['levelTwoCount'] = count($levelTwo);
        $output['levelThreeCount'] = count($levelThree);
        $output['levelFourCount'] = count($levelFour);
        $output['eDate'] = $eResult['assesmentofaudit'];
        $output['duplicate'] = count($dpResult);
        return $output;
    }

    public function fetchAllV6DuplicateSubmissionsDetails()
    {
        // echo "sj";die;
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        return $dbAdapter->query("SELECT `meta-instance-id`,`id`,`facilityname`,`status`,`auditroundno`,`AUDIT_SCORE_PERCENTAGE`,`affiliation`,`level`,`assesmentofaudit`,`testingpointtype`, COUNT(*) c FROM spi_form_v_6 GROUP BY `meta-instance-id` HAVING c > 1", $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    }

    public function fetchAllSubmissionsDatas($parameters, $acl)
    {
        $loginContainer = new Container('credo');
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */

        $aColumns = array('facilityname', 'auditroundno', 'assesmentofaudit', 'testingpointtype', 'level', 'affiliation', 'AUDIT_SCORE_PERCENTAGE', 'status');
        $orderColumns = array('facilityname', 'auditroundno', 'assesmentofaudit', 'testingpointtype', 'level', 'affiliation', 'AUDIT_SCORE_PERCENTAGE', 'status');

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
            for ($i = 0; $i < (int) $parameters['iSortingCols']; $i++) {
                if ($parameters['bSortable_' . (int) $parameters['iSortCol_' . $i]] == "true") {
                    $sOrder .= $orderColumns[(int) $parameters['iSortCol_' . $i]] . " " . ($parameters['sSortDir_' . $i]) . ",";
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
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' OR ";
                    } else {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' ";
                    }
                }
                $sWhereSub .= ")";
            }
            $sWhere .= $sWhereSub;
        }
        /* Individual column filtering */
        $counter = count($aColumns);

        /* Individual column filtering */
        for ($i = 0; $i < $counter; $i++) {
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
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
            ->where('spiv6.status != "deleted"');
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where('spiv6.token IN ("' . implode('", "', $loginContainer->token) . '")');
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

        $sQueryStr = $sql->buildSqlString($sQuery); // Get the string of the Sql, instead of the Select-instance
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->buildSqlString($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $tQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
            ->where('spiv6.status != "deleted"');
        if (!empty($loginContainer->token)) {
            $tQuery = $tQuery->where('spiv6.token IN ("' . implode('", "', $loginContainer->token) . '")');
        }
        $tQueryStr = $sql->buildSqlString($tQuery); // Get the string of the Sql, instead of the Select-instance
        $tResult = $dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);
        $iTotal = count($tResult);
        $output = array(
            "sEcho" => (int) $parameters['sEcho'],
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array(),
        );
        $role = $loginContainer->roleCode;
        $update = (bool) $acl->isAllowed($role, 'Application\Controller\SpiV6Controller', 'edit');

        $delete = (bool) $acl->isAllowed($role, 'Application\Controller\SpiV6Controller', 'delete');

        $downloadPdfAction = (bool) $acl->isAllowed($role, 'Application\Controller\SpiV6Controller', 'download-pdf');

        foreach ($rResult as $aRow) {
            $row = [];
            $downloadPdf = "";
            $edit = "";
            $remove = "";
            $row['DT_RowId'] = $aRow['id'];
            $row[] = $aRow['facilityname'];
            $row[] = $aRow['auditroundno'];
            $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
            $row[] = $aRow['testingpointtype'];
            $row[] = $aRow['level'];
            $row[] = $aRow['affiliation'];
            $row[] = round($aRow['AUDIT_SCORE_PERCENTAGE'], 2);
            $row[] = ucwords($aRow['status']);
            if ($downloadPdfAction) {
                //$downloadPdf = '<a href="javascript:void(0);" onclick="downloadPdf('.$aRow['id'].')" style="white-space:nowrap;"><i class="fa fa-download"></i> PDF</a>';
            }
            if ($update) {
                $edit = '&nbsp;<a href="/spi-v6/edit/' . $aRow['id'] . '" style="white-space:nowrap;"><i class="fa fa-pencil"></i> Edit</a>';
            }
            if ($delete) {
                $remove = '&nbsp;<a href="javascript:void(0);" onclick="deleteAudit(' . $aRow['id'] . ');" style="white-space:nowrap;"><i class="fa fa-times"></i> Delete</a>';
            }
            $row[] = $edit . " " . $downloadPdf . " " . $remove;
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function fetchPendingFacilityNames()
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
            ->where(array('spiv6.status' => 'pending'));
        $sQueryStr = $sql->buildSqlString($sQuery);
        return $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    }

    public function getFormData($id, $pdfDowload = 'no')
    {
        $loginContainer = new Container('credo');
        $username = $loginContainer->login;
        $dbAdapter = $this->adapter;
        $trackTable = new EventLogTable($dbAdapter);
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
            ->where(array('spiv6.id' => $id));
        $sQueryStr = $sql->buildSqlString($sQuery);
        /** @var \Laminas\Db\Adapter\Driver\ResultInterface $sResult */
        $sResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
        if ($sResult) {
            $searchFacility = false;
            if (trim($sResult->facility) != '' || trim($sResult->facilityid) != '' || trim($sResult->facilityname) != '') {
                //$fQuery = $sql->select()->from(array('spirt5' => 'spi_rt_5_facilities'))
                $fQuery = $sql->select()->from(array('spirt3' => 'spi_rt_3_facilities'))
                    ->columns(array('fId' => 'id', 'ffId' => 'facility_id', 'fName' => 'facility_name', 'fEmail' => 'email', 'fCPerson' => 'contact_person', 'fLatitude' => 'latitude', 'fLongitude' => 'longitude'));
                if (isset($sResult->facility) && $sResult->facility > 0) {
                    $fQuery = $fQuery->where(array("spirt3.id" => $sResult->facility));
                    $searchFacility = true;
                } elseif (isset($sResult->facilityid) && $sResult->facilityid != '') {
                    $fQuery = $fQuery->where(array("spirt3.facility_id" => $sResult->facilityid));
                    $searchFacility = true;
                } elseif (isset($sResult->facilityname) && $sResult->facilityname != '') {
                    $fQuery = $fQuery->where(array("spirt3.facility_name" => $sResult->facilityname));
                    $searchFacility = true;
                }
                if ($searchFacility) {
                    $fQueryStr = $sql->buildSqlString($fQuery);
                    $sResult['facilityInfo'] = $dbAdapter->query($fQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
                }
            }
            //if ($pdfDowload == 'yes') {
            $subject = '';
            $eventType = 'Print-PDF';
            $action = $username . ' has generated SPI RRT Audit PDF';
            $resourceName = 'Print-SPI-RRT-PDF';
            $trackTable->addEventLog($subject, $eventType, $action, $resourceName);
            //}
            if ($pdfDowload == 'yes') {
                $this->updateFormMetadata($id, 'audit_printed', 'yes', $sResult['form_metadata']);
            }
        }
        return $sResult;
    }

    public function updateFormMetadata($id, $key, $value, $formMetaData)
    {
        $existingMetadata = !empty($formMetaData)
                ? json_decode($formMetaData, true)
                : [];

        if (isset($existingMetadata[$key]) && $existingMetadata[$key] === $value) {
            return; // Skip if already set
        }

        $existingMetadata[$key] = $value;
        $updateData = ['form_metadata' => json_encode($existingMetadata)];

        $this->update($updateData, ['id' => $id]);
    }

    public function updateAuditMailSentStatus($auditIds)
    {
        $auditIdArray = explode(",", $auditIds);

        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $query = $sql->select()
                    ->from('spi_form_v_6')
                    ->columns(['id', 'form_metadata'])
                    ->where(['id' => $auditIdArray]);

        $queryStr = $sql->buildSqlString($query);
        $results = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();

        foreach ($results as $row) {
            $this->updateFormMetadata($row['id'], 'audit_email_sent', 'yes', $row['form_metadata']);
        }
    }

    public function getAuditRoundWiseDataV6($params)
    {
        $loginContainer = new Container('credo');
        //$rResult = $this->getAllSubmissions();
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
            ->order(array("id DESC"));
        if (isset($params['roundno']) && $params['roundno'] != '') {
            $sQuery = $sQuery->where('spiv6.auditroundno IN ("' . implode('", "', $params['roundno']) . '")');
        }
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where('spiv6.token IN ("' . implode('", "', $loginContainer->token) . '")');
        }
        $startDate = '';
        $endDate = '';
        if (isset($params['dateRange']) && ($params['dateRange'] != "")) {
            $dateField = explode(" ", $params['dateRange']);
            if (isset($dateField[0]) && trim($dateField[0]) != "") {
                $startDate = CommonService::isoDateFormat($dateField[0]);
            }
            if (isset($dateField[2]) && trim($dateField[2]) != "") {
                $endDate = CommonService::isoDateFormat($dateField[2]);
            }
        }
        if (trim($startDate) != "" && trim($endDate) != "") {
            $sQuery = $sQuery->where(array("spiv6.assesmentofaudit >='" . $startDate . "'", "spiv6.assesmentofaudit <='" . $endDate . "'"));
        }
        if ($params['auditRndNo'] != '') {
            $sQuery = $sQuery->where("spiv6.auditroundno='" . $params['auditRndNo'] . "'");
        }

        if (isset($params['testPoint']) && trim($params['testPoint']) != '') {

            if (isset($params['testPoint']) && trim($params['testPoint']) != '' && strtolower(trim($params['testPoint'])) == 'other') {
                $sQuery = $sQuery->where("spiv6.testingpointtype_other='" . $params['testPointName'] . "'");
            } else {
                $sQuery = $sQuery->where("spiv6.testingpointtype='" . $params['testPoint'] . "'");
            }
        }
        if (isset($params['level']) && $params['level'] != '') {
            $sQuery = $sQuery->where("spiv6.level='" . $params['level'] . "'");
        }
        if (isset($params['affiliation']) && $params['affiliation'] != '') {
            $sQuery = $sQuery->where("spiv6.affiliation='" . $params['affiliation'] . "'");
        }

        if (isset($params['scoreLevel']) && $params['scoreLevel'] != '') {
            if ($params['scoreLevel'] == 0) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE < 40");
            } elseif ($params['scoreLevel'] == 1) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 59");
            } elseif ($params['scoreLevel'] == 2) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 79");
            } elseif ($params['scoreLevel'] == 3) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 89");
            } elseif ($params['scoreLevel'] == 4) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 90");
            }
        }

        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType != '') {
            if ($loginContainer->userMappingType === 'country' && $params['country'] == '') {
                // User mapped by country: show mapped country data
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('country', 'province', 'district'))
                    ->where('f.country IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'province' && $params['province'] == '') {
                // User mapped by province: show only mapped provinces
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('province'))
                    ->where('f.province IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'district' && $params['district'] == '') {
                // User mapped by district: show only mapped districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('district'))
                    ->where('f.district IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            }
        }
        if ((isset($params['country']) && $params['country'] != '') || (isset($params['province']) && $params['province'] != '') || (isset($params['district']) && $params['district'] != '')) {
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('country', 'province', 'district'));

            if (isset($params['country']) && $params['country'] != '' && is_array($params['country'])) {
                $sQuery = $sQuery->where('f.country IN ("' . implode('", "', $params['country']) . '")');
            }
            if (isset($params['province']) && $params['province'] != '' && is_array($params['province'])) {
                $sQuery = $sQuery->where('f.province IN ("' . implode('", "', $params['province']) . '")');
            }
            if (isset($params['district']) && $params['district'] != '' && is_array($params['district'])) {
                $sQuery = $sQuery->where('f.district IN ("' . implode('", "', $params['district']) . '")');
            }
        }

        if (isset($params['fieldName']) && trim($params['fieldName']) != '') {
            $sQuery = $sQuery->where(array($params['fieldName'] => $params['val']));
        }
        $sQueryStr = $sql->buildSqlString($sQuery);
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();

        $response = [];

        foreach ($rResult as $row) {

            $response[0]['PERSONAL_SCORE'][] = $row['PERSONAL_SCORE'];
            $response[0]['PHYSICAL_SCORE'][] = $row['PHYSICAL_SCORE'];
            $response[0]['SAFETY_SCORE'][] = $row['SAFETY_SCORE'];
            $response[0]['PRETEST_SCORE'][] = $row['PRETEST_SCORE'];
            $response[0]['TEST_SCORE'][] = $row['TEST_SCORE'];
            $response[0]['POST_SCORE'][] = $row['POST_SCORE'];
            $response[0]['EQA_SCORE'][] = $row['EQA_SCORE'];
            $response[0]['RTRI_SCORE'][] = $row['RTRI_SCORE'];
        }

        $auditRoundWiseData = [];
        foreach ($response as $auditNo => $auditScores) {
            $auditRoundWiseData[$auditNo]['PERSONAL_SCORE'] = array_sum($auditScores['PERSONAL_SCORE']) / count($auditScores['PERSONAL_SCORE']);
            $auditRoundWiseData[$auditNo]['PHYSICAL_SCORE'] = array_sum($auditScores['PHYSICAL_SCORE']) / count($auditScores['PERSONAL_SCORE']);
            $auditRoundWiseData[$auditNo]['SAFETY_SCORE'] = array_sum($auditScores['SAFETY_SCORE']) / count($auditScores['PERSONAL_SCORE']);
            $auditRoundWiseData[$auditNo]['PRETEST_SCORE'] = array_sum($auditScores['PRETEST_SCORE']) / count($auditScores['PERSONAL_SCORE']);
            $auditRoundWiseData[$auditNo]['TEST_SCORE'] = array_sum($auditScores['TEST_SCORE']) / count($auditScores['PERSONAL_SCORE']);
            $auditRoundWiseData[$auditNo]['POST_SCORE'] = array_sum($auditScores['POST_SCORE']) / count($auditScores['PERSONAL_SCORE']);
            $auditRoundWiseData[$auditNo]['EQA_SCORE'] = array_sum($auditScores['EQA_SCORE']) / count($auditScores['PERSONAL_SCORE']);
            $auditRoundWiseData[$auditNo]['RTRI_SCORE'] = array_sum($auditScores['RTRI_SCORE']) / count($auditScores['PERSONAL_SCORE']);
        }
        $response = array('');
        return $auditRoundWiseData;
    }

    /*

    Get audit Performance of
    S.0    HIV-1 RECENT INFECTION SURVEILLANCE STUDY PROTOCOL

     */

    public function getAuditRoundWiseS0DataV6($params)
    {
        $loginContainer = new Container('credo');
        //$rResult = $this->getAllSubmissions();
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
            ->order(array("id DESC"));
        if (isset($params['roundno']) && $params['roundno'] != '') {
            $sQuery = $sQuery->where('spiv6.auditroundno IN ("' . implode('", "', $params['roundno']) . '")');
        }
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where('spiv6.token IN ("' . implode('", "', $loginContainer->token) . '")');
        }
        $startDate = '';
        $endDate = '';
        if (isset($params['dateRange']) && ($params['dateRange'] != "")) {
            $dateField = explode(" ", $params['dateRange']);
            if (isset($dateField[0]) && trim($dateField[0]) != "") {
                $startDate = CommonService::isoDateFormat($dateField[0]);
            }
            if (isset($dateField[2]) && trim($dateField[2]) != "") {
                $endDate = CommonService::isoDateFormat($dateField[2]);
            }
        }
        if (trim($startDate) != "" && trim($endDate) != "") {
            $sQuery = $sQuery->where(array("spiv6.assesmentofaudit >='" . $startDate . "'", "spiv6.assesmentofaudit <='" . $endDate . "'"));
        }
        if ($params['auditRndNo'] != '') {
            $sQuery = $sQuery->where("spiv6.auditroundno='" . $params['auditRndNo'] . "'");
        }

        if (isset($params['testPoint']) && trim($params['testPoint']) != '') {

            if (isset($params['testPoint']) && trim($params['testPoint']) != '' && strtolower(trim($params['testPoint'])) == 'other') {
                $sQuery = $sQuery->where("spiv6.testingpointtype_other='" . $params['testPointName'] . "'");
            } else {
                $sQuery = $sQuery->where("spiv6.testingpointtype='" . $params['testPoint'] . "'");
            }
        }
        if (isset($params['level']) && $params['level'] != '') {
            $sQuery = $sQuery->where("spiv6.level='" . $params['level'] . "'");
        }
        if (isset($params['affiliation']) && $params['affiliation'] != '') {
            $sQuery = $sQuery->where("spiv6.affiliation='" . $params['affiliation'] . "'");
        }
        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType != '') {
            if ($loginContainer->userMappingType === 'country' && $params['country'] == '') {
                // User mapped by country: show mapped country data and all related provinces and districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('country', 'province', 'district'))
                    ->where('f.country IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'province' && $params['province'] == '') {
                // User mapped by province: show only mapped provinces
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('province'))
                    ->where('f.province IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'district' && $params['district'] == '') {
                // User mapped by district: show only mapped districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('district'))
                    ->where('f.district IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            }
        }
        if ((isset($params['country']) && $params['country'] != '') || (isset($params['province']) && $params['province'] != '') || (isset($params['district']) && $params['district'] != '')) {
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('country', 'province', 'district'));

            // Apply filters dynamically
            if (isset($params['country']) && $params['country'] != '' && is_array($params['country'])) {
                $sQuery = $sQuery->where('f.country IN ("' . implode('", "', $params['country']) . '")');
            }
            if (isset($params['province']) && $params['province'] != '' && is_array($params['province'])) {
                $sQuery = $sQuery->where('f.province IN ("' . implode('", "', $params['province']) . '")');
            }
            if (isset($params['district']) && $params['district'] != '' && is_array($params['district'])) {
                $sQuery = $sQuery->where('f.district IN ("' . implode('", "', $params['district']) . '")');
            }
        }
        if (isset($params['scoreLevel']) && $params['scoreLevel'] != '') {
            if ($params['scoreLevel'] == 0) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE < 40");
            } elseif ($params['scoreLevel'] == 1) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 59");
            } elseif ($params['scoreLevel'] == 2) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 79");
            } elseif ($params['scoreLevel'] == 3) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 89");
            } elseif ($params['scoreLevel'] == 4) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 90");
            }
        }

        if (isset($params['fieldName']) && trim($params['fieldName']) != '') {
            $sQuery = $sQuery->where(array($params['fieldName'] => $params['val']));
        }
        $sQueryStr = $sql->buildSqlString($sQuery);
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();

        $responseOfSectionS0 = [];

        foreach ($rResult as $row) {
            //error_log($row['S0_Q_4_PROCESS_DOCUMENTED']);
            $responseOfSectionS0[0]['SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY'][] = (float) $row['S0_Q_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY'];
            $responseOfSectionS0[0]['COUNSELORS_FOLLOWING_PROTOCOL'][] = (float) $row['S0_Q_2_COUNSELORS_FOLLOWING_PROTOCOL'];
            $responseOfSectionS0[0]['TESTS_RECORDED_RECENCY'][] = (float) $row['S0_Q_3_TESTS_RECORDED_RECENCY'];
            $responseOfSectionS0[0]['PROCESS_DOCUMENTED'][] = (float) $row['S0_Q_4_PROCESS_DOCUMENTED'];
            $responseOfSectionS0[0]['RESULTS_RETURNED_IN_TWO_WEEKS'][] = (float) $row['S0_Q_5_RESULTS_RETURNED_IN_TWO_WEEKS'];
            $responseOfSectionS0[0]['PROTOCOL_VIOLATION_DOCUMENTED'][] = (float) $row['S0_Q_6_PROTOCOL_VIOLATION_DOCUMENTED'];
            $responseOfSectionS0[0]['DOCUMENTING_PROTOCOL_ERRORS'][] = (float) $row['S0_Q_7_DOCUMENTING_PROTOCOL_ERRORS'];
        }

        $auditRoundWiseData = [];
        foreach ($responseOfSectionS0 as $auditNo => $auditScores) {
            $auditRoundWiseData[$auditNo]['SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY'] = array_sum($auditScores['SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY']) / count($auditScores['SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY']);
            $auditRoundWiseData[$auditNo]['COUNSELORS_FOLLOWING_PROTOCOL'] = array_sum($auditScores['COUNSELORS_FOLLOWING_PROTOCOL']) / count($auditScores['COUNSELORS_FOLLOWING_PROTOCOL']);
            $auditRoundWiseData[$auditNo]['TESTS_RECORDED_RECENCY'] = array_sum($auditScores['TESTS_RECORDED_RECENCY']) / count($auditScores['TESTS_RECORDED_RECENCY']);
            $auditRoundWiseData[$auditNo]['PROCESS_DOCUMENTED'] = array_sum($auditScores['PROCESS_DOCUMENTED']) / count($auditScores['PROCESS_DOCUMENTED']);
            $auditRoundWiseData[$auditNo]['RESULTS_RETURNED_IN_TWO_WEEKS'] = array_sum($auditScores['RESULTS_RETURNED_IN_TWO_WEEKS']) / count($auditScores['RESULTS_RETURNED_IN_TWO_WEEKS']);
            $auditRoundWiseData[$auditNo]['PROTOCOL_VIOLATION_DOCUMENTED'] = array_sum($auditScores['PROTOCOL_VIOLATION_DOCUMENTED']) / count($auditScores['PROTOCOL_VIOLATION_DOCUMENTED']);
            $auditRoundWiseData[$auditNo]['DOCUMENTING_PROTOCOL_ERRORS'] = array_sum($auditScores['DOCUMENTING_PROTOCOL_ERRORS']) / count($auditScores['DOCUMENTING_PROTOCOL_ERRORS']);
        }

        //var_dump($auditRoundWiseData);die;
        $responseOfSectionS0 = array('');
        return $auditRoundWiseData;
    }

    /*

    Get audit Performance of
    D.0    HIV-1 RECENT INFECTION SURVEILLANCE USING DATA INDICATORS

     */

    public function getAuditRoundWiseD0DataV6($params)
    {
        $loginContainer = new Container('credo');
        //$rResult = $this->getAllSubmissions();
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
            ->order(array("id DESC"));
        if (isset($params['roundno']) && $params['roundno'] != '') {
            $sQuery = $sQuery->where('spiv6.auditroundno IN ("' . implode('", "', $params['roundno']) . '")');
        }
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where('spiv6.token IN ("' . implode('", "', $loginContainer->token) . '")');
        }
        $startDate = '';
        $endDate = '';
        if (isset($params['dateRange']) && ($params['dateRange'] != "")) {
            $dateField = explode(" ", $params['dateRange']);
            if (isset($dateField[0]) && trim($dateField[0]) != "") {
                $startDate = CommonService::isoDateFormat($dateField[0]);
            }
            if (isset($dateField[2]) && trim($dateField[2]) != "") {
                $endDate = CommonService::isoDateFormat($dateField[2]);
            }
        }
        if (trim($startDate) != "" && trim($endDate) != "") {
            $sQuery = $sQuery->where(array("spiv6.assesmentofaudit >='" . $startDate . "'", "spiv6.assesmentofaudit <='" . $endDate . "'"));
        }
        if ($params['auditRndNo'] != '') {
            $sQuery = $sQuery->where("spiv6.auditroundno='" . $params['auditRndNo'] . "'");
        }

        if (isset($params['testPoint']) && trim($params['testPoint']) != '') {

            if (isset($params['testPoint']) && trim($params['testPoint']) != '' && strtolower(trim($params['testPoint'])) == 'other') {
                $sQuery = $sQuery->where("spiv6.testingpointtype_other='" . $params['testPointName'] . "'");
            } else {
                $sQuery = $sQuery->where("spiv6.testingpointtype='" . $params['testPoint'] . "'");
            }
        }
        if (isset($params['level']) && $params['level'] != '') {
            $sQuery = $sQuery->where("spiv6.level='" . $params['level'] . "'");
        }
        if (isset($params['affiliation']) && $params['affiliation'] != '') {
            $sQuery = $sQuery->where("spiv6.affiliation='" . $params['affiliation'] . "'");
        }

        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType != '') {
            if ($loginContainer->userMappingType === 'country' && $params['country'] == '') {
                // User mapped by country: show mapped country data and all related provinces and districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('country', 'province', 'district'))
                    ->where('f.country IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'province' && $params['province'] == '') {
                // User mapped by province: show only mapped provinces
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('province'))
                    ->where('f.province IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'district' && $params['district'] == '') {
                // User mapped by district: show only mapped districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('district'))
                    ->where('f.district IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            }
        }
        if ((isset($params['country']) && $params['country'] != '') || (isset($params['province']) && $params['province'] != '') || (isset($params['district']) && $params['district'] != '')) {
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('country', 'province', 'district'));

            // Apply filters dynamically
            if (isset($params['country']) && $params['country'] != '' && is_array($params['country'])) {
                $sQuery = $sQuery->where('f.country IN ("' . implode('", "', $params['country']) . '")');
            }
            if (isset($params['province']) && $params['province'] != '' && is_array($params['province'])) {
                $sQuery = $sQuery->where('f.province IN ("' . implode('", "', $params['province']) . '")');
            }
            if (isset($params['district']) && $params['district'] != '' && is_array($params['district'])) {
                $sQuery = $sQuery->where('f.district IN ("' . implode('", "', $params['district']) . '")');
            }
        }

        if (isset($params['scoreLevel']) && $params['scoreLevel'] != '') {
            if ($params['scoreLevel'] == 0) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE < 40");
            } elseif ($params['scoreLevel'] == 1) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 59");
            } elseif ($params['scoreLevel'] == 2) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 79");
            } elseif ($params['scoreLevel'] == 3) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 89");
            } elseif ($params['scoreLevel'] == 4) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 90");
            }
        }

        if (isset($params['fieldName']) && trim($params['fieldName']) != '') {
            $sQuery = $sQuery->where(array($params['fieldName'] => $params['val']));
        }
        $sQueryStr = $sql->buildSqlString($sQuery);
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();

        $responseOfSectionS0 = [];

        foreach ($rResult as $row) {
            //error_log($row['S0_Q_4_PROCESS_DOCUMENTED']);
            $responseOfSectionS0[0]['DIAGNOSED_HIV_ABOVE_15'][] = (float) $row['D0_S_1_DIAGNOSED_HIV_ABOVE_15'];
            $responseOfSectionS0[0]['CANDIDATE_SCREENED_FOR_PARTICIPATION'][] = (float) $row['D0_S_2_CANDIDATE_SCREENED_FOR_PARTICIPATION'];
            $responseOfSectionS0[0]['ELIGIBLE_DURING_REVIEW_PERIOD'][] = (float) $row['D0_S_3_ELIGIBLE_DURING_REVIEW_PERIOD'];
            $responseOfSectionS0[0]['ELIGIBLE_AND_DECLINED_REVIEW_PERIOD'][] = (float) $row['D0_S_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD'];
            $responseOfSectionS0[0]['DOCUMENTED_AND_REFUSED'][] = (float) $row['D0_S_5_DOCUMENTED_AND_REFUSED'];
            $responseOfSectionS0[0]['PARTICIAPANTS_ENROLLED_IN_RTRI'][] = (float) $row['D0_S_6_PARTICIAPANTS_ENROLLED_IN_RTRI'];
            $responseOfSectionS0[0]['PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI'][] = (float) $row['D0_S_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI'];
            $responseOfSectionS0[0]['PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI'][] = (float) $row['D0_S_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI'];
        }

        $auditRoundWiseData = [];
        foreach ($responseOfSectionS0 as $auditNo => $auditScores) {
            $auditRoundWiseData[$auditNo]['DIAGNOSED_HIV_ABOVE_15'] = array_sum($auditScores['DIAGNOSED_HIV_ABOVE_15']) / count($auditScores['DIAGNOSED_HIV_ABOVE_15']);
            $auditRoundWiseData[$auditNo]['CANDIDATE_SCREENED_FOR_PARTICIPATION'] = array_sum($auditScores['CANDIDATE_SCREENED_FOR_PARTICIPATION']) / count($auditScores['CANDIDATE_SCREENED_FOR_PARTICIPATION']);
            $auditRoundWiseData[$auditNo]['ELIGIBLE_DURING_REVIEW_PERIOD'] = array_sum($auditScores['ELIGIBLE_DURING_REVIEW_PERIOD']) / count($auditScores['ELIGIBLE_DURING_REVIEW_PERIOD']);
            $auditRoundWiseData[$auditNo]['ELIGIBLE_AND_DECLINED_REVIEW_PERIOD'] = array_sum($auditScores['ELIGIBLE_AND_DECLINED_REVIEW_PERIOD']) / count($auditScores['ELIGIBLE_AND_DECLINED_REVIEW_PERIOD']);
            $auditRoundWiseData[$auditNo]['DOCUMENTED_AND_REFUSED'] = array_sum($auditScores['DOCUMENTED_AND_REFUSED']) / count($auditScores['DOCUMENTED_AND_REFUSED']);
            $auditRoundWiseData[$auditNo]['PARTICIAPANTS_ENROLLED_IN_RTRI'] = array_sum($auditScores['PARTICIAPANTS_ENROLLED_IN_RTRI']) / count($auditScores['PARTICIAPANTS_ENROLLED_IN_RTRI']);
            $auditRoundWiseData[$auditNo]['PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI'] = array_sum($auditScores['PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI']) / count($auditScores['PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI']);
            $auditRoundWiseData[$auditNo]['PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI'] = array_sum($auditScores['PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI']) / count($auditScores['PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI']);
        }

        //var_dump($auditRoundWiseData);die;
        $responseOfSectionS0 = array('');
        return $auditRoundWiseData;
    }

    public function getZeroQuestionCountsV6($params)
    {
        $loginContainer = new Container('credo');
        //$rResult = $this->fetchAllApprovedSubmissions();
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
            ->where(array('spiv6.status' => 'approved'))
            ->order(array("assesmentofaudit DESC"));
        if (isset($params['roundno']) && $params['roundno'] != '') {
            $sQuery = $sQuery->where('spiv6.auditroundno IN ("' . implode('", "', $params['roundno']) . '")');
        }

        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where('spiv6.token IN ("' . implode('", "', $loginContainer->token) . '")');
        }
        $startDate = '';
        $endDate = '';
        if (isset($params['dateRange']) && ($params['dateRange'] != "")) {
            $dateField = explode(" ", $params['dateRange']);
            if (isset($dateField[0]) && trim($dateField[0]) != "") {
                $startDate = CommonService::isoDateFormat($dateField[0]);
            }
            if (isset($dateField[2]) && trim($dateField[2]) != "") {
                $endDate = CommonService::isoDateFormat($dateField[2]);
            }
        }
        if (trim($startDate) != "" && trim($endDate) != "") {
            $sQuery = $sQuery->where(array("spiv6.assesmentofaudit >='" . $startDate . "'", "spiv6.assesmentofaudit <='" . $endDate . "'"));
        }
        if ($params['auditRndNo'] != '') {
            $sQuery = $sQuery->where("spiv6.auditroundno='" . $params['auditRndNo'] . "'");
        }

        if (isset($params['testPoint']) && trim($params['testPoint']) != '') {
            $sQuery = $sQuery->where("spiv6.testingpointtype='" . $params['testPoint'] . "'");
            // if(isset($params['testPointName']) && trim($params['testPointName'])!= ''){
            if (trim($params['testPoint']) != 'other') {
                $sQuery = $sQuery->where("spiv6.testingpointname='" . $params['testPointName'] . "'");
            } else {
                $sQuery = $sQuery->where("spiv6.testingpointtype_other='" . $params['testPointName'] . "'");
            }
            // }
        }
        if (isset($params['level']) && $params['level'] != '') {
            $sQuery = $sQuery->where("spiv6.level='" . $params['level'] . "'");
        }
        if (isset($params['affiliation']) && $params['affiliation'] != '') {
            $sQuery = $sQuery->where("spiv6.affiliation='" . $params['affiliation'] . "'");
        }

        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType != '') {
            if ($loginContainer->userMappingType === 'country' && $params['country'] == '') {
                // User mapped by country: show mapped country data and all related provinces and districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('country', 'province', 'district'))
                    ->where('f.country IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'province' && $params['province'] == '') {
                // User mapped by province: show only mapped provinces
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('province'))
                    ->where('f.province IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'district' && $params['district'] == '') {
                // User mapped by district: show only mapped districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('district'))
                    ->where('f.district IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            }
        }
        if ((isset($params['country']) && $params['country'] != '') || (isset($params['province']) && $params['province'] != '') || (isset($params['district']) && $params['district'] != '')) {
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('country', 'province', 'district'));

            // Apply filters dynamically
            if (isset($params['country']) && $params['country'] != '' && is_array($params['country'])) {
                $sQuery = $sQuery->where('f.country IN ("' . implode('", "', $params['country']) . '")');
            }
            if (isset($params['province']) && $params['province'] != '' && is_array($params['province'])) {
                $sQuery = $sQuery->where('f.province IN ("' . implode('", "', $params['province']) . '")');
            }
            if (isset($params['district']) && $params['district'] != '' && is_array($params['district'])) {
                $sQuery = $sQuery->where('f.district IN ("' . implode('", "', $params['district']) . '")');
            }
        }
        if (isset($params['scoreLevel']) && $params['scoreLevel'] != '') {
            if ($params['scoreLevel'] == 0) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE < 40");
            } elseif ($params['scoreLevel'] == 1) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 59");
            } elseif ($params['scoreLevel'] == 2) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 79");
            } elseif ($params['scoreLevel'] == 3) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 89");
            } elseif ($params['scoreLevel'] == 4) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 90");
            }
        }

        $sQueryStr = $sql->buildSqlString($sQuery);
        // echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();

        $response = [];

        $questionColums = array(
            'PERSONAL_Q_1_1_HIV_TRAINING',
            'PERSONAL_Q_1_2_HIV_TESTING_REGISTER',
            'PERSONAL_Q_1_3_EQA_PT',
            'PERSONAL_Q_1_4_QC_PROCESS',
            'PERSONAL_Q_1_5_SAFETY_MANAGEMENT',
            'PERSONAL_Q_1_6_REFRESHER_TRAINING',
            'PERSONAL_Q_1_7_HIV_COMPETENCY_TESTING',
            'PERSONAL_Q_1_8_NATIONAL_CERTIFICATION',
            'PERSONAL_Q_1_9_CERTIFIED_TESTERS',
            'PERSONAL_Q_1_10_RECERTIFIED',
            'PHYSICAL_Q_2_1_DESIGNATED_HIV_AREA',
            'PHYSICAL_Q_2_2_CLEAN_TESTING_AREA',
            'PHYSICAL_Q_2_3_SUFFICIENT_LIGHT_AVAILABILITY',
            'PHYSICAL_Q_2_4_TEST_KIT_STORAGE',
            'PHYSICAL_Q_2_5_SUFFICIENT_SECURE_STORAGE',
            'SAFETY_Q_3_1_IMPLEMENT_SAFETY_PRACTICES',
            'SAFETY_Q_3_2_ACCIDENTAL_EXPOSURE',
            'SAFETY_Q_3_3_PRACTICE_SAFETY_PRACTICES',
            'SAFETY_Q_3_4_PPE_AVAILABILITY',
            'SAFETY_Q_3_5_PPE_USED_PROPERLY',
            'SAFETY_Q_3_6_WATER_SOAP_AVAILABILITY',
            'SAFETY_Q_3_7_DISINFECTANT_AVAILABLE',
            'SAFETY_Q_3_8_DISINFECTANT_LABELED_PROPERLY',
            'SAFETY_Q_3_9_SEGREGATION_OF_WASTE',
            'SAFETY_Q_3_10_INFECTIOUS_WASTE_EMPTIED',
            'SAFETY_Q_3_11',
            'PRE_Q_4_1_NATIONAL_GUIDELINES',
            'PRE_Q_4_2_HIV_TESTING_ALGORITHM',
            'PRE_Q_4_3_TEST_PROCEDURES_ACCESSIBLE',
            'PRE_Q_4_4_TEST_PROCEDURES_ACCURATE',
            'PRE_Q_4_5_APPROVED_KITS_AVAILABLE',
            'PRE_Q_4_6_HIV_KITS_EXPIRATION',
            'PRE_Q_4_7_KIT_SUPPLIES_AVAILABILITY',
            'PRE_Q_4_8_STOCK_MANAGEMENT',
            'PRE_Q_4_9_DOCUMENTED_INVENTORY',
            'PRE_Q_4_10_SOPS_BLOOD_COLLECTION',
            'PRE_Q_4_11_BLOOD_COLLECTION_SUPPLIES',
            'PRE_Q_4_12_CLIENT_IDENTIFICATION',
            'TEST_Q_5_1_PROCEDURES_TESTING_ALGORITHM',
            'TEST_Q_5_2_TIMERS_AVAILABILITY',
            'TEST_Q_5_3_SAMPLE_DEVICE_ACCURACY',
            'TEST_Q_5_4_TESTING_PROCEDURE_FOLLOWED',
            'TEST_Q_5_5_QUALITY_CONTROL',
            'TEST_Q_5_6_QC_RESULTS_RECORDED',
            'TEST_Q_5_7_INCORRECT_QC_RESULTS',
            'TEST_Q_5_8_APPROPRIATE_STEPS_TAKEN',
            'TEST_Q_5_9_REVIEW_QC_RECORDS',
            'POST_Q_6_1_STANDARDIZED_HIV_REGISTER',
            'POST_Q_6_2_ELEMENTS_CAPTURED_CORRECTLY',
            'POST_Q_6_3_PAGE_TOTAL_SUMMARY',
            'POST_Q_6_4_INVALID_TEST_RESULT_RECORDED',
            'POST_Q_6_5_APPROPRIATE_STEPS_TAKEN',
            'POST_Q_6_6_REGISTERS_REVIEWED',
            'POST_Q_6_7_DOCUMENTS_SECURELY_KEPT',
            'POST_Q_6_8_REGISTER_SECURE_LOCATION',
            'POST_Q_6_9_REGISTERS_PROPERLY_LABELED',
            'EQA_Q_7_1_PT_ENROLLMENT',
            'EQA_Q_7_2_TESTING_EQAPT_SAMPLES',
            'EQA_Q_7_3_REVIEW_BEFORE_SUBMISSION',
            'EQA_Q_7_4_FEEDBACK_RECEIVED_REVIEWED',
            'EQA_Q_7_5_IMPLEMENT_CORRECTIVE_ACTION',
            'EQA_Q_7_6_RECEIVE_PERIODIC_VISITS',
            'EQA_Q_7_7_FEEDBACK_PROVIDED_DOCUMENTED',
            'EQA_Q_7_8_TESTERS_RETRAINED_IN_VISITS',
            'EQA_Q_7_9',
            'EQA_Q_7_10',
            'EQA_Q_7_11',
            'EQA_Q_7_12',
            'EQA_Q_7_13',
            'EQA_Q_7_14',
        );
        foreach ($rResult as $row) {
            foreach ($row as $col => $val) {
                if (in_array($col, $questionColums) && $val == "0") {
                    $response[$col] = isset($response[$col]) ? $response[$col] + 1 : 1;
                }
            }
        }
        arsort($response);
        return $response;
    }

    public function updateFormStatus($id, $status)
    {
        if (trim($id) != "" && trim($status) != '') {
            $data = array('status' => $status);
            $this->update($data, array('id' => $id));
        }
        return $id;
    }

    public function fetchAllApprovedSubmissionsV6($sortOrder = 'DESC')
    {
        $loginContainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
            ->where(array('spiv6.status' => 'approved'))
            ->order(array("assesmentofaudit $sortOrder"));
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where('spiv6.token IN ("' . implode('", "', $loginContainer->token) . '")');
        }
        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType != '') {
            if ($loginContainer->userMappingType === 'country') {
                // User mapped by country: show mapped country data and all related provinces and districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('country', 'province', 'district'))
                    ->where('f.country IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'province') {
                // User mapped by province: show only mapped provinces
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('province'))
                    ->where('f.province IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'district') {
                // User mapped by district: show only mapped districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('district'))
                    ->where('f.district IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            }
        }
        $sQueryStr = $sql->buildSqlString($sQuery);
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        return $rResult;
    }

    public function getHighVolumeSites()
    {
        $loginContainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
            ->where(array('spiv6.status' => 'approved'))
            ->order(array("client_tested_HIV_PM DESC"))
            ->limit(10);
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where('spiv6.token IN ("' . implode('", "', $loginContainer->token) . '")');
        }
        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType != '') {
            if ($loginContainer->userMappingType === 'country') {
                // User mapped by country: show mapped country data and all related provinces and districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('country', 'province', 'district'))
                    ->where('f.country IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'province') {
                // User mapped by province: show only mapped provinces
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('province'))
                    ->where('f.province IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'district') {
                // User mapped by district: show only mapped districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('district'))
                    ->where('f.district IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            }
        }
        $sQueryStr = $sql->buildSqlString($sQuery);
        //echo $sQueryStr;die;
        return $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    }

    public function fecthAllApprovedSubmissionsTable($parameters)
    {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */
        $queryContainer = new Container('query');
        $loginContainer = new Container('credo');
        $aColumns = array('facilityname', 'assesmentofaudit', 'testingpointname', 'PERSONAL_SCORE', 'PHYSICAL_SCORE', 'SAFETY_SCORE', 'PRETEST_SCORE', 'TEST_SCORE', 'POST_SCORE', 'EQA_SCORE', 'FINAL_AUDIT_SCORE', 'AUDIT_SCORE_PERCENTAGE');
        $orderColumns = array('facilityname', 'assesmentofaudit', 'testingpointname', 'PERSONAL_SCORE', 'PHYSICAL_SCORE', 'SAFETY_SCORE', 'PRETEST_SCORE', 'TEST_SCORE', 'POST_SCORE', 'EQA_SCORE', 'FINAL_AUDIT_SCORE', 'AUDIT_SCORE_PERCENTAGE');

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
            for ($i = 0; $i < (int) $parameters['iSortingCols']; $i++) {
                if ($parameters['bSortable_' . (int) $parameters['iSortCol_' . $i]] == "true") {
                    $sOrder .= $orderColumns[(int) $parameters['iSortCol_' . $i]] . " " . ($parameters['sSortDir_' . $i]) . ",";
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
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' OR ";
                    } else {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' ";
                    }
                }
                $sWhereSub .= ")";
            }
            $sWhere .= $sWhereSub;
        }
        /* Individual column filtering */
        $counter = count($aColumns);

        /* Individual column filtering */
        for ($i = 0; $i < $counter; $i++) {
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
        $sql = new Sql($this->adapter);
        $startDate = '';
        $endDate = '';
        if (isset($parameters['dateRange']) && ($parameters['dateRange'] != "")) {
            $dateField = explode(" ", $parameters['dateRange']);
            if (isset($dateField[0]) && trim($dateField[0]) != "") {
                $startDate = CommonService::isoDateFormat($dateField[0]);
            }
            if (isset($dateField[2]) && trim($dateField[2]) != "") {
                $endDate = CommonService::isoDateFormat($dateField[2]);
            }
        }
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->where(array('spiv3.status' => 'approved'));
        if ($parameters['auditRndNo'] != '') {
            $sQuery = $sQuery->where("spiv3.auditroundno='" . $parameters['auditRndNo'] . "'");
        }
        if (trim($startDate) != "" && trim($endDate) != "") {
            $sQuery = $sQuery->where(array("spiv3.assesmentofaudit >='" . $startDate . "'", "spiv3.assesmentofaudit <='" . $endDate . "'"));
        }
        if (isset($parameters['testPoint']) && trim($parameters['testPoint']) != '') {
            $sQuery = $sQuery->where("spiv3.testingpointtype='" . $parameters['testPoint'] . "'");
            if (trim($parameters['testPoint']) != 'other') {
                $sQuery = $sQuery->where("spiv3.testingpointname='" . $parameters['testPointName'] . "'");
            } else {
                $sQuery = $sQuery->where("spiv3.testingpointtype_other='" . $parameters['testPointName'] . "'");
            }
        }
        if ($parameters['level'] != '') {
            $sQuery = $sQuery->where("spiv3.level='" . $parameters['level'] . "'");
        }
        if (is_array($parameters['province']) && $parameters['province'] !== []) {
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('province', 'district'))
                ->where('f.province IN ("' . implode('", "', $parameters['province']) . '")');
            if (is_array($parameters['district']) && $parameters['district'] !== []) {
                $sQuery = $sQuery->where('f.province IN ("' . implode('", "', $parameters['province']) . '")');
            }
        } elseif ($parameters['province'] != '') {
            $provinces = explode(",", $parameters['province']);
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('province', 'district'))
                ->where('f.province IN ("' . implode('", "', $provinces) . '")');
        }
        if ($parameters['province'] != '') {
            if (is_array($parameters['district']) && $parameters['district'] !== []) {
                $sQuery = $sQuery->where('f.district IN ("' . implode('", "', $parameters['district']) . '")');
            } elseif ($parameters['district'] != '') {
                $provinces = explode(",", $parameters['district']);
                $sQuery = $sQuery->where('f.district IN ("' . implode('", "', $provinces) . '")');
            }
        }
        if ($parameters['affiliation'] != '') {
            $sQuery = $sQuery->where("spiv3.affiliation='" . $parameters['affiliation'] . "'");
        }
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where('spiv3.token IN ("' . implode('", "', $loginContainer->token) . '")');
        }
        if (isset($parameters['scoreLevel']) && $parameters['scoreLevel'] != '') {
            if ($parameters['scoreLevel'] == 0) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE < 40");
            } elseif ($parameters['scoreLevel'] == 1) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 59");
            } elseif ($parameters['scoreLevel'] == 2) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 79");
            } elseif ($parameters['scoreLevel'] == 3) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 89");
            } elseif ($parameters['scoreLevel'] == 4) {
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
        $sQueryStr = $sql->buildSqlString($sQuery); // Get the string of the Sql, instead of the Select-instance
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->buildSqlString($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->where(array('spiv3.status' => 'approved'));
        if ($parameters['auditRndNo'] != '') {
            $tQuery = $tQuery->where("spiv3.auditroundno='" . $parameters['auditRndNo'] . "'");
        }
        if (trim($startDate) != "" && trim($endDate) != "") {
            $tQuery = $tQuery->where(array("spiv3.assesmentofaudit >='" . $startDate . "'", "spiv3.assesmentofaudit <='" . $endDate . "'"));
        }
        if (isset($parameters['testPoint']) && trim($parameters['testPoint']) != '') {
            $tQuery = $tQuery->where("spiv3.testingpointtype='" . $parameters['testPoint'] . "'");
            // if(isset($parameters['testPointName']) && trim($parameters['testPointName'])!= ''){
            if (trim($parameters['testPoint']) != 'other') {
                $tQuery = $tQuery->where("spiv3.testingpointname='" . $parameters['testPointName'] . "'");
            } else {
                $tQuery = $tQuery->where("spiv3.testingpointtype_other='" . $parameters['testPointName'] . "'");
            }
            // }
        }
        if ($parameters['level'] != '') {
            $tQuery = $tQuery->where("spiv3.level='" . $parameters['level'] . "'");
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
        if ($parameters['affiliation'] != '') {
            $tQuery = $tQuery->where("spiv3.affiliation='" . $parameters['affiliation'] . "'");
        }
        if (!empty($loginContainer->token)) {
            $tQuery = $tQuery->where('spiv3.token IN ("' . implode('", "', $loginContainer->token) . '")');
        }
        if (isset($parameters['scoreLevel']) && $parameters['scoreLevel'] != '') {
            if ($parameters['scoreLevel'] == 0) {
                $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE < 40");
            } elseif ($parameters['scoreLevel'] == 1) {
                $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 59");
            } elseif ($parameters['scoreLevel'] == 2) {
                $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 79");
            } elseif ($parameters['scoreLevel'] == 3) {
                $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 89");
            } elseif ($parameters['scoreLevel'] == 4) {
                $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 90");
            }
        }
        $tQueryStr = $sql->buildSqlString($tQuery); // Get the string of the Sql, instead of the Select-instance
        $tResult = $dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);
        $iTotal = count($tResult);
        $output = array(
            "sEcho" => (int) $parameters['sEcho'],
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array(),
        );

        foreach ($rResult as $aRow) {
            $row = [];
            $row[] = $aRow['facilityname'];
            $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
            $row[] = $aRow['testingpointname'] . " - " . $aRow['testingpointtype'];
            $row[] = $aRow['PERSONAL_SCORE'];
            $row[] = $aRow['PHYSICAL_SCORE'];
            $row[] = $aRow['SAFETY_SCORE'];
            $row[] = $aRow['PRETEST_SCORE'];
            $row[] = $aRow['TEST_SCORE'];
            $row[] = $aRow['POST_SCORE'];
            $row[] = $aRow['EQA_SCORE'];
            $row[] = $aRow['FINAL_AUDIT_SCORE'];
            $row[] = round($aRow['AUDIT_SCORE_PERCENTAGE'], 2);
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function fetchAllApprovedV6FormSubmissionsTable($parameters)
    {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */

        $queryContainer = new Container('query');
        $loginContainer = new Container('credo');
        $aColumns = array('facilityname', 'assesmentofaudit', 'testingpointtype', 'PERSONAL_SCORE', 'PHYSICAL_SCORE', 'SAFETY_SCORE', 'PRETEST_SCORE', 'TEST_SCORE', 'POST_SCORE', 'EQA_SCORE', 'RTRI_SCORE', 'FINAL_AUDIT_SCORE', 'AUDIT_SCORE_PERCENTAGE');
        $orderColumns = array('facilityname', 'assesmentofaudit', 'testingpointtype', 'PERSONAL_SCORE', 'PHYSICAL_SCORE', 'SAFETY_SCORE', 'PRETEST_SCORE', 'TEST_SCORE', 'POST_SCORE', 'EQA_SCORE', 'RTRI_SCORE', 'FINAL_AUDIT_SCORE', 'AUDIT_SCORE_PERCENTAGE');

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
            for ($i = 0; $i < (int) $parameters['iSortingCols']; $i++) {
                if ($parameters['bSortable_' . (int) $parameters['iSortCol_' . $i]] == "true") {
                    $sOrder .= $orderColumns[(int) $parameters['iSortCol_' . $i]] . " " . ($parameters['sSortDir_' . $i]) . ",";
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
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' OR ";
                    } else {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' ";
                    }
                }
                $sWhereSub .= ")";
            }
            $sWhere .= $sWhereSub;
        }
        /* Individual column filtering */
        $counter = count($aColumns);

        /* Individual column filtering */
        for ($i = 0; $i < $counter; $i++) {
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
        $sql = new Sql($this->adapter);
        $startDate = '';
        $endDate = '';
        if (isset($parameters['dateRange']) && ($parameters['dateRange'] != "")) {
            $dateField = explode(" ", $parameters['dateRange']);
            if (isset($dateField[0]) && trim($dateField[0]) != "") {
                $startDate = CommonService::isoDateFormat($dateField[0]);
            }
            if (isset($dateField[2]) && trim($dateField[2]) != "") {
                $endDate = CommonService::isoDateFormat($dateField[2]);
            }
        }
        $sQuery = $sql->select()->from(array('spiv5' => 'spi_form_v_6'))
            ->where(array('spiv5.status' => 'approved'));
        if ($parameters['auditRndNo'] != '') {
            $sQuery = $sQuery->where("spiv5.auditroundno='" . $parameters['auditRndNo'] . "'");
        }
        if (trim($startDate) != "" && trim($endDate) != "") {
            $sQuery = $sQuery->where(array("spiv5.assesmentofaudit >='" . $startDate . "'", "spiv5.assesmentofaudit <='" . $endDate . "'"));
        }
        if (isset($parameters['testPoint']) && trim($parameters['testPoint']) != '') {

            if (trim($parameters['testPoint']) != 'other') {
                $sQuery = $sQuery->where("spiv5.testingpointtype='" . $parameters['testPoint'] . "'");
            } else {
                $sQuery = $sQuery->where("spiv5.testingpointtype_other='" . $parameters['testPointName'] . "'");
            }
        }

        if ($parameters['affiliation'] != '') {
            $sQuery = $sQuery->where("spiv5.affiliation='" . $parameters['affiliation'] . "'");
        }
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where('spiv5.token IN ("' . implode('", "', $loginContainer->token) . '")');
        }
        if (isset($parameters['scoreLevel']) && $parameters['scoreLevel'] != '') {
            if ($parameters['scoreLevel'] == 0) {
                $sQuery = $sQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE < 40");
            } elseif ($parameters['scoreLevel'] == 1) {
                $sQuery = $sQuery->where("ROUND(spiv5.AUDIT_SCORE_PERCENTAGE) >= 40 AND ROUND(spiv5.AUDIT_SCORE_PERCENTAGE) <= 59");
            } elseif ($parameters['scoreLevel'] == 2) {
                $sQuery = $sQuery->where("ROUND(spiv5.AUDIT_SCORE_PERCENTAGE) >= 60 AND ROUND(spiv5.AUDIT_SCORE_PERCENTAGE) <= 79");
            } elseif ($parameters['scoreLevel'] == 3) {
                $sQuery = $sQuery->where("ROUND(spiv5.AUDIT_SCORE_PERCENTAGE) >= 80 AND ROUND(spiv5.AUDIT_SCORE_PERCENTAGE) <= 89");
            } elseif ($parameters['scoreLevel'] == 4) {
                $sQuery = $sQuery->where("ROUND(spiv5.AUDIT_SCORE_PERCENTAGE) >= 90");
            }
        }
        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType != '') {
            // Mapping exists
            if ($loginContainer->userMappingType === 'country' && $parameters['country'] == '') {
                // User mapped by country: show mapped country data and all related provinces and districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.facility_id=spiv5.facilityid OR f.facility_name=spiv5.facilityname', array('country', 'province', 'district'))
                    ->where('f.country IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'province' && $parameters['province'] == '') {
                // User mapped by province: show only mapped provinces
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.facility_id=spiv5.facilityid OR f.facility_name=spiv5.facilityname', array('province'))
                    ->where('f.province IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'district' && $parameters['district'] == '') {
                // User mapped by district: show only mapped districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.facility_id=spiv5.facilityid OR f.facility_name=spiv5.facilityname', array('district'))
                    ->where('f.district IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            }
        }
        if (isset($parameters['country']) && $parameters['country'] != '') {
            $sQuery = $sQuery->join(array('f1' => 'spi_rt_3_facilities'), 'f1.facility_id=spiv5.facilityid OR f1.facility_name=spiv5.facilityname', array('country'))
                ->where('f1.country IN (' . $parameters['country'] . ')');
        }
        if (isset($parameters['province']) && $parameters['province'] != '') {
            $sQuery = $sQuery->join(array('f2' => 'spi_rt_3_facilities'), 'f2.facility_id=spiv5.facilityid OR f2.facility_name=spiv5.facilityname', array('province'))
                ->where('f2.province IN (' . $parameters['province'] . ')');
        }
        if (isset($parameters['district']) && $parameters['district'] != '') {
            $sQuery = $sQuery->join(array('f3' => 'spi_rt_3_facilities'), 'f3.facility_id=spiv5.facilityid OR f3.facility_name=spiv5.facilityname', array('district'))
                ->where('f3.district IN (' . $parameters['district'] . ')');
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
        $sQueryStr = $sql->buildSqlString($sQuery); // Get the string of the Sql, instead of the Select-instance
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->buildSqlString($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $tQuery = $sql->select()->from(array('spiv5' => 'spi_form_v_6'))
            ->where(array('spiv5.status' => 'approved'));
        if ($parameters['auditRndNo'] != '') {
            $tQuery = $tQuery->where("spiv5.auditroundno='" . $parameters['auditRndNo'] . "'");
        }
        if (trim($startDate) != "" && trim($endDate) != "") {
            $tQuery = $tQuery->where(array("spiv5.assesmentofaudit >='" . $startDate . "'", "spiv5.assesmentofaudit <='" . $endDate . "'"));
        }
        if (isset($parameters['testPoint']) && trim($parameters['testPoint']) != '') {
            if (trim($parameters['testPoint']) != 'other') {
                $tQuery = $tQuery->where("spiv5.testingpointtype='" . $parameters['testPoint'] . "'");
            } else {
                $tQuery = $tQuery->where("spiv5.testingpointtype_other='" . $parameters['testPointName'] . "'");
            }
        }

        if ($parameters['affiliation'] != '') {
            $tQuery = $tQuery->where("spiv5.affiliation='" . $parameters['affiliation'] . "'");
        }
        if (!empty($loginContainer->token)) {
            $tQuery = $tQuery->where('spiv5.token IN ("' . implode('", "', $loginContainer->token) . '")');
        }
        if (isset($parameters['scoreLevel']) && $parameters['scoreLevel'] != '') {
            if ($parameters['scoreLevel'] == 0) {
                $tQuery = $tQuery->where("spiv5.AUDIT_SCORE_PERCENTAGE < 40");
            } elseif ($parameters['scoreLevel'] == 1) {
                $tQuery = $tQuery->where("ROUND(spiv5.AUDIT_SCORE_PERCENTAGE) >= 40 AND ROUND(spiv5.AUDIT_SCORE_PERCENTAGE) <= 59");
            } elseif ($parameters['scoreLevel'] == 2) {
                $tQuery = $tQuery->where("ROUND(spiv5.AUDIT_SCORE_PERCENTAGE) >= 60 AND ROUND(spiv5.AUDIT_SCORE_PERCENTAGE) <= 79");
            } elseif ($parameters['scoreLevel'] == 3) {
                $tQuery = $tQuery->where("ROUND(spiv5.AUDIT_SCORE_PERCENTAGE) >= 80 AND ROUND(spiv5.AUDIT_SCORE_PERCENTAGE) <= 89");
            } elseif ($parameters['scoreLevel'] == 4) {
                $tQuery = $tQuery->where("ROUND(spiv5.AUDIT_SCORE_PERCENTAGE) >= 90");
            }
        }
        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType != '') {
            // Mapping exists
            if ($loginContainer->userMappingType === 'country' && $parameters['country'] == '') {
                // User mapped by country: show mapped country data and all related provinces and districts
                $tQuery = $tQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.facility_id=spiv5.facilityid OR f.facility_name=spiv5.facilityname', array('country', 'province', 'district'))
                    ->where('f.country IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'province' && $parameters['province'] == '') {
                // User mapped by province: show only mapped provinces
                $tQuery = $tQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.facility_id=spiv5.facilityid OR f.facility_name=spiv5.facilityname', array('province'))
                    ->where('f.province IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'district' && $parameters['district'] == '') {
                // User mapped by district: show only mapped districts
                $tQuery = $tQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.facility_id=spiv5.facilityid OR f.facility_name=spiv5.facilityname', array('district'))
                    ->where('f.district IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            }
        }
        if (isset($parameters['country']) && $parameters['country'] != '') {
            $tQuery = $tQuery->join(array('f1' => 'spi_rt_3_facilities'), 'f1.facility_id=spiv5.facilityid OR f1.facility_name=spiv5.facilityname', array('country'))
                ->where('f1.country IN (' . $parameters['country'] . ')');
        }
        if (isset($parameters['province']) && $parameters['province'] != '') {
            $tQuery = $tQuery->join(array('f2' => 'spi_rt_3_facilities'), 'f2.facility_id=spiv5.facilityid OR f2.facility_name=spiv5.facilityname', array('province'))
                ->where('f2.province IN (' . $parameters['province'] . ')');
        }
        if (isset($parameters['district']) && $parameters['district'] != '') {
            $tQuery = $tQuery->join(array('f3' => 'spi_rt_3_facilities'), 'f3.facility_id=spiv5.facilityid OR f3.facility_name=spiv5.facilityname', array('district'))
                ->where('f3.district IN (' . $parameters['district'] . ')');
        }
        $tQueryStr = $sql->buildSqlString($tQuery); // Get the string of the Sql, instead of the Select-instance
        $tResult = $dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);
        $iTotal = count($tResult);
        $output = array(
            "sEcho" => (int) $parameters['sEcho'],
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array(),
        );


        foreach ($rResult as $aRow) {
            $row = [];
            $row[] = $aRow['facilityname'];
            $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
            $row[] = $aRow['testingpointtype'];
            $row[] = $aRow['PERSONAL_SCORE'];
            $row[] = $aRow['PHYSICAL_SCORE'];
            $row[] = $aRow['SAFETY_SCORE'];
            $row[] = $aRow['PRETEST_SCORE'];
            $row[] = $aRow['TEST_SCORE'];
            $row[] = $aRow['POST_SCORE'];
            $row[] = $aRow['EQA_SCORE'];
            $row[] = $aRow['RTRI_SCORE'];
            $row[] = $aRow['FINAL_AUDIT_SCORE'];
            $row[] = round($aRow['AUDIT_SCORE_PERCENTAGE'], 2);
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function fetchAllApprovedSubmissionLocationV6($params)
    {
        $loginContainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
            ->where(array('spiv6.status' => 'approved'))
            ->order(array("assesmentofaudit DESC"));
        if (isset($params['roundno']) && $params['roundno'] != '') {
            $sQuery = $sQuery->where('spiv6.auditroundno IN ("' . implode('", "', $params['roundno']) . '")');
        }
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where('spiv6.token IN ("' . implode('", "', $loginContainer->token) . '")');
        }
        $startDate = '';
        $endDate = '';
        if (isset($params['dateRange']) && ($params['dateRange'] != "")) {
            $dateField = explode(" ", $params['dateRange']);
            if (isset($dateField[0]) && trim($dateField[0]) != "") {
                $startDate = CommonService::isoDateFormat($dateField[0]);
            }
            if (isset($dateField[2]) && trim($dateField[2]) != "") {
                $endDate = CommonService::isoDateFormat($dateField[2]);
            }
        }
        if (trim($startDate) != "" && trim($endDate) != "") {
            $sQuery = $sQuery->where(array("spiv6.assesmentofaudit >='" . $startDate . "'", "spiv6.assesmentofaudit <='" . $endDate . "'"));
        }
        if ($params['auditRndNo'] != '') {
            $sQuery = $sQuery->where("spiv6.auditroundno='" . $params['auditRndNo'] . "'");
        }

        if (isset($params['testPoint']) && trim($params['testPoint']) != '') {
            $sQuery = $sQuery->where("spiv6.testingpointtype='" . $params['testPoint'] . "'");
            // if(isset($params['testPointName']) && trim($params['testPointName'])!= ''){
            if (trim($params['testPoint']) != 'other') {
                $sQuery = $sQuery->where("spiv6.testingpointname='" . $params['testPointName'] . "'");
            } else {
                $sQuery = $sQuery->where("spiv6.testingpointtype_other='" . $params['testPointName'] . "'");
            }
            // }
        }
        if (isset($params['level']) && $params['level'] != '') {
            $sQuery = $sQuery->where("spiv6.level='" . $params['level'] . "'");
        }
        if (isset($params['affiliation']) && $params['affiliation'] != '') {
            $sQuery = $sQuery->where("spiv6.affiliation='" . $params['affiliation'] . "'");
        }

        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType != '') {
            if ($loginContainer->userMappingType === 'country' && $params['country'] == '') {
                // User mapped by country: show mapped country data and all related provinces and districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('country', 'province', 'district'))
                    ->where('f.country IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'province' && $params['province'] == '') {
                // User mapped by province: show only mapped provinces
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('province'))
                    ->where('f.province IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'district' && $params['district'] == '') {
                // User mapped by district: show only mapped districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('district'))
                    ->where('f.district IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            }
        }
        if ((isset($params['country']) && $params['country'] != '') || (isset($params['province']) && $params['province'] != '') || (isset($params['district']) && $params['district'] != '')) {
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('country', 'province', 'district'));

            // Apply filters dynamically
            if (isset($params['country']) && $params['country'] != '' && is_array($params['country'])) {
                $sQuery = $sQuery->where('f.country IN ("' . implode('", "', $params['country']) . '")');
            }
            if (isset($params['province']) && $params['province'] != '' && is_array($params['province'])) {
                $sQuery = $sQuery->where('f.province IN ("' . implode('", "', $params['province']) . '")');
            }
            if (isset($params['district']) && $params['district'] != '' && is_array($params['district'])) {
                $sQuery = $sQuery->where('f.district IN ("' . implode('", "', $params['district']) . '")');
            }
        }
        if (isset($params['scoreLevel']) && $params['scoreLevel'] != '') {
            if ($params['scoreLevel'] == 0) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE < 40");
            } elseif ($params['scoreLevel'] == 1) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 59");
            } elseif ($params['scoreLevel'] == 2) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 79");
            } elseif ($params['scoreLevel'] == 3) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 89");
            } elseif ($params['scoreLevel'] == 4) {
                $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 90");
            }
        }
        $sQueryStr = $sql->buildSqlString($sQuery);
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();

        return $rResult;
    }


    public function updateSpiV6FormDetails($params)
    {
        // \Zend\Debug\Debug::dump($params);
        if (trim($params['formId']) != "") {
            $sessionLogin = new Container('credo');
            $user_name = $sessionLogin->login;
            $dbAdapter = $this->adapter;
            $eventTable = new EventLogTable($dbAdapter);
            $sql = new Sql($this->adapter);
            $formId = base64_decode($params['formId']);
            $summationData = [];
            if (isset($params['sectionNo'])) {
                $n = count($params['sectionNo']);
                for ($i = 0; $i < $n; $i++) {
                    $rowId = $params['rowId'][$i];
                    if (isset($params['sectionNo'][$i]) && trim($params['sectionNo'][$i]) != "" && trim($params['deficiency'][$i]) != "" && trim($params['correction' . $rowId]) != "") {
                        // \Zend\Debug\Debug::dump($summationData);die;
                        $summationData[] = array(
                            'sectionno' => $params['sectionNo'][$i],
                            'deficiency' => $params['deficiency'][$i],
                            'correction' => $params['correction' . $rowId],
                            'auditorcomment' => $params['auditorComment'][$i],
                            'action' => $params['action'][$i],
                            'timeline' => $params['timeline'][$i],
                        );
                    }
                }
                $summationData = json_encode($summationData, true);
                // \Zend\Debug\Debug::dump($summationData);die;
            }

            // update facility tbl
            $id = 0;
            //$facilityDb = new SpiRt5FacilitiesTable($dbAdapter);
            $facilityDb = new SpiRtFacilitiesTable($dbAdapter);
            if (trim($params['testingFacility']) != '') {
                $id = base64_decode($params['testingFacility']);
                $facilityDb->updateFacilityInfo($id, $params);
            } elseif (trim($params['testingFacilityName']) != '') {
                //$fQuery = $sql->select()->from(array('spirt5' => 'spi_rt_5_facilities'))
                $fQuery = $sql->select()->from(array('spirt3' => 'spi_rt_3_facilities'))
                    ->columns(array('id'))
                    ->where(array('spirt3.facility_name' => $params['testingFacilityName']));
                $fQueryStr = $sql->buildSqlString($fQuery);
                /** @var \Laminas\Db\Adapter\Driver\ResultInterface $fResult */
                $fResult = $dbAdapter->query($fQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
                if ($fResult) {
                    $id = $fResult['id'];
                    $facilityDb->updateFacilityInfo($id, $params);
                } else {
                    $id = $facilityDb->addFacilityInfo($params);
                }
            }

            $data = array(
                //'assesmentofaudit' => CommonService::isoDateFormat($params['auditDate']),
                'auditroundno' => $params['auditRound'],
                'facility' => ($id > 0) ? $id : null,
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
                // 'avgMonthTesting' => (isset($params['client_tested_HIV_PM']) && $params['client_tested_HIV_PM'] > 0 ? $params['client_tested_HIV_PM'] : 0),
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

                'S0_C_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY' => $params['sur_c_s_1'],
                'S0_C_2_COUNSELORS_FOLLOWING_PROTOCOL' => $params['sur_c_s_2'],
                'S0_C_3_TESTS_RECORDED_RECENCY' => $params['sur_c_s_3'],
                'S0_C_4_PROCESS_DOCUMENTED' => $params['sur_c_s_4'],
                'S0_C_5_RESULTS_RETURNED_IN_TWO_WEEKS' => $params['sur_c_s_5'],
                'S0_C_6_PROTOCOL_VIOLATION_DOCUMENTED' => $params['sur_c_s_6'],
                'S0_C_7_DOCUMENTING_PROTOCOL_ERRORS' => $params['sur_c_s_7'],

                'D0_N_1_DIAGNOSED_HIV_ABOVE_15' => $params['dati_n_1'],
                'D0_D_1_DIAGNOSED_HIV_ABOVE_15' => $params['dati_d_1'],
                'D0_S_1_DIAGNOSED_HIV_ABOVE_15' => $params['dati_s_1'],
                'D0_N_2_CANDIDATE_SCREENED_FOR_PARTICIPATION' => $params['dati_n_2'],
                'D0_D_2_CANDIDATE_SCREENED_FOR_PARTICIPATION' => $params['dati_d_2'],
                'D0_S_2_CANDIDATE_SCREENED_FOR_PARTICIPATION' => $params['dati_s_2'],

                'D0_N_3_ELIGIBLE_DURING_REVIEW_PERIOD' => $params['dati_n_3'],
                'D0_D_3_ELIGIBLE_DURING_REVIEW_PERIOD' => $params['dati_d_3'],
                'D0_S_3_ELIGIBLE_DURING_REVIEW_PERIOD' => $params['dati_s_3'],

                'D0_N_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD' => $params['dati_n_4'],
                'D0_D_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD' => $params['dati_d_4'],
                'D0_S_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD' => $params['dati_s_4'],
                'D0_N_5_DOCUMENTED_AND_REFUSED' => $params['dati_n_5'],
                'D0_D_5_DOCUMENTED_AND_REFUSED' => $params['dati_d_5'],
                'D0_S_5_DOCUMENTED_AND_REFUSED' => $params['dati_s_5'],
                'D0_N_6_PARTICIAPANTS_ENROLLED_IN_RTRI' => $params['dati_n_6'],
                'D0_D_6_PARTICIAPANTS_ENROLLED_IN_RTRI' => $params['dati_d_6'],
                'D0_S_6_PARTICIAPANTS_ENROLLED_IN_RTRI' => $params['dati_s_6'],
                'D0_N_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI' => $params['dati_n_7'],
                'D0_D_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI' => $params['dati_d_7'],
                'D0_S_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI' => $params['dati_s_7'],
                'D0_N_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI' => $params['dati_n_8'],
                'D0_D_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI' => $params['dati_d_8'],
                'D0_S_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI' => $params['dati_s_8'],

                'Latitude' => $params['latitude'],
                'Longitude' => $params['longitude'],

                'staffaudited' => $params['staffAuditedName'],
                'durationaudit' => $params['durationaudit'],
                //'FINAL_AUDIT_SCORE' => $params['totalPointsScored'],
                //'MAX_AUDIT_SCORE' => $params['totalScoreExpect'],
                //'AUDIT_SCORE_PERCENTAGE' => $params['auditScorePercentage'],
                'correctiveaction' => $summationData,
            );
            // \Zend\Debug\Debug::dump($data);die;

            $result = $this->update($data, array('id' => $formId));
            $subject = '';
            $eventType = 'Update-SPI RT Form 6-Request';
            $action = $user_name . ' has updated the SPI RT Form 6 information ' . $formId;
            $resourceName = 'SPI-RT-Form-6';
            $eventTable->addEventLog($subject, $eventType, $action, $resourceName);

            return $formId;
        }
    }
    public function fetchSpiV6FormAuditNo()
    {
        $loginContainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
            ->columns(array('assesmentofaudit', 'auditroundno', 'rowCount' => new Expression("COUNT('auditroundno')")))
            ->group('auditroundno')
            ->order("auditroundno ASC");
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where('spiv6.token IN ("' . implode('", "', $loginContainer->token) . '")');
        }
        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType != '') {
            // Mapping exists
            if ($loginContainer->userMappingType === 'country') {
                // User mapped by country: show mapped country data and all related provinces and districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('country', 'province', 'district'))
                    ->where('f.country IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'province') {
                // User mapped by province: show only mapped provinces
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('province'))
                    ->where('f.province IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'district') {
                // User mapped by district: show only mapped districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility', array('district'))
                    ->where('f.district IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            }
        }
        $sQueryStr = $sql->buildSqlString($sQuery);
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        return $rResult;
    }

    public function fetchSpiV3FormFacilityAuditNo($params)
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->columns(array(new Expression('DISTINCT(auditroundno) as auditroundno'), 'rowCount' => new Expression("COUNT('auditroundno')")))
            ->where(array($params['fieldName'] => $params['val']))
            ->group('auditroundno')
            ->order("auditroundno ASC");
        $sQueryStr = $sql->buildSqlString($sQuery);
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();

        return $rResult;
    }

    public function mergeFacilityName($params)
    {
        $result = 0;
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $facilityDb = new \Application\Model\SpiRtFacilitiesTable($this->adapter);
        if (isset($params['editFacilityName']) && trim($params['editFacilityName']) != '') {
            $facilityQuery = $sql->select()->from(array('spirt3' => 'spi_rt_3_facilities'))->columns(array('facility_name'))
                ->where(array('spirt3.facility_name' => $params['defaultFacilityName']));
            $facilityQueryStr = $sql->buildSqlString($facilityQuery);
            $facilityResult = $dbAdapter->query($facilityQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
            if ($facilityResult) {
                $data = array(
                    'facility_id' => $params['facilityId'],
                    'facility_name' => $params['editFacilityName'],
                );
                $facilityDb->update($data, array('facility_name' => $params['defaultFacilityName']));
            }

            $aQuery = $sql->select()->from(array('spiv5' => 'spi_form_v_6'))->columns(array('facilityname'))
                ->where(array('spiv5.facilityname' => $params['defaultFacilityName']));
            $aQueryStr = $sql->buildSqlString($aQuery);
            $aResult = $dbAdapter->query($aQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
            if ($aResult) {
                $data = array(
                    'facilityid' => $params['facilityId'],
                    'facilityname' => $params['editFacilityName'],
                );
                $this->update($data, array('facilityname' => $params['defaultFacilityName']));
            }
        }
        $c = count($params['upFaciltyName']);
        for ($i = 0; $i < $c; $i++) {
            $aQuery = $sql->select()->from(array('spiv5' => 'spi_form_v_6'))->columns(array('facilityname', 'id'))
                ->where(array('spiv5.facilityname' => $params['upFaciltyName'][$i]));
            $aQueryStr = $sql->buildSqlString($aQuery);
            $aResult = $dbAdapter->query($aQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();

            if (count($aResult) > 0) {
                $counter = count($aResult);
                for ($k = 0; $k < $counter; $k++) {
                    $data = array(
                        'facilityid' => $params['facilityId'],
                        'facilityname' => $params['editFacilityName'],
                    );
                    $result = $this->update($data, array('id' => $aResult[$k]['id']));
                }
            }
            //Update status in Facility table
            $facilityQuery = $sql->select()->from(array('spirt3' => 'spi_rt_3_facilities'))->columns(array('facility_name'))
                ->where(array('spirt3.facility_name' => $params['upFaciltyName'][$i]));
            $facilityQueryStr = $sql->buildSqlString($facilityQuery);
            $facilityResult = $dbAdapter->query($facilityQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
            if ($facilityResult) {
                $facilityDb->update(array('status' => 'deleted'), array('facility_name' => $params['upFaciltyName'][$i]));
            }
        }
        return $result;
    }

    //get all faciltiy name
    public function fetchAllFacilityNames()
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $uQuery = $sql->select()->from(array('spiv5' => 'spi_form_v_6'))->columns(array('facilityname' => new Expression("DISTINCT facilityname")))
            ->order('spiv5.facilityname ASC');
        $uQueryStr = $sql->buildSqlString($uQuery);
        $uResult = $dbAdapter->query($uQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();

        //$aQuery = $sql->select()->from(array('spirt5' => 'spi_rt_5_facilities'))
        $aQuery = $sql->select()->from(array('spirt3' => 'spi_rt_3_facilities'))
            ->where('spirt3.status != "deleted"');
        $aQueryStr = $sql->buildSqlString($aQuery);
        $aResult = $dbAdapter->query($aQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();

        return array('uniqueName' => $uResult, 'allName' => $aResult);
    }

    public function fetchAllTestingPointsBasedOnFacility($parameters, $acl)
    {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */

        $aColumns = array('facilityid', 'facilityname', 'testingpointname', "DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", 'AUDIT_SCORE_PERCENTAGE');
        $orderColumns = array('facilityid', 'facilityname', 'testingpointname', 'assesmentofaudit', 'AUDIT_SCORE_PERCENTAGE');

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
            for ($i = 0; $i < (int) $parameters['iSortingCols']; $i++) {
                if ($parameters['bSortable_' . (int) $parameters['iSortCol_' . $i]] == "true") {
                    $sOrder .= $orderColumns[(int) $parameters['iSortCol_' . $i]] . " " . ($parameters['sSortDir_' . $i]) . ",";
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
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' OR ";
                    } else {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' ";
                    }
                }
                $sWhereSub .= ")";
            }
            $sWhere .= $sWhereSub;
        }
        /* Individual column filtering */
        $counter = count($aColumns);

        /* Individual column filtering */
        for ($i = 0; $i < $counter; $i++) {
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
        $sql = new Sql($this->adapter);
        $startDate = "";
        $endDate = "";
        $sQuery = $sql->select()->from('spi_form_v_3')->columns(array('id', 'facilityid', 'facilityname', 'testingpointname', 'testingpointtype', 'assesmentofaudit', 'AUDIT_SCORE_PERCENTAGE'));

        if (isset($parameters['fieldName']) && $parameters['fieldName'] == 'facilityId') {
            $sQuery = $sQuery->where(array('facilityid' => $parameters['val']));
        } elseif (isset($parameters['fieldName']) && $parameters['fieldName'] == 'facilityName') {
            $sQuery = $sQuery->where(array('facilityname' => $parameters['val']));
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

        $sQueryStr = $sql->buildSqlString($sQuery); // Get the string of the Sql, instead of the Select-instance
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->buildSqlString($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $tQuery = $sql->select()->from('spi_form_v_3');
        if (isset($parameters['fieldName']) && $parameters['fieldName'] == 'facilityId') {
            $tQuery = $tQuery->where(array('facilityid' => $parameters['val']));
        } elseif (isset($parameters['fieldName']) && $parameters['fieldName'] == 'facilityName') {
            $tQuery = $tQuery->where(array('facilityname' => $parameters['val']));
        }
        $tQueryStr = $sql->buildSqlString($tQuery); // Get the string of the Sql, instead of the Select-instance

        $tResult = $dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);
        $iTotal = count($tResult);
        $output = array(
            "sEcho" => (int) $parameters['sEcho'],
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array(),
        );
        $loginContainer = new Container('credo');
        $role = $loginContainer->roleCode;
        $downloadPdfAction = (bool) $acl->isAllowed($role, 'Application\Controller\SpiV3Controller', 'download-pdf');

        foreach ($rResult as $aRow) {
            $row = [];
            $downloadPdf = "";

            $row[] = $aRow['facilityid'];
            $row[] = ucwords($aRow['facilityname']);
            $row[] = $aRow['testingpointtype'];
            $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
            $row[] = round($aRow['AUDIT_SCORE_PERCENTAGE'], 2);
            if ($downloadPdfAction) {
                $downloadPdf = '<br><a href="javascript:void(0);" onclick="downloadPdf(' . $aRow['id'] . ')" style="white-space:nowrap;"><i class="fa fa-download"></i> PDF</a>';
            }
            $row[] = $downloadPdf;
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function fetchFacilitiesAudits($params)
    {
        $audits = [];
        $aResult = "";
        if (isset($params['facilityName']) && trim($params['facilityName']) != '') {
            $dbAdapter = $this->adapter;
            $sql = new Sql($this->adapter);
            $query = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
                ->columns(array('id', 'assesmentofaudit', 'form_metadata'))
                ->where(array('spiv6.facilityname' => $params['facilityName'], 'spiv6.status' => 'approved'));
            if (isset($params['showEmailAudits']) && $params['showEmailAudits'] != '') {
                if ($params['showEmailAudits'] == 'yes') {
                    // Query where 'audit_printed' is set to 'yes'
                    $query = $query->where("JSON_UNQUOTE(JSON_EXTRACT(spiv6.form_metadata, '$.audit_email_sent')) = 'yes'");
                } elseif ($params['showEmailAudits'] == 'no') {
                    // Query where 'audit_email_sent' is not 'yes' or not set (grouping with parentheses)
                    $query = $query->where(
                        "(
                            JSON_UNQUOTE(JSON_EXTRACT(spiv6.form_metadata, '$.audit_email_sent')) != 'yes'
                            OR JSON_EXTRACT(spiv6.form_metadata, '$.audit_email_sent') IS NULL
                        )"
                    );
                }
            }
            $query->order("id DESC");
            $queryStr = $sql->buildSqlString($query);
            $audits = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();

            //$aQuery = $sql->select()->from(array('spiv5_fclt' => 'spi_rt_5_facilities'))
            $aQuery = $sql->select()->from(array('spiv3_fclt' => 'spi_rt_3_facilities'))
                ->columns(array('facility_name', 'email'))
                ->where(array('spiv3_fclt.facility_name' => $params['facilityName']));
            $aQueryStr = $sql->buildSqlString($aQuery);
            $aResult = $dbAdapter->query($aQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
        }
        return array('audits' => $audits, 'facilityProfile' => $aResult);
    }

    public function getSpiV6PendingCount()
    {
        $loginContainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
            ->where('spiv6.status = "pending"');
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where('spiv6.token IN ("' . implode('", "', $loginContainer->token) . '")');
        }
        $sQueryStr = $sql->buildSqlString($sQuery);
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        return count($rResult);
    }

    public function deleteAuditRowData($params)
    {
        $result = 0;
        $loginContainer = new Container('credo');
        $user_name = $loginContainer->login;
        $dbAdapter = $this->adapter;
        $eventTable = new EventLogTable($dbAdapter);
        if (trim($params['deleteId']) != "" && trim($params['deleteId']) != '') {
            $data = array('status' => 'deleted');
            $result = $this->update($data, array('id' => $params['deleteId']));
            $subject = '';
            $eventType = 'Delete-SPI RT Form 6';
            $action = $user_name . ' has deleted the SPI RT Form 6 information ' . $params['deleteId'];
            $resourceName = 'SPI-RT-Form-6';
            $eventTable->addEventLog($subject, $eventType, $action, $resourceName);
        }
        return $result;
    }

    public function fetchAllApprovedSubmissionsDetailsBasedOnAuditDate($parameters, $acl)
    {
        $loginContainer = new Container('credo');
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */

        $aColumns = array('facilityname', 'auditroundno', 'assesmentofaudit', 'testingpointname', 'testingpointtype', 'level', 'affiliation', 'AUDIT_SCORE_PERCENTAGE', 'status');
        $orderColumns = array('facilityname', 'auditroundno', 'assesmentofaudit', 'testingpointname', 'testingpointtype', 'level', 'affiliation', 'AUDIT_SCORE_PERCENTAGE', 'status');

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
            for ($i = 0; $i < (int) $parameters['iSortingCols']; $i++) {
                if ($parameters['bSortable_' . (int) $parameters['iSortCol_' . $i]] == "true") {
                    $sOrder .= $orderColumns[(int) $parameters['iSortCol_' . $i]] . " " . ($parameters['sSortDir_' . $i]) . ",";
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
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' OR ";
                    } else {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' ";
                    }
                }
                $sWhereSub .= ")";
            }
            $sWhere .= $sWhereSub;
        }
        /* Individual column filtering */
        $counter = count($aColumns);

        /* Individual column filtering */
        for ($i = 0; $i < $counter; $i++) {
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
        $sql = new Sql($this->adapter);
        $startDate = "";
        $endDate = "";
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->where('spiv3.status != "deleted"');

        $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->where('spiv3.status != "deleted"');

        if (isset($parameters['assesmentOfAuditDate']) && $parameters['assesmentOfAuditDate'] != '') {
            $sQuery = $sQuery->where("spiv3.assesmentofaudit='" . CommonService::isoDateFormat($parameters['assesmentOfAuditDate']) . "'");
            $tQuery = $tQuery->where("spiv3.assesmentofaudit='" . CommonService::isoDateFormat($parameters['assesmentOfAuditDate']) . "'");
        }
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where('spiv3.token IN ("' . implode('", "', $loginContainer->token) . '")');
            $tQuery = $tQuery->where('spiv3.token IN ("' . implode('", "', $loginContainer->token) . '")');
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

        $sQueryStr = $sql->buildSqlString($sQuery); // Get the string of the Sql, instead of the Select-instance
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->buildSqlString($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */

        $tQueryStr = $sql->buildSqlString($tQuery); // Get the string of the Sql, instead of the Select-instance
        $tResult = $dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);
        $iTotal = count($tResult);
        $output = array(
            "sEcho" => (int) $parameters['sEcho'],
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array(),
        );

        $role = $loginContainer->roleCode;
        $downloadPdfAction = (bool) $acl->isAllowed($role, 'Application\Controller\SpiV3Controller', 'download-pdf');

        $approveStatusAction = (bool) $acl->isAllowed($role, 'Application\Controller\SpiV3Controller', 'approve-status');


        foreach ($rResult as $aRow) {
            $row = [];
            $downloadPdf = "";

            $level = isset($aRow['level_other']) && $aRow['level_other'] != "" ? " - " . $aRow['level_other'] : '';

            $row[] = $aRow['facilityname'];
            $row[] = $aRow['auditroundno'];
            $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
            $row[] = (isset($aRow['testingpointname']) && $aRow['testingpointname'] != "" ? $aRow['testingpointname'] : $aRow['testingpointtype']);
            $row[] = $aRow['testingpointtype'];
            $row[] = $aRow['level'] . $level;
            $row[] = $aRow['affiliation'];
            $row[] = round($aRow['AUDIT_SCORE_PERCENTAGE'], 2);
            $row[] = ucwords($aRow['status']);
            //$print = '<a href="/spi-v3/print/' . $aRow['id'] . '" target="_blank" style="white-space:nowrap;"><i class="fa fa-print"></i> Print</a>';

            if ($downloadPdfAction) {
                $downloadPdf = '<br><a href="javascript:void(0);" onclick="downloadPdf(' . $aRow['id'] . ')" style="white-space:nowrap;"><i class="fa fa-download"></i> PDF</a>';
            }
            //$pending = '<br><a href="/spi-v3/edit/' . $aRow['id'] . '" style="white-space:nowrap;"><i class="fa fa-pencil"></i> Edit</a>';
            $row[] = $downloadPdf;
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function fetchSpiV3FormUniqueTokens()
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $tokenQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->columns(array(new Expression('DISTINCT(token) as token')))
            ->group('token')
            ->order("token ASC");
        $tokenQueryStr = $sql->buildSqlString($tokenQuery);
        return $dbAdapter->query($tokenQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    }

    // public function fetchViewDataDetails($parameters)
    // {
    //     $loginContainer = new Container('credo');
    //     $queryContainer = new Container('query');
    //     /* Array of database columns which should be read and sent back to DataTables. Use a space where
    //      * you want to insert a non-database field (for example a counter or static image)
    //      */
    //     if ($parameters['source'] == 'hv') {
    //         $aColumns = array("DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", 'facilityname', 'testingpointname', 'client_tested_HIV_PM', 'NumberofTester');
    //         $orderColumns = array('assesmentofaudit', 'facilityname', 'testingpointname', 'client_tested_HIV_PM', 'NumberofTester');
    //     } elseif ($parameters['source'] == 'la') {
    //         $aColumns = array("DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", 'facilityname', 'testingpointname', 'AUDIT_SCORE_PERCENTAGE', 'client_tested_HIV_PM');
    //         $orderColumns = array('assesmentofaudit', 'facilityname', 'testingpointname', 'AUDIT_SCORE_PERCENTAGE', 'client_tested_HIV_PM');
    //     } elseif ($parameters['source'] == 'ad') {
    //         $aColumns = array("DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", "DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')");
    //         $orderColumns = array('assesmentofaudit', 'assesmentofaudit');
    //     } elseif ($parameters['source'] == 'apall' || $parameters['source'] == 'apl180' || $parameters['source'] == 'ap') {
    //         $aColumns = array('facilityid', 'facilityname', 'AUDIT_SCORE_PERCENTAGE', "DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", 'testingpointtype', 'testingpointtype_other', 'level', 'affiliation', 'AUDIT_SCORE_PERCENTAGE', 'AUDIT_SCORE_PERCENTAGE');
    //         $orderColumns = array('facilityid', 'facilityname', 'AUDIT_SCORE_PERCENTAGE', 'assesmentofaudit', 'testingpointtype', 'level', 'affiliation', 'AUDIT_SCORE_PERCENTAGE', 'AUDIT_SCORE_PERCENTAGE');
    //     } elseif ($parameters['source'] == 'apspi') {
    //         $aColumns = array("DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", 'PERSONAL_SCORE', 'PHYSICAL_SCORE', 'SAFETY_SCORE', 'PRETEST_SCORE', 'TEST_SCORE', 'POST_SCORE', 'EQA_SCORE');
    //         $orderColumns = array('assesmentofaudit', 'PERSONAL_SCORE', 'PHYSICAL_SCORE', 'SAFETY_SCORE', 'PRETEST_SCORE', 'TEST_SCORE', 'POST_SCORE', 'EQA_SCORE');
    //     }

    //     /*
    //      * Paging
    //      */
    //     $sLimit = "";
    //     if (isset($parameters['iDisplayStart']) && $parameters['iDisplayLength'] != '-1') {
    //         $sOffset = $parameters['iDisplayStart'];
    //         $sLimit = $parameters['iDisplayLength'];
    //     }

    //     /*
    //      * Ordering
    //      */

    //     $sOrder = "";
    //     if (isset($parameters['iSortCol_0'])) {
    //         for ($i = 0; $i < (int) $parameters['iSortingCols']; $i++) {
    //             if ($parameters['bSortable_' . (int) $parameters['iSortCol_' . $i]] == "true") {
    //                 $sOrder .= $orderColumns[(int) $parameters['iSortCol_' . $i]] . " " . ($parameters['sSortDir_' . $i]) . ",";
    //             }
    //         }
    //         $sOrder = substr_replace($sOrder, "", -1);
    //     }

    //     /*
    //      * Filtering
    //      * NOTE this does not match the built-in DataTables filtering which does it
    //      * word by word on any field. It's possible to do here, but concerned about efficiency
    //      * on very large tables, and MySQL's regex functionality is very limited
    //      */

    //     $sWhere = "";
    //     if (isset($parameters['sSearch']) && $parameters['sSearch'] != "") {
    //         $searchArray = explode(" ", $parameters['sSearch']);
    //         $sWhereSub = "";
    //         foreach ($searchArray as $search) {
    //             if ($sWhereSub == "") {
    //                 $sWhereSub .= "(";
    //             } else {
    //                 $sWhereSub .= " AND (";
    //             }
    //             $colSize = count($aColumns);

    //             for ($i = 0; $i < $colSize; $i++) {
    //                 if ($i < $colSize - 1) {
    //                     $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' OR ";
    //                 } else {
    //                     $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' ";
    //                 }
    //             }
    //             $sWhereSub .= ")";
    //         }
    //         $sWhere .= $sWhereSub;
    //     }
    //     /* Individual column filtering */
    //     $counter = count($aColumns);

    //     /* Individual column filtering */
    //     for ($i = 0; $i < $counter; $i++) {
    //         if (isset($parameters['bSearchable_' . $i]) && $parameters['bSearchable_' . $i] == "true" && $parameters['sSearch_' . $i] != '') {
    //             if ($sWhere == "") {
    //                 $sWhere .= $aColumns[$i] . " LIKE '%" . ($parameters['sSearch_' . $i]) . "%' ";
    //             } else {
    //                 $sWhere .= " AND " . $aColumns[$i] . " LIKE '%" . ($parameters['sSearch_' . $i]) . "%' ";
    //             }
    //         }
    //     }

    //     /*
    //      * SQL queries
    //      * Get data to display
    //      */
    //     $dbAdapter = $this->adapter;
    //     $sql = new Sql($this->adapter);
    //     $startDate = "";
    //     $endDate = "";
    //     if (isset($parameters['drange']) && ($parameters['drange'] != "")) {
    //         $dateField = explode(" ", $parameters['drange']);
    //         if (isset($dateField[0]) && trim($dateField[0]) != "") {
    //             $startDate = CommonService::isoDateFormat($dateField[0]);
    //         }
    //         if (isset($dateField[2]) && trim($dateField[2]) != "") {
    //             $endDate = CommonService::isoDateFormat($dateField[2]);
    //         }
    //     }

    //     if ($parameters['source'] == 'ad') {
    //         //For Audit Dates
    //         $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
    //             ->columns(array(new Expression('DISTINCT(assesmentofaudit) as assesmentofaudit'), 'totalDataPoints' => new Expression("COUNT(*)")))
    //             ->where(array('spiv3.status' => 'approved'))
    //             ->group('spiv3.assesmentofaudit');
    //         $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
    //             ->columns(array(new Expression('DISTINCT(assesmentofaudit) as assesmentofaudit'), 'totalDataPoints' => new Expression("COUNT(*)")))
    //             ->where(array('spiv3.status' => 'approved'))
    //             ->group('spiv3.assesmentofaudit');
    //     } elseif ($parameters['source'] == 'apall' || $parameters['source'] == 'apl180' || $parameters['source'] == 'ap') {
    //         if (isset($parameters['date']) && ($parameters['date'] != "")) {
    //             $dateField = explode(" ", $parameters['date']);
    //             //print_r($proceed_date);die;
    //             if (isset($dateField[0]) && trim($dateField[0]) != "") {
    //                 $startDate = CommonService::isoDateFormat($dateField[0]);
    //             }
    //             if (isset($dateField[2]) && trim($dateField[2]) != "") {
    //                 $endDate = CommonService::isoDateFormat($dateField[2]);
    //             }
    //         }
    //         //For Audit Performance Row
    //         $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
    //             ->columns(array('facilityid', 'facilityname', 'auditroundno', 'assesmentofaudit', 'client_tested_HIV_PM', 'testingpointtype', 'testingpointtype_other', 'level', 'affiliation', 'AUDIT_SCORE_PERCENTAGE'))
    //             ->where(array('spiv3.status' => 'approved'));
    //         $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
    //             ->columns(array('facilityid', 'facilityname', 'auditroundno', 'assesmentofaudit', 'client_tested_HIV_PM', 'testingpointtype', 'testingpointtype_other', 'level', 'affiliation', 'AUDIT_SCORE_PERCENTAGE'))
    //             ->where(array('spiv3.status' => 'approved'));
    //         if ($parameters['source'] == 'apl180') {
    //             $sQuery = $sQuery->where("(`assesmentofaudit` BETWEEN CURDATE() - INTERVAL 180 DAY AND CURDATE())");
    //             $tQuery = $tQuery->where("(`assesmentofaudit` BETWEEN CURDATE() - INTERVAL 180 DAY AND CURDATE())");
    //         }
    //         if (isset($parameters['testPoint']) && trim($parameters['testPoint']) != '') {
    //             $sQuery = $sQuery->where("spiv3.testingpointtype='" . $parameters['testPoint'] . "'");
    //             $tQuery = $tQuery->where("spiv3.testingpointtype='" . $parameters['testPoint'] . "'");
    //             if (isset($parameters['testPointName']) && trim($parameters['testPointName']) != '') {
    //                 if (trim($parameters['testPoint']) != 'other') {
    //                     $sQuery = $sQuery->where("spiv3.testingpointname='" . $parameters['testPointName'] . "'");
    //                     $tQuery = $tQuery->where("spiv3.testingpointname='" . $parameters['testPointName'] . "'");
    //                 } else {
    //                     $sQuery = $sQuery->where("spiv3.testingpointtype_other='" . $parameters['testPointName'] . "'");
    //                     $tQuery = $tQuery->where("spiv3.testingpointtype_other='" . $parameters['testPointName'] . "'");
    //                 }
    //             }
    //         }
    //         if (isset($parameters['auditRndNo']) && $parameters['auditRndNo'] != '') {
    //             $sQuery = $sQuery->where("spiv3.auditroundno='" . $parameters['auditRndNo'] . "'");
    //             $tQuery = $tQuery->where("spiv3.auditroundno='" . $parameters['auditRndNo'] . "'");
    //         }
    //         if (isset($parameters['scoreLevel']) && $parameters['scoreLevel'] != '') {
    //             if ($parameters['scoreLevel'] == 0) {
    //                 $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE < 40");
    //                 $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE < 40");
    //             } elseif ($parameters['scoreLevel'] == 1) {
    //                 $sQuery = $sQuery->where("ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) >= 40 AND ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) <= 59");
    //                 $tQuery = $tQuery->where("ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) >= 40 AND ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) <= 59");
    //             } elseif ($parameters['scoreLevel'] == 2) {
    //                 $sQuery = $sQuery->where("ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) >= 60 AND ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) <= 79");
    //                 $tQuery = $tQuery->where("ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) >= 60 AND ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) <= 79");
    //             } elseif ($parameters['scoreLevel'] == 3) {
    //                 $sQuery = $sQuery->where("ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) >= 80 AND ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) <= 89");
    //                 $tQuery = $tQuery->where("ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) >= 80 AND ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) <= 89");
    //             } elseif ($parameters['scoreLevel'] == 4) {
    //                 $sQuery = $sQuery->where("ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) >= 90");
    //                 $tQuery = $tQuery->where("ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) >= 90");
    //             }
    //         }
    //         if (isset($parameters['level']) && $parameters['level'] != '') {
    //             $sQuery = $sQuery->where("spiv3.level='" . $parameters['level'] . "'");
    //             $tQuery = $tQuery->where("spiv3.level='" . $parameters['level'] . "'");
    //         }
    //         if (isset($parameters['province']) && $parameters['province'] != '') {
    //             $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('province'))
    //                 ->where("f.province='" . $parameters['province'] . "'");
    //             $tQuery = $tQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('province'))
    //                 ->where("f.province='" . $parameters['province'] . "'");
    //         }
    //         if (isset($parameters['district']) && $parameters['district'] != '') {
    //             $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('district'))
    //                 ->where("f.district='" . $parameters['district'] . "'");
    //             $tQuery = $tQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('district'))
    //                 ->where("f.district='" . $parameters['district'] . "'");
    //         }
    //         if (isset($parameters['affiliation']) && $parameters['affiliation'] != '') {
    //             $sQuery = $sQuery->where("spiv3.affiliation='" . $parameters['affiliation'] . "'");
    //             $tQuery = $tQuery->where("spiv3.affiliation='" . $parameters['affiliation'] . "'");
    //         }
    //     } elseif ($parameters['source'] == 'apspi') {
    //         //For Audit Performance
    //         $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
    //             ->columns(array('assesmentofaudit', 'client_tested_HIV_PM', 'PERSONAL_SCORE' => new Expression('AVG(PERSONAL_SCORE)'), 'PHYSICAL_SCORE' => new Expression('AVG(PHYSICAL_SCORE)'), 'SAFETY_SCORE' => new Expression('AVG(SAFETY_SCORE)'), 'PRETEST_SCORE' => new Expression('AVG(PRETEST_SCORE)'), 'TEST_SCORE' => new Expression('AVG(TEST_SCORE)'), 'POST_SCORE' => new Expression('AVG(POST_SCORE)'), 'EQA_SCORE' => new Expression('AVG(EQA_SCORE)')))
    //             ->group('spiv3.assesmentofaudit');
    //         $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
    //             ->columns(array('assesmentofaudit', 'client_tested_HIV_PM', 'PERSONAL_SCORE' => new Expression('AVG(PERSONAL_SCORE)'), 'PHYSICAL_SCORE' => new Expression('AVG(PHYSICAL_SCORE)'), 'SAFETY_SCORE' => new Expression('AVG(SAFETY_SCORE)'), 'PRETEST_SCORE' => new Expression('AVG(PRETEST_SCORE)'), 'TEST_SCORE' => new Expression('AVG(TEST_SCORE)'), 'POST_SCORE' => new Expression('AVG(POST_SCORE)'), 'EQA_SCORE' => new Expression('AVG(EQA_SCORE)')))
    //             ->group('spiv3.assesmentofaudit');
    //         if (isset($parameters['roundno']) && $parameters['roundno'] != '') {
    //             $xplodRoundNo = explode(",", $parameters['roundno']);
    //             $sQuery = $sQuery->where('spiv3.auditroundno IN ("' . implode('", "', $xplodRoundNo) . '")');
    //             $tQuery = $tQuery->where('spiv3.auditroundno IN ("' . implode('", "', $xplodRoundNo) . '")');
    //         }
    //     } else {
    //         //For Others
    //         $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
    //             ->columns(array('assesmentofaudit', 'facilityname', 'testingpointtype', 'client_tested_HIV_PM', 'NumberofTester', 'AUDIT_SCORE_PERCENTAGE'));

    //         $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
    //             ->columns(array('assesmentofaudit', 'facilityname', 'testingpointtype', 'client_tested_HIV_PM', 'NumberofTester', 'AUDIT_SCORE_PERCENTAGE'));
    //     }

    //     if (!empty($loginContainer->token)) {
    //         $sQuery = $sQuery->where('spiv3.token IN ("' . implode('", "', $loginContainer->token) . '")');
    //         $tQuery = $tQuery->where('spiv3.token IN ("' . implode('", "', $loginContainer->token) . '")');
    //     }

    //     if (trim($startDate) != "" && trim($endDate) != "") {
    //         $sQuery = $sQuery->where(array("spiv3.assesmentofaudit >='" . $startDate . "'", "spiv3.assesmentofaudit <='" . $endDate . "'"));
    //         $tQuery = $tQuery->where(array("spiv3.assesmentofaudit >='" . $startDate . "'", "spiv3.assesmentofaudit <='" . $endDate . "'"));
    //     }

    //     if (isset($sWhere) && $sWhere != "") {
    //         $sQuery->where($sWhere);
    //     }

    //     if (trim($sOrder) === 'desc') {
    //         $sOrder = '';
    //     }
    //     if (isset($sOrder) && $sOrder != "") {
    //         $sQuery->order($sOrder);
    //     }

    //     if (isset($sLimit) && isset($sOffset)) {
    //         $sQuery->limit($sLimit);
    //         $sQuery->offset($sOffset);
    //     }
    //     $queryContainer->exportViewDataV6Query = $sQuery;
    //     $sQueryStr = $sql->buildSqlString($sQuery); // Get the string of the Sql, instead of the Select-instance
    //     // echo $sQueryStr;die;
    //     $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);
    //     /* Data set length after filtering */
    //     $sQuery->reset('limit');
    //     $sQuery->reset('offset');
    //     $fQuery = $sql->buildSqlString($sQuery);
    //     $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
    //     $iFilteredTotal = count($aResultFilterTotal);

    //     /* Total data set length */

    //     $tQueryStr = $sql->buildSqlString($tQuery); // Get the string of the Sql, instead of the Select-instance
    //     $tResult = $dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);
    //     // print_r($rResult); die;
    //     $iTotal = count($tResult);
    //     $output = array(
    //         "sEcho" => (int) $parameters['sEcho'],
    //         "iTotalRecords" => $iTotal,
    //         "iTotalDisplayRecords" => $iFilteredTotal,
    //         "aaData" => array(),
    //     );


    //     //$personalScoreArray = [];
    //     //$physicalScoreArray = [];
    //     //$safetyScoreArray = [];
    //     //$preTestScoreArray = [];
    //     //$testScoreArray = [];
    //     //$postTestScoreArray = [];
    //     //$eqaScoreArray = [];
    //     //$personalScore = 0;
    //     //$physicalScore = 0;
    //     //$safetyScore = 0;
    //     //$preTestScore = 0;
    //     //$testScore = 0;
    //     //$postTestScore = 0;
    //     //$eqaScore = 0;
    //     $auditScore = 0;
    //     $levelZero = [];
    //     $levelOne = [];
    //     $levelTwo = [];
    //     $levelThree = [];
    //     $levelFour = [];
    //     foreach ($rResult as $aRow) {
    //         $scorePer = round($aRow['AUDIT_SCORE_PERCENTAGE']);
    //         $row = [];
    //         if ($parameters['source'] == 'hv' || $parameters['source'] == 'la' || $parameters['source'] == 'apall' || $parameters['source'] == 'apl180' || $parameters['source'] == 'ap') {
    //             $auditScore += $aRow['AUDIT_SCORE_PERCENTAGE'];
    //             if (isset($scorePer) && $scorePer < 40) {
    //                 $level = 0;
    //                 $levelZero[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
    //             } elseif (isset($scorePer) && $scorePer >= 40 && $scorePer < 60) {
    //                 $level = 1;
    //                 $levelOne[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
    //             } elseif (isset($scorePer) && $scorePer >= 60 && $scorePer < 80) {
    //                 $level = 2;
    //                 $levelTwo[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
    //             } elseif (isset($scorePer) && $scorePer >= 80 && $scorePer < 90) {
    //                 $level = 3;
    //                 $levelThree[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
    //             } elseif (isset($scorePer) && $scorePer >= 90) {
    //                 $level = 4;
    //                 $levelFour[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
    //             }
    //         }
    //         if ($parameters['source'] == 'hv') {
    //             $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
    //             $row[] = ucwords($aRow['facilityname']);
    //             $row[] = (isset($aRow['testingpointname']) && $aRow['testingpointname'] != "" ? $aRow['testingpointname'] : $aRow['testingpointtype']);
    //             $row[] = (isset($aRow['client_tested_HIV_PM']) ? $aRow['client_tested_HIV_PM'] : 0);
    //             $row[] = (isset($aRow['NumberofTester']) ? $aRow['NumberofTester'] : 0);
    //             $row[] = $level;
    //         } elseif ($parameters['source'] == 'la') {
    //             $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
    //             $row[] = ucwords($aRow['facilityname']);
    //             $row[] = (isset($aRow['testingpointname']) && $aRow['testingpointname'] != "" ? $aRow['testingpointname'] : $aRow['testingpointtype']);
    //             $row[] = round($aRow['AUDIT_SCORE_PERCENTAGE'], 2);
    //             $row[] = (isset($aRow['client_tested_HIV_PM']) ? $aRow['client_tested_HIV_PM'] : 0);
    //         } elseif ($parameters['source'] == 'ad') {
    //             $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
    //             $row[] = $aRow['totalDataPoints'];
    //         } elseif ($parameters['source'] == 'apall' || $parameters['source'] == 'apl180' || $parameters['source'] == 'ap') {
    //             $row[] = $aRow['facilityid'];
    //             $row[] = ucwords($aRow['facilityname']);
    //             $row[] = $aRow['auditroundno'];
    //             $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
    //             $row[] = ucwords($aRow['testingpointtype']);
    //             $row[] = ($aRow['testingpointtype'] == 'other') ? ucwords($aRow['testingpointtype_other']) : ucwords($aRow['testingpointname']);
    //             $row[] = ucwords($aRow['level']);
    //             $row[] = ucwords($aRow['affiliation']);
    //             $row[] = $level;
    //             $row[] = round($aRow['AUDIT_SCORE_PERCENTAGE'], 2);
    //         } elseif ($parameters['source'] == 'apspi') {
    //             $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
    //             $row[] = round($aRow['PERSONAL_SCORE'], 2);
    //             $row[] = round($aRow['PHYSICAL_SCORE'], 2);
    //             $row[] = round($aRow['SAFETY_SCORE'], 2);
    //             $row[] = round($aRow['PRETEST_SCORE'], 2);
    //             $row[] = round($aRow['TEST_SCORE'], 2);
    //             $row[] = round($aRow['POST_SCORE'], 2);
    //             $row[] = round($aRow['EQA_SCORE'], 2);
    //             //$personalScoreArray[] = $aRow['PERSONAL_SCORE'];
    //             //$physicalScoreArray[] = $aRow['PHYSICAL_SCORE'];
    //             //$safetyScoreArray[] = $aRow['SAFETY_SCORE'];
    //             //$preTestScoreArray[] = $aRow['PRETEST_SCORE'];
    //             //$testScoreArray[] = $aRow['TEST_SCORE'];
    //             //$postTestScoreArray[] = $aRow['POST_SCORE'];
    //             //$eqaScoreArray[] = $aRow['EQA_SCORE'];
    //             //$personalScore+=$aRow['PERSONAL_SCORE'];
    //             //$physicalScore+=$aRow['PHYSICAL_SCORE'];
    //             //$safetyScore+=$aRow['SAFETY_SCORE'];
    //             //$preTestScore+=$aRow['PRETEST_SCORE'];
    //             //$testScore+=$aRow['TEST_SCORE'];
    //             //$postTestScore+=$aRow['POST_SCORE'];
    //             //$eqaScore+=$aRow['EQA_SCORE'];
    //         }
    //         $output['aaData'][] = $row;
    //     }
    //     $output['avgAuditScore'] = (count($rResult) > 0) ? round($auditScore / count($rResult), 2) : 0;
    //     $output['levelZeroCount'] = count($levelZero);
    //     $output['levelOneCount'] = count($levelOne);
    //     $output['levelTwoCount'] = count($levelTwo);
    //     $output['levelThreeCount'] = count($levelThree);
    //     $output['levelFourCount'] = count($levelFour);
    //     return $output;
    // }

    public function fetchViewDataDetails($parameters)
    {
        $loginContainer = new Container('credo');
        $queryContainer = new Container('query');
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */
        if ($parameters['source'] == 'hv') {
            $aColumns = array("DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", 'facilityname', 'testingpointname', 'client_tested_HIV_PM', 'NumberofTester');
            $orderColumns = array('assesmentofaudit', 'facilityname', 'testingpointname', 'client_tested_HIV_PM', 'NumberofTester');
        } elseif ($parameters['source'] == 'la') {
            $aColumns = array("DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", 'facilityname', 'testingpointname', 'AUDIT_SCORE_PERCENTAGE', 'client_tested_HIV_PM');
            $orderColumns = array('assesmentofaudit', 'facilityname', 'testingpointname', 'AUDIT_SCORE_PERCENTAGE', 'client_tested_HIV_PM');
        } elseif ($parameters['source'] == 'ad') {
            $aColumns = array("DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", "DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')");
            $orderColumns = array('assesmentofaudit', 'assesmentofaudit');
        } elseif ($parameters['source'] == 'apall' || $parameters['source'] == 'apl180' || $parameters['source'] == 'ap') {
            $aColumns = array('facilityid', 'facilityname', 'AUDIT_SCORE_PERCENTAGE', "DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", 'testingpointtype', 'testingpointtype_other', 'level', 'affiliation', 'AUDIT_SCORE_PERCENTAGE', 'AUDIT_SCORE_PERCENTAGE');
            $orderColumns = array('facilityid', 'facilityname', 'AUDIT_SCORE_PERCENTAGE', 'assesmentofaudit', 'testingpointtype', 'level', 'affiliation', 'AUDIT_SCORE_PERCENTAGE', 'AUDIT_SCORE_PERCENTAGE');
        } elseif ($parameters['source'] == 'apspi') {
            $aColumns = array("DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", 'PERSONAL_SCORE', 'PHYSICAL_SCORE', 'SAFETY_SCORE', 'PRETEST_SCORE', 'TEST_SCORE', 'POST_SCORE', 'EQA_SCORE');
            $orderColumns = array('assesmentofaudit', 'PERSONAL_SCORE', 'PHYSICAL_SCORE', 'SAFETY_SCORE', 'PRETEST_SCORE', 'TEST_SCORE', 'POST_SCORE', 'EQA_SCORE');
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
            for ($i = 0; $i < (int) $parameters['iSortingCols']; $i++) {
                if ($parameters['bSortable_' . (int) $parameters['iSortCol_' . $i]] == "true") {
                    $sOrder .= $orderColumns[(int) $parameters['iSortCol_' . $i]] . " " . ($parameters['sSortDir_' . $i]) . ",";
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
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' OR ";
                    } else {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' ";
                    }
                }
                $sWhereSub .= ")";
            }
            $sWhere .= $sWhereSub;
        }
        /* Individual column filtering */
        $counter = count($aColumns);

        /* Individual column filtering */
        for ($i = 0; $i < $counter; $i++) {
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
        $sql = new Sql($this->adapter);
        $startDate = "";
        $endDate = "";
        if (isset($parameters['drange']) && ($parameters['drange'] != "")) {
            $dateField = explode(" ", $parameters['drange']);
            if (isset($dateField[0]) && trim($dateField[0]) != "") {
                $startDate = CommonService::isoDateFormat($dateField[0]);
            }
            if (isset($dateField[2]) && trim($dateField[2]) != "") {
                $endDate = CommonService::isoDateFormat($dateField[2]);
            }
        }
        if ($parameters['source'] == 'ad') {
            //For Audit Dates
            $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array(new Expression('DISTINCT(assesmentofaudit) as assesmentofaudit'), 'totalDataPoints' => new Expression("COUNT(*)")))
                ->where(array('spiv3.status' => 'approved'))
                ->group('spiv3.assesmentofaudit');
            $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array(new Expression('DISTINCT(assesmentofaudit) as assesmentofaudit'), 'totalDataPoints' => new Expression("COUNT(*)")))
                ->where(array('spiv3.status' => 'approved'))
                ->group('spiv3.assesmentofaudit');
        } else if ($parameters['source'] == 'apall' || $parameters['source'] == 'apl180' || $parameters['source'] == 'ap') {
            if (isset($parameters['date']) && ($parameters['date'] != "")) {
                $dateField = explode(" ", $parameters['date']);
                //print_r($proceed_date);die;
                if (isset($dateField[0]) && trim($dateField[0]) != "") {
                    $startDate = CommonService::isoDateFormat($dateField[0]);
                }
                if (isset($dateField[2]) && trim($dateField[2]) != "") {
                    $endDate = CommonService::isoDateFormat($dateField[2]);
                }
            }
            $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array('facilityid', 'facilityname', 'auditroundno', 'assesmentofaudit', 'client_tested_HIV_PM', 'testingpointtype', 'testingpointtype_other', 'level', 'affiliation', 'AUDIT_SCORE_PERCENTAGE' => new Expression('ROUND(AUDIT_SCORE_PERCENTAGE)')))
                ->where(array('spiv3.status' => 'approved'));
            $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array('facilityid', 'facilityname', 'auditroundno', 'assesmentofaudit', 'client_tested_HIV_PM', 'testingpointtype', 'testingpointtype_other', 'level', 'affiliation', 'AUDIT_SCORE_PERCENTAGE' => new Expression('ROUND(AUDIT_SCORE_PERCENTAGE)')))
                ->where(array('spiv3.status' => 'approved'));
            if ($parameters['source'] == 'apl180') {
                $sQuery = $sQuery->where("(`assesmentofaudit` BETWEEN CURDATE() - INTERVAL 180 DAY AND CURDATE())");
                $tQuery = $tQuery->where("(`assesmentofaudit` BETWEEN CURDATE() - INTERVAL 180 DAY AND CURDATE())");
            }
            if (isset($parameters['testPoint']) && trim($parameters['testPoint']) != '') {
                $sQuery = $sQuery->where("spiv3.testingpointtype='" . $parameters['testPoint'] . "'");
                $tQuery = $tQuery->where("spiv3.testingpointtype='" . $parameters['testPoint'] . "'");
                if (isset($parameters['testPointName']) && trim($parameters['testPointName']) != '') {
                    if (trim($parameters['testPoint']) != 'other') {
                        $sQuery = $sQuery->where("spiv3.testingpointname='" . $parameters['testPointName'] . "'");
                        $tQuery = $tQuery->where("spiv3.testingpointname='" . $parameters['testPointName'] . "'");
                    } else {
                        $sQuery = $sQuery->where("spiv3.testingpointtype_other='" . $parameters['testPointName'] . "'");
                        $tQuery = $tQuery->where("spiv3.testingpointtype_other='" . $parameters['testPointName'] . "'");
                    }
                }
            }
            if (isset($parameters['auditRndNo']) && $parameters['auditRndNo'] != '') {
                $sQuery = $sQuery->where("spiv3.auditroundno='" . $parameters['auditRndNo'] . "'");
                $tQuery = $tQuery->where("spiv3.auditroundno='" . $parameters['auditRndNo'] . "'");
            }
            if (isset($parameters['scoreLevel']) && $parameters['scoreLevel'] != '') {
                if ($parameters['scoreLevel'] == 0) {
                    $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE < 40");
                    $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE < 40");
                } elseif ($parameters['scoreLevel'] == 1) {
                    $sQuery = $sQuery->where("ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) >= 40 AND ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) <= 59");
                    $tQuery = $tQuery->where("ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) >= 40 AND ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) <= 59");
                } elseif ($parameters['scoreLevel'] == 2) {
                    $sQuery = $sQuery->where("ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) >= 60 AND ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) <= 79");
                    $tQuery = $tQuery->where("ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) >= 60 AND ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) <= 79");
                } elseif ($parameters['scoreLevel'] == 3) {
                    $sQuery = $sQuery->where("ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) >= 80 AND ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) <= 89");
                    $tQuery = $tQuery->where("ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) >= 80 AND ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) <= 89");
                } elseif ($parameters['scoreLevel'] == 4) {
                    $sQuery = $sQuery->where("ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) >= 90");
                    $tQuery = $tQuery->where("ROUND(spiv3.AUDIT_SCORE_PERCENTAGE) >= 90");
                }
            }
            if (isset($parameters['level']) && $parameters['level'] != '') {
                $sQuery = $sQuery->where("spiv3.level='" . $parameters['level'] . "'");
                $tQuery = $tQuery->where("spiv3.level='" . $parameters['level'] . "'");
            }

        } elseif ($parameters['source'] == 'apspi') {
            //For Audit Performance
            $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array('assesmentofaudit', 'client_tested_HIV_PM', 'PERSONAL_SCORE' => new Expression('AVG(PERSONAL_SCORE)'), 'PHYSICAL_SCORE' => new Expression('AVG(PHYSICAL_SCORE)'), 'SAFETY_SCORE' => new Expression('AVG(SAFETY_SCORE)'), 'PRETEST_SCORE' => new Expression('AVG(PRETEST_SCORE)'), 'TEST_SCORE' => new Expression('AVG(TEST_SCORE)'), 'POST_SCORE' => new Expression('AVG(POST_SCORE)'), 'EQA_SCORE' => new Expression('AVG(EQA_SCORE)')))
                ->group('spiv3.assesmentofaudit');
            $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array('assesmentofaudit', 'client_tested_HIV_PM', 'PERSONAL_SCORE' => new Expression('AVG(PERSONAL_SCORE)'), 'PHYSICAL_SCORE' => new Expression('AVG(PHYSICAL_SCORE)'), 'SAFETY_SCORE' => new Expression('AVG(SAFETY_SCORE)'), 'PRETEST_SCORE' => new Expression('AVG(PRETEST_SCORE)'), 'TEST_SCORE' => new Expression('AVG(TEST_SCORE)'), 'POST_SCORE' => new Expression('AVG(POST_SCORE)'), 'EQA_SCORE' => new Expression('AVG(EQA_SCORE)')))
                ->group('spiv3.assesmentofaudit');
            if (isset($parameters['roundno']) && $parameters['roundno'] != '') {
                $xplodRoundNo = explode(",", $parameters['roundno']);
                $sQuery = $sQuery->where('spiv3.auditroundno IN ("' . implode('", "', $xplodRoundNo) . '")');
                $tQuery = $tQuery->where('spiv3.auditroundno IN ("' . implode('", "', $xplodRoundNo) . '")');
            }
        } else {
            //For Others
            $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array('assesmentofaudit', 'facilityname', 'testingpointtype', 'client_tested_HIV_PM', 'NumberofTester', 'AUDIT_SCORE_PERCENTAGE'));

            $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array('assesmentofaudit', 'facilityname', 'testingpointtype', 'client_tested_HIV_PM', 'NumberofTester', 'AUDIT_SCORE_PERCENTAGE'));
        }

        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where('spiv3.token IN ("' . implode('", "', $loginContainer->token) . '")');
            $tQuery = $tQuery->where('spiv3.token IN ("' . implode('", "', $loginContainer->token) . '")');
        }

        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType != '') {
            // Mapping exists
            if ($loginContainer->userMappingType === 'country' && $parameters['country'] == '') {
                // User mapped by country: show mapped country data and all related provinces and districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('country', 'province', 'district'))
                    ->where('f.country IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
                $tQuery = $tQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('country', 'province', 'district'))
                    ->where('f.country IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'province' && $parameters['province'] == '') {
                // User mapped by province: show only mapped provinces
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('province'))
                    ->where('f.province IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
                $tQuery = $tQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('province'))
                    ->where('f.province IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'district' && $parameters['district'] == '') {
                // User mapped by district: show only mapped districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('district'))
                    ->where('f.district IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
                $tQuery = $tQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('district'))
                    ->where('f.district IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            }
        }
        if ((isset($parameters['country']) && $parameters['country'] != '') || (isset($parameters['province']) && $parameters['province'] != '') || (isset($parameters['district']) && $parameters['district'] != '')) {
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('country', 'province', 'district'));
            $tQuery = $tQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('country', 'province', 'district'));

            // Apply filters dynamically
            if (isset($parameters['country']) && $parameters['country'] != '') {
                $sQuery = $sQuery->where('f.country IN (' . $parameters['country'] . ')');
                $tQuery = $tQuery->where('f.country IN (' . $parameters['country'] . ')');
            }
            if (isset($parameters['province']) && $parameters['province'] != '') {
                $sQuery = $sQuery->where('f.province IN (' . $parameters['province'] . ')');
                $tQuery = $tQuery->where('f.province IN (' . $parameters['province'] . ')');
            }
            if (isset($parameters['district']) && $parameters['district'] != '') {
                $sQuery = $sQuery->where('f.district IN (' . $parameters['district'] . ')');
                $tQuery = $tQuery->where('f.district IN (' . $parameters['district'] . ')');
            }
        }

        if (trim($startDate) != "" && trim($endDate) != "") {
            $sQuery = $sQuery->where(array("spiv3.assesmentofaudit >='" . $startDate . "'", "spiv3.assesmentofaudit <='" . $endDate . "'"));
            $tQuery = $tQuery->where(array("spiv3.assesmentofaudit >='" . $startDate . "'", "spiv3.assesmentofaudit <='" . $endDate . "'"));
        }

        if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }

        if (trim($sOrder) === 'desc') {
            $sOrder = '';
        }
        if (isset($sOrder) && $sOrder != "") {
            $sQuery->order($sOrder);
        }

        // if (isset($sLimit) && isset($sOffset)) {
        //     $sQuery->limit($sLimit);
        //     $sQuery->offset($sOffset);
        // }

        $queryContainer->exportViewDataV6Query = $sQuery;
        $sQueryStr = $sql->buildSqlString($sQuery); // Get the string of the Sql, instead of the Select-instance
        //print_r($sQueryStr)
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->buildSqlString($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */

        $tQueryStr = $sql->buildSqlString($tQuery); // Get the string of the Sql, instead of the Select-instance
        $tResult = $dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);
        // print_r($rResult); die;
        $iTotal = count($tResult);
        $output = array(
            "sEcho" => (int) $parameters['sEcho'],
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array(),
        );

        /* Data set length after filtering */

        $auditScore = 0;
        $levelZero = 0;
        $levelOne = 0;
        $levelTwo = 0;
        $levelThree = 0;
        $levelFour = 0;

        foreach ($rResult as $aRow) {
            $scorePer = $aRow['AUDIT_SCORE_PERCENTAGE'];
            $auditScore += $scorePer;
            $row = [];

            if ($parameters['source'] == 'hv' || $parameters['source'] == 'la' || $parameters['source'] == 'apall' || $parameters['source'] == 'apl180' || $parameters['source'] == 'ap') {
                if ($scorePer < 40) {
                    $level = 0;
                    $levelZero++;
                } elseif ($scorePer >= 40 && $scorePer < 60) {
                    $level = 1;
                    $levelOne++;
                } elseif ($scorePer >= 60 && $scorePer < 80) {
                    $level = 2;
                    $levelTwo++;
                } elseif ($scorePer >= 80 && $scorePer < 90) {
                    $level = 3;
                    $levelThree++;
                } elseif ($scorePer >= 90) {
                    $level = 4;
                    $levelFour++;
                }
            }

            if ($parameters['source'] == 'hv') {
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = ucwords($aRow['facilityname']);
                $row[] = (isset($aRow['testingpointname']) && $aRow['testingpointname'] != "" ? $aRow['testingpointname'] : $aRow['testingpointtype']);
                $row[] = (isset($aRow['client_tested_HIV_PM']) ? $aRow['client_tested_HIV_PM'] : 0);
                $row[] = (isset($aRow['NumberofTester']) ? $aRow['NumberofTester'] : 0);
                $row[] = $level; //level
            } elseif ($parameters['source'] == 'la') {
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = ucwords($aRow['facilityname']);
                $row[] = (isset($aRow['testingpointname']) && $aRow['testingpointname'] != "" ? $aRow['testingpointname'] : $aRow['testingpointtype']);
                $row[] = round($aRow['AUDIT_SCORE_PERCENTAGE'], 2);
                $row[] = (isset($aRow['client_tested_HIV_PM']) ? $aRow['client_tested_HIV_PM'] : 0);
            } elseif ($parameters['source'] == 'ad') {
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = $aRow['totalDataPoints'];
            } elseif ($parameters['source'] == 'apall' || $parameters['source'] == 'apl180' || $parameters['source'] == 'ap') {
                $row[] = $aRow['facilityid'];
                $row[] = ucwords($aRow['facilityname']);
                $row[] = $aRow['auditroundno'];
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = ucwords($aRow['testingpointtype']);
                $row[] = ($aRow['testingpointtype'] == 'other') ? ucwords($aRow['testingpointtype_other']) : ucwords($aRow['testingpointname']);
                $row[] = ucwords($aRow['level']);
                $row[] = ucwords($aRow['affiliation']);
                $row[] = $level; //level
                $row[] = round($aRow['AUDIT_SCORE_PERCENTAGE'], 2);
            } elseif ($parameters['source'] == 'apspi') {
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = round($aRow['PERSONAL_SCORE'], 2);
                $row[] = round($aRow['PHYSICAL_SCORE'], 2);
                $row[] = round($aRow['SAFETY_SCORE'], 2);
                $row[] = round($aRow['PRETEST_SCORE'], 2);
                $row[] = round($aRow['TEST_SCORE'], 2);
                $row[] = round($aRow['POST_SCORE'], 2);
                $row[] = round($aRow['EQA_SCORE'], 2);
            }
            // Add more data to the row as needed
            $output['aaData'][] = $row;
        }

        $output['avgAuditScore'] = (count($rResult) > 0) ? round($auditScore / count($rResult), 2) : 0;
        $output['levelZeroCount'] = $levelZero;
        $output['levelOneCount'] = $levelOne;
        $output['levelTwoCount'] = $levelTwo;
        $output['levelThreeCount'] = $levelThree;
        $output['levelFourCount'] = $levelFour;

        return $output;
    }

    public function fetchViewDataS0Details($parameters)
    {
        $loginContainer = new Container('credo');
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */
        if ($parameters['source'] == 'hv') {
            $aColumns = array("DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", 'facilityname', 'NumberofTester');
            $orderColumns = array('assesmentofaudit', 'facilityname', 'NumberofTester', 'AUDIT_SCORE_PERCENTAGE');
        } elseif ($parameters['source'] == 'la') {
            $aColumns = array("DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", 'facilityname', 'AUDIT_SCORE_PERCENTAGE');
            $orderColumns = array('assesmentofaudit', 'facilityname', 'AUDIT_SCORE_PERCENTAGE');
        } elseif ($parameters['source'] == 'ad') {
            $aColumns = array("DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", "DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')");
            $orderColumns = array('assesmentofaudit', 'assesmentofaudit');
        } elseif ($parameters['source'] == 'apall' || $parameters['source'] == 'apl180' || $parameters['source'] == 'ap') {
            $aColumns = array('facilityid', 'facilityname', 'AUDIT_SCORE_PERCENTAGE', "DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", 'testingpointtype', 'testingpointtype_other', 'level', 'affiliation', 'AUDIT_SCORE_PERCENTAGE', 'AUDIT_SCORE_PERCENTAGE');
            $orderColumns = array('facilityid', 'facilityname', 'AUDIT_SCORE_PERCENTAGE', 'assesmentofaudit', 'testingpointtype', 'level', 'affiliation', 'AUDIT_SCORE_PERCENTAGE', 'AUDIT_SCORE_PERCENTAGE');
        } elseif ($parameters['source'] == 'apspi') {
            $aColumns = array("DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", 'PERSONAL_SCORE', 'PHYSICAL_SCORE', 'SAFETY_SCORE', 'PRETEST_SCORE', 'TEST_SCORE', 'POST_SCORE', 'EQA_SCORE');
            $orderColumns = array('assesmentofaudit', 'S0_Q_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY', 'S0_Q_2_COUNSELORS_FOLLOWING_PROTOCOL', 'S0_Q_3_TESTS_RECORDED_RECENCY', 'S0_Q_4_PROCESS_DOCUMENTED', 'S0_Q_5_RESULTS_RETURNED_IN_TWO_WEEKS', 'S0_Q_6_PROTOCOL_VIOLATION_DOCUMENTED', 'S0_Q_7_DOCUMENTING_PROTOCOL_ERRORS');
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
            for ($i = 0; $i < (int) $parameters['iSortingCols']; $i++) {
                if ($parameters['bSortable_' . (int) $parameters['iSortCol_' . $i]] == "true") {
                    $sOrder .= $orderColumns[(int) $parameters['iSortCol_' . $i]] . " " . ($parameters['sSortDir_' . $i]) . ",";
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
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' OR ";
                    } else {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' ";
                    }
                }
                $sWhereSub .= ")";
            }
            $sWhere .= $sWhereSub;
        }
        /* Individual column filtering */
        $counter = count($aColumns);

        /* Individual column filtering */
        for ($i = 0; $i < $counter; $i++) {
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
        $sql = new Sql($this->adapter);
        $startDate = "";
        $endDate = "";
        if (isset($parameters['drange']) && ($parameters['drange'] != "")) {
            $dateField = explode(" ", $parameters['drange']);
            if (isset($dateField[0]) && trim($dateField[0]) != "") {
                $startDate = CommonService::isoDateFormat($dateField[0]);
            }
            if (isset($dateField[2]) && trim($dateField[2]) != "") {
                $endDate = CommonService::isoDateFormat($dateField[2]);
            }
        }

        if ($parameters['source'] == 'ad') {
            //For Audit Dates
            $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array(new Expression('DISTINCT(assesmentofaudit) as assesmentofaudit'), 'totalDataPoints' => new Expression("COUNT(*)")))
                ->where(array('spiv3.status' => 'approved'))
                ->group('spiv3.assesmentofaudit');
            $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array(new Expression('DISTINCT(assesmentofaudit) as assesmentofaudit'), 'totalDataPoints' => new Expression("COUNT(*)")))
                ->where(array('spiv3.status' => 'approved'))
                ->group('spiv3.assesmentofaudit');
        } elseif ($parameters['source'] == 'apall' || $parameters['source'] == 'apl180' || $parameters['source'] == 'ap') {
            if (isset($parameters['date']) && ($parameters['date'] != "")) {
                $dateField = explode(" ", $parameters['date']);
                //print_r($proceed_date);die;
                if (isset($dateField[0]) && trim($dateField[0]) != "") {
                    $startDate = CommonService::isoDateFormat($dateField[0]);
                }
                if (isset($dateField[2]) && trim($dateField[2]) != "") {
                    $endDate = CommonService::isoDateFormat($dateField[2]);
                }
            }
            //For Audit Performance Row
            $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array('facilityid', 'facilityname', 'auditroundno', 'assesmentofaudit', 'testingpointtype', 'testingpointtype_other', 'level', 'affiliation', 'AUDIT_SCORE_PERCENTAGE'))
                ->where(array('spiv3.status' => 'approved'));
            $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array('facilityid', 'facilityname', 'auditroundno', 'assesmentofaudit', 'testingpointtype', 'testingpointtype_other', 'level', 'affiliation', 'AUDIT_SCORE_PERCENTAGE'))
                ->where(array('spiv3.status' => 'approved'));
            if ($parameters['source'] == 'apl180') {
                $sQuery = $sQuery->where("(`assesmentofaudit` BETWEEN CURDATE() - INTERVAL 180 DAY AND CURDATE())");
                $tQuery = $tQuery->where("(`assesmentofaudit` BETWEEN CURDATE() - INTERVAL 180 DAY AND CURDATE())");
            }
            if (isset($parameters['testPoint']) && trim($parameters['testPoint']) != '') {
                $sQuery = $sQuery->where("spiv3.testingpointtype='" . $parameters['testPoint'] . "'");
                $tQuery = $tQuery->where("spiv3.testingpointtype='" . $parameters['testPoint'] . "'");
                if (isset($parameters['testPointName']) && trim($parameters['testPointName']) != '') {
                    if (trim($parameters['testPoint']) != 'other') {
                        $sQuery = $sQuery->where("spiv3.testingpointname='" . $parameters['testPointName'] . "'");
                        $tQuery = $tQuery->where("spiv3.testingpointname='" . $parameters['testPointName'] . "'");
                    } else {
                        $sQuery = $sQuery->where("spiv3.testingpointtype_other='" . $parameters['testPointName'] . "'");
                        $tQuery = $tQuery->where("spiv3.testingpointtype_other='" . $parameters['testPointName'] . "'");
                    }
                }
            }
            if (isset($parameters['auditRndNo']) && $parameters['auditRndNo'] != '') {
                $sQuery = $sQuery->where("spiv3.auditroundno='" . $parameters['auditRndNo'] . "'");
                $tQuery = $tQuery->where("spiv3.auditroundno='" . $parameters['auditRndNo'] . "'");
            }
            if (isset($parameters['scoreLevel']) && $parameters['scoreLevel'] != '') {
                if ($parameters['scoreLevel'] == 0) {
                    $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE < 40");
                    $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE < 40");
                } elseif ($parameters['scoreLevel'] == 1) {
                    $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 59");
                    $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 59");
                } elseif ($parameters['scoreLevel'] == 2) {
                    $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 79");
                    $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 79");
                } elseif ($parameters['scoreLevel'] == 3) {
                    $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 89");
                    $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 89");
                } elseif ($parameters['scoreLevel'] == 4) {
                    $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 90");
                    $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 90");
                }
            }
            if (isset($parameters['level']) && $parameters['level'] != '') {
                $sQuery = $sQuery->where("spiv3.level='" . $parameters['level'] . "'");
                $tQuery = $tQuery->where("spiv3.level='" . $parameters['level'] . "'");
            }
            if (isset($parameters['province']) && $parameters['province'] != '') {
                $provinces = explode(",", $parameters['province']);
                $sQuery = $sQuery->where('spiv3.level_name IN ("' . implode('", "', $provinces) . '")');
                $tQuery = $tQuery->where('spiv3.level_name IN ("' . implode('", "', $provinces) . '")');
            }
            if (isset($parameters['affiliation']) && $parameters['affiliation'] != '') {
                $sQuery = $sQuery->where("spiv3.affiliation='" . $parameters['affiliation'] . "'");
                $tQuery = $tQuery->where("spiv3.affiliation='" . $parameters['affiliation'] . "'");
            }
        } elseif ($parameters['source'] == 'apspi') {
            //For Audit Performance
            $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array('assesmentofaudit', 'S0_Q_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY' => new Expression('AVG(S0_Q_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY)'), 'S0_Q_2_COUNSELORS_FOLLOWING_PROTOCOL' => new Expression('AVG(S0_Q_2_COUNSELORS_FOLLOWING_PROTOCOL)'), 'S0_Q_3_TESTS_RECORDED_RECENCY' => new Expression('AVG(S0_Q_3_TESTS_RECORDED_RECENCY)'), 'S0_Q_4_PROCESS_DOCUMENTED' => new Expression('AVG(S0_Q_4_PROCESS_DOCUMENTED)'), 'S0_Q_5_RESULTS_RETURNED_IN_TWO_WEEKS' => new Expression('AVG(S0_Q_5_RESULTS_RETURNED_IN_TWO_WEEKS)'), 'S0_Q_6_PROTOCOL_VIOLATION_DOCUMENTED' => new Expression('AVG(S0_Q_6_PROTOCOL_VIOLATION_DOCUMENTED)'), 'S0_Q_7_DOCUMENTING_PROTOCOL_ERRORS' => new Expression('AVG(S0_Q_7_DOCUMENTING_PROTOCOL_ERRORS)')))
                ->group('spiv3.assesmentofaudit');
            $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array('assesmentofaudit', 'S0_Q_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY' => new Expression('AVG(S0_Q_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY)'), 'S0_Q_2_COUNSELORS_FOLLOWING_PROTOCOL' => new Expression('AVG(S0_Q_2_COUNSELORS_FOLLOWING_PROTOCOL)'), 'S0_Q_3_TESTS_RECORDED_RECENCY' => new Expression('AVG(S0_Q_3_TESTS_RECORDED_RECENCY)'), 'S0_Q_4_PROCESS_DOCUMENTED' => new Expression('AVG(S0_Q_4_PROCESS_DOCUMENTED)'), 'S0_Q_5_RESULTS_RETURNED_IN_TWO_WEEKS' => new Expression('AVG(S0_Q_5_RESULTS_RETURNED_IN_TWO_WEEKS)'), 'S0_Q_6_PROTOCOL_VIOLATION_DOCUMENTED' => new Expression('AVG(S0_Q_6_PROTOCOL_VIOLATION_DOCUMENTED)'), 'S0_Q_7_DOCUMENTING_PROTOCOL_ERRORS' => new Expression('AVG(S0_Q_7_DOCUMENTING_PROTOCOL_ERRORS)')))
                ->group('spiv3.assesmentofaudit');
            if (isset($parameters['roundno']) && $parameters['roundno'] != '') {
                $xplodRoundNo = explode(",", $parameters['roundno']);
                $sQuery = $sQuery->where('spiv3.auditroundno IN ("' . implode('", "', $xplodRoundNo) . '")');
                $tQuery = $tQuery->where('spiv3.auditroundno IN ("' . implode('", "', $xplodRoundNo) . '")');
            }
        } else {
            //For Others
            $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array('assesmentofaudit', 'facilityname', 'testingpointtype', 'NumberofTester', 'AUDIT_SCORE_PERCENTAGE'));

            $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array('assesmentofaudit', 'facilityname', 'testingpointtype', 'NumberofTester', 'AUDIT_SCORE_PERCENTAGE'));
        }

        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where('spiv3.token IN ("' . implode('", "', $loginContainer->token) . '")');
            $tQuery = $tQuery->where('spiv3.token IN ("' . implode('", "', $loginContainer->token) . '")');
        }

        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType != '') {
            // Mapping exists
            if ($loginContainer->userMappingType === 'country' && $parameters['country'] == '') {
                // User mapped by country: show mapped country data and all related provinces and districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('country', 'province', 'district'))
                    ->where('f.country IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
                $tQuery = $tQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('country', 'province', 'district'))
                    ->where('f.country IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'province' && $parameters['province'] == '') {
                // User mapped by province: show only mapped provinces
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('province'))
                    ->where('f.province IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
                $tQuery = $tQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('province'))
                    ->where('f.province IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'district' && $parameters['district'] == '') {
                // User mapped by district: show only mapped districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('district'))
                    ->where('f.district IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
                $tQuery = $tQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('district'))
                    ->where('f.district IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            }
        }
        if ((isset($parameters['country']) && $parameters['country'] != '') || (isset($parameters['province']) && $parameters['province'] != '') || (isset($parameters['district']) && $parameters['district'] != '')) {
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('country', 'province', 'district'));
            $tQuery = $tQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('country', 'province', 'district'));

            if (isset($parameters['country']) && $parameters['country'] != '' && is_array($parameters['country'])) {
                $sQuery = $sQuery->where('f.country IN ("' . implode('", "', $parameters['country']) . '")');
                $tQuery = $tQuery->where('f.country IN ("' . implode('", "', $parameters['country']) . '")');
            }
            if (isset($parameters['province']) && $parameters['province'] != '' && is_array($parameters['province'])) {
                $sQuery = $sQuery->where('f.province IN ("' . implode('", "', $parameters['province']) . '")');
                $tQuery = $tQuery->where('f.province IN ("' . implode('", "', $parameters['province']) . '")');
            }
            if (isset($parameters['district']) && $parameters['district'] != '' && is_array($parameters['district'])) {
                $sQuery = $sQuery->where('f.district IN ("' . implode('", "', $parameters['district']) . '")');
                $tQuery = $tQuery->where('f.district IN ("' . implode('", "', $parameters['district']) . '")');
            }
        }

        if (trim($startDate) != "" && trim($endDate) != "") {
            $sQuery = $sQuery->where(array("spiv3.assesmentofaudit >='" . $startDate . "'", "spiv3.assesmentofaudit <='" . $endDate . "'"));
            $tQuery = $tQuery->where(array("spiv3.assesmentofaudit >='" . $startDate . "'", "spiv3.assesmentofaudit <='" . $endDate . "'"));
        }

        if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }

        if (trim($sOrder) === 'desc') {
            $sOrder = '';
        }
        if (isset($sOrder) && $sOrder != "") {
            $sQuery->order($sOrder);
        }

        if (isset($sLimit) && isset($sOffset)) {
            $sQuery->limit($sLimit);
            $sQuery->offset($sOffset);
        }

        $sQueryStr = $sql->buildSqlString($sQuery); // Get the string of the Sql, instead of the Select-instance
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);
        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->buildSqlString($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */

        $tQueryStr = $sql->buildSqlString($tQuery); // Get the string of the Sql, instead of the Select-instance
        $tResult = $dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);
        $iTotal = count($tResult);
        $output = array(
            "sEcho" => (int) $parameters['sEcho'],
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array(),
        );

        //$personalScoreArray = [];
        //$physicalScoreArray = [];
        //$safetyScoreArray = [];
        //$preTestScoreArray = [];
        //$testScoreArray = [];
        //$postTestScoreArray = [];
        //$eqaScoreArray = [];
        //$personalScore = 0;
        //$physicalScore = 0;
        //$safetyScore = 0;
        //$preTestScore = 0;
        //$testScore = 0;
        //$postTestScore = 0;
        //$eqaScore = 0;
        $auditScore = 0;
        $levelZero = [];
        $levelOne = [];
        $levelTwo = [];
        $levelThree = [];
        $levelFour = [];
        foreach ($rResult as $aRow) {
            $row = [];
            if ($parameters['source'] == 'hv' || $parameters['source'] == 'la' || $parameters['source'] == 'apall' || $parameters['source'] == 'apl180' || $parameters['source'] == 'ap') {
                $auditScore += $aRow['AUDIT_SCORE_PERCENTAGE'];
                if (isset($aRow['AUDIT_SCORE_PERCENTAGE']) && $aRow['AUDIT_SCORE_PERCENTAGE'] < 40) {
                    $level = 0;
                    $levelZero[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
                } elseif (isset($aRow['AUDIT_SCORE_PERCENTAGE']) && $aRow['AUDIT_SCORE_PERCENTAGE'] >= 40 && $aRow['AUDIT_SCORE_PERCENTAGE'] < 60) {
                    $level = 1;
                    $levelOne[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
                } elseif (isset($aRow['AUDIT_SCORE_PERCENTAGE']) && $aRow['AUDIT_SCORE_PERCENTAGE'] >= 60 && $aRow['AUDIT_SCORE_PERCENTAGE'] < 80) {
                    $level = 2;
                    $levelTwo[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
                } elseif (isset($aRow['AUDIT_SCORE_PERCENTAGE']) && $aRow['AUDIT_SCORE_PERCENTAGE'] >= 80 && $aRow['AUDIT_SCORE_PERCENTAGE'] < 90) {
                    $level = 3;
                    $levelThree[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
                } elseif (isset($aRow['AUDIT_SCORE_PERCENTAGE']) && $aRow['AUDIT_SCORE_PERCENTAGE'] >= 90) {
                    $level = 4;
                    $levelFour[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
                }
            }
            if ($parameters['source'] == 'hv') {
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = ucwords($aRow['facilityname']);
                $row[] = (isset($aRow['testingpointname']) && $aRow['testingpointname'] != "" ? $aRow['testingpointname'] : $aRow['testingpointtype']);
                $row[] = (isset($aRow['client_tested_HIV_PM']) ? $aRow['client_tested_HIV_PM'] : 0);
                $row[] = (isset($aRow['NumberofTester']) ? $aRow['NumberofTester'] : 0);
                $row[] = $level;
            } elseif ($parameters['source'] == 'la') {
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = ucwords($aRow['facilityname']);
                $row[] = (isset($aRow['testingpointname']) && $aRow['testingpointname'] != "" ? $aRow['testingpointname'] : $aRow['testingpointtype']);
                $row[] = round($aRow['AUDIT_SCORE_PERCENTAGE'], 2);
                $row[] = (isset($aRow['client_tested_HIV_PM']) ? $aRow['client_tested_HIV_PM'] : 0);
            } elseif ($parameters['source'] == 'ad') {
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = $aRow['totalDataPoints'];
            } elseif ($parameters['source'] == 'apall' || $parameters['source'] == 'apl180' || $parameters['source'] == 'ap') {
                $row[] = $aRow['facilityid'];
                $row[] = ucwords($aRow['facilityname']);
                $row[] = $aRow['auditroundno'];
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = ucwords($aRow['testingpointtype']);
                $row[] = ($aRow['testingpointtype'] == 'other') ? ucwords($aRow['testingpointtype_other']) : ucwords($aRow['testingpointname']);
                $row[] = ucwords($aRow['level']);
                $row[] = ucwords($aRow['affiliation']);
                $row[] = $level;
                $row[] = round($aRow['AUDIT_SCORE_PERCENTAGE'], 2);
            } elseif ($parameters['source'] == 'apspi') {
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = $aRow['S0_Q_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY'];
                $row[] = $aRow['S0_Q_2_COUNSELORS_FOLLOWING_PROTOCOL'];
                $row[] = $aRow['S0_Q_3_TESTS_RECORDED_RECENCY'];
                $row[] = $aRow['S0_Q_4_PROCESS_DOCUMENTED'];
                $row[] = $aRow['S0_Q_5_RESULTS_RETURNED_IN_TWO_WEEKS'];
                $row[] = $aRow['S0_Q_6_PROTOCOL_VIOLATION_DOCUMENTED'];
                $row[] = $aRow['S0_Q_7_DOCUMENTING_PROTOCOL_ERRORS'];
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
        //  $output['avgAuditScore'] = (count($rResult) > 0) ? round($auditScore/count($rResult),2) : 0;
        //  $output['levelZeroCount'] = count($levelZero);
        //  $output['levelOneCount'] = count($levelOne);
        //  $output['levelTwoCount'] = count($levelTwo);
        //  $output['levelThreeCount'] = count($levelThree);
        //  $output['levelFourCount'] = count($levelFour);
        return $output;
    }

    public function fetchViewDataD0Details($parameters)
    {
        $loginContainer = new Container('credo');
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */
        if ($parameters['source'] == 'hv') {
            $aColumns = array("DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", 'facilityname', 'NumberofTester');
            $orderColumns = array('assesmentofaudit', 'facilityname', 'NumberofTester', 'AUDIT_SCORE_PERCENTAGE');
        } elseif ($parameters['source'] == 'la') {
            $aColumns = array("DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", 'facilityname', 'AUDIT_SCORE_PERCENTAGE');
            $orderColumns = array('assesmentofaudit', 'facilityname', 'AUDIT_SCORE_PERCENTAGE');
        } elseif ($parameters['source'] == 'ad') {
            $aColumns = array("DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", "DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')");
            $orderColumns = array('assesmentofaudit', 'assesmentofaudit');
        } elseif ($parameters['source'] == 'apall' || $parameters['source'] == 'apl180' || $parameters['source'] == 'ap') {
            $aColumns = array('facilityid', 'facilityname', 'AUDIT_SCORE_PERCENTAGE', "DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", 'testingpointtype', 'testingpointtype_other', 'level', 'affiliation', 'AUDIT_SCORE_PERCENTAGE', 'AUDIT_SCORE_PERCENTAGE');
            $orderColumns = array('facilityid', 'facilityname', 'AUDIT_SCORE_PERCENTAGE', 'assesmentofaudit', 'testingpointtype', 'level', 'affiliation', 'AUDIT_SCORE_PERCENTAGE', 'AUDIT_SCORE_PERCENTAGE');
        } elseif ($parameters['source'] == 'apspi') {
            $aColumns = array("DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", 'PERSONAL_SCORE', 'PHYSICAL_SCORE', 'SAFETY_SCORE', 'PRETEST_SCORE', 'TEST_SCORE', 'POST_SCORE', 'EQA_SCORE');
            $orderColumns = array('assesmentofaudit', 'D0_S_1_DIAGNOSED_HIV_ABOVE_15', 'D0_S_2_CANDIDATE_SCREENED_FOR_PARTICIPATION', 'D0_S_3_ELIGIBLE_DURING_REVIEW_PERIOD', 'D0_S_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD', 'D0_S_5_DOCUMENTED_AND_REFUSED', 'D0_S_6_PARTICIAPANTS_ENROLLED_IN_RTRI', 'D0_S_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI', 'D0_S_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI');
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
            for ($i = 0; $i < (int) $parameters['iSortingCols']; $i++) {
                if ($parameters['bSortable_' . (int) $parameters['iSortCol_' . $i]] == "true") {
                    $sOrder .= $orderColumns[(int) $parameters['iSortCol_' . $i]] . " " . ($parameters['sSortDir_' . $i]) . ",";
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
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' OR ";
                    } else {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' ";
                    }
                }
                $sWhereSub .= ")";
            }
            $sWhere .= $sWhereSub;
        }
        /* Individual column filtering */
        $counter = count($aColumns);

        /* Individual column filtering */
        for ($i = 0; $i < $counter; $i++) {
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
        $sql = new Sql($this->adapter);
        $startDate = "";
        $endDate = "";
        if (isset($parameters['drange']) && ($parameters['drange'] != "")) {
            $dateField = explode(" ", $parameters['drange']);
            if (isset($dateField[0]) && trim($dateField[0]) != "") {
                $startDate = CommonService::isoDateFormat($dateField[0]);
            }
            if (isset($dateField[2]) && trim($dateField[2]) != "") {
                $endDate = CommonService::isoDateFormat($dateField[2]);
            }
        }

        if ($parameters['source'] == 'ad') {
            //For Audit Dates
            $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array(new Expression('DISTINCT(assesmentofaudit) as assesmentofaudit'), 'totalDataPoints' => new Expression("COUNT(*)")))
                ->where(array('spiv3.status' => 'approved'))
                ->group('spiv3.assesmentofaudit');
            $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array(new Expression('DISTINCT(assesmentofaudit) as assesmentofaudit'), 'totalDataPoints' => new Expression("COUNT(*)")))
                ->where(array('spiv3.status' => 'approved'))
                ->group('spiv3.assesmentofaudit');
        } elseif ($parameters['source'] == 'apall' || $parameters['source'] == 'apl180' || $parameters['source'] == 'ap') {
            if (isset($parameters['date']) && ($parameters['date'] != "")) {
                $dateField = explode(" ", $parameters['date']);
                //print_r($proceed_date);die;
                if (isset($dateField[0]) && trim($dateField[0]) != "") {
                    $startDate = CommonService::isoDateFormat($dateField[0]);
                }
                if (isset($dateField[2]) && trim($dateField[2]) != "") {
                    $endDate = CommonService::isoDateFormat($dateField[2]);
                }
            }
            //For Audit Performance Row
            $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array('facilityid', 'facilityname', 'auditroundno', 'assesmentofaudit', 'testingpointtype', 'testingpointtype_other', 'level', 'affiliation', 'AUDIT_SCORE_PERCENTAGE'))
                ->where(array('spiv3.status' => 'approved'));
            $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array('facilityid', 'facilityname', 'auditroundno', 'assesmentofaudit', 'testingpointtype', 'testingpointtype_other', 'level', 'affiliation', 'AUDIT_SCORE_PERCENTAGE'))
                ->where(array('spiv3.status' => 'approved'));
            if ($parameters['source'] == 'apl180') {
                $sQuery = $sQuery->where("(`assesmentofaudit` BETWEEN CURDATE() - INTERVAL 180 DAY AND CURDATE())");
                $tQuery = $tQuery->where("(`assesmentofaudit` BETWEEN CURDATE() - INTERVAL 180 DAY AND CURDATE())");
            }
            if (isset($parameters['testPoint']) && trim($parameters['testPoint']) != '') {
                $sQuery = $sQuery->where("spiv3.testingpointtype='" . $parameters['testPoint'] . "'");
                $tQuery = $tQuery->where("spiv3.testingpointtype='" . $parameters['testPoint'] . "'");
                if (isset($parameters['testPointName']) && trim($parameters['testPointName']) != '') {
                    if (trim($parameters['testPoint']) != 'other') {
                        $sQuery = $sQuery->where("spiv3.testingpointname='" . $parameters['testPointName'] . "'");
                        $tQuery = $tQuery->where("spiv3.testingpointname='" . $parameters['testPointName'] . "'");
                    } else {
                        $sQuery = $sQuery->where("spiv3.testingpointtype_other='" . $parameters['testPointName'] . "'");
                        $tQuery = $tQuery->where("spiv3.testingpointtype_other='" . $parameters['testPointName'] . "'");
                    }
                }
            }
            if (isset($parameters['auditRndNo']) && $parameters['auditRndNo'] != '') {
                $sQuery = $sQuery->where("spiv3.auditroundno='" . $parameters['auditRndNo'] . "'");
                $tQuery = $tQuery->where("spiv3.auditroundno='" . $parameters['auditRndNo'] . "'");
            }
            if (isset($parameters['scoreLevel']) && $parameters['scoreLevel'] != '') {
                if ($parameters['scoreLevel'] == 0) {
                    $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE < 40");
                    $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE < 40");
                } elseif ($parameters['scoreLevel'] == 1) {
                    $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 59");
                    $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 59");
                } elseif ($parameters['scoreLevel'] == 2) {
                    $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 79");
                    $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 79");
                } elseif ($parameters['scoreLevel'] == 3) {
                    $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 89");
                    $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv3.AUDIT_SCORE_PERCENTAGE <= 89");
                } elseif ($parameters['scoreLevel'] == 4) {
                    $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 90");
                    $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCENTAGE >= 90");
                }
            }
            if (isset($parameters['level']) && $parameters['level'] != '') {
                $sQuery = $sQuery->where("spiv3.level='" . $parameters['level'] . "'");
                $tQuery = $tQuery->where("spiv3.level='" . $parameters['level'] . "'");
            }
            if (isset($parameters['province']) && $parameters['province'] != '') {
                $provinces = explode(",", $parameters['province']);
                $sQuery = $sQuery->where('spiv3.level_name IN ("' . implode('", "', $provinces) . '")');
                $tQuery = $tQuery->where('spiv3.level_name IN ("' . implode('", "', $provinces) . '")');
            }
            if (isset($parameters['affiliation']) && $parameters['affiliation'] != '') {
                $sQuery = $sQuery->where("spiv3.affiliation='" . $parameters['affiliation'] . "'");
                $tQuery = $tQuery->where("spiv3.affiliation='" . $parameters['affiliation'] . "'");
            }
        } elseif ($parameters['source'] == 'apspi') {
            //For Audit Performance
            $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array(
                    'assesmentofaudit',
                    'D0_S_1_DIAGNOSED_HIV_ABOVE_15' => new Expression('AVG(D0_S_1_DIAGNOSED_HIV_ABOVE_15)'),
                    'D0_S_2_CANDIDATE_SCREENED_FOR_PARTICIPATION' => new Expression('AVG(D0_S_2_CANDIDATE_SCREENED_FOR_PARTICIPATION)'),
                    'D0_S_3_ELIGIBLE_DURING_REVIEW_PERIOD' => new Expression('AVG(D0_S_3_ELIGIBLE_DURING_REVIEW_PERIOD)'),
                    'D0_S_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD' => new Expression('AVG(D0_S_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD)'),
                    'D0_S_5_DOCUMENTED_AND_REFUSED' => new Expression('AVG(D0_S_5_DOCUMENTED_AND_REFUSED)'),
                    'D0_S_6_PARTICIAPANTS_ENROLLED_IN_RTRI' => new Expression('AVG(D0_S_6_PARTICIAPANTS_ENROLLED_IN_RTRI)'),
                    'D0_S_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI' => new Expression('AVG(D0_S_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI)'),
                    'D0_S_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI' => new Expression('AVG(D0_S_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI)')
                ))
                ->group('spiv3.assesmentofaudit');
            $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array('assesmentofaudit', 'S0_Q_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY' => new Expression('AVG(S0_Q_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY)'), 'S0_Q_2_COUNSELORS_FOLLOWING_PROTOCOL' => new Expression('AVG(S0_Q_2_COUNSELORS_FOLLOWING_PROTOCOL)'), 'S0_Q_3_TESTS_RECORDED_RECENCY' => new Expression('AVG(S0_Q_3_TESTS_RECORDED_RECENCY)'), 'S0_Q_4_PROCESS_DOCUMENTED' => new Expression('AVG(S0_Q_4_PROCESS_DOCUMENTED)'), 'S0_Q_5_RESULTS_RETURNED_IN_TWO_WEEKS' => new Expression('AVG(S0_Q_5_RESULTS_RETURNED_IN_TWO_WEEKS)'), 'S0_Q_6_PROTOCOL_VIOLATION_DOCUMENTED' => new Expression('AVG(S0_Q_6_PROTOCOL_VIOLATION_DOCUMENTED)'), 'S0_Q_7_DOCUMENTING_PROTOCOL_ERRORS' => new Expression('AVG(S0_Q_7_DOCUMENTING_PROTOCOL_ERRORS)')))
                ->group('spiv3.assesmentofaudit');
            if (isset($parameters['roundno']) && $parameters['roundno'] != '') {
                $xplodRoundNo = explode(",", $parameters['roundno']);
                $sQuery = $sQuery->where('spiv3.auditroundno IN ("' . implode('", "', $xplodRoundNo) . '")');
                $tQuery = $tQuery->where('spiv3.auditroundno IN ("' . implode('", "', $xplodRoundNo) . '")');
            }
        } else {
            //For Others
            $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array('assesmentofaudit', 'facilityname', 'testingpointtype', 'NumberofTester', 'AUDIT_SCORE_PERCENTAGE'));

            $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array('assesmentofaudit', 'facilityname', 'testingpointtype', 'NumberofTester', 'AUDIT_SCORE_PERCENTAGE'));
        }

        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where('spiv3.token IN ("' . implode('", "', $loginContainer->token) . '")');
            $tQuery = $tQuery->where('spiv3.token IN ("' . implode('", "', $loginContainer->token) . '")');
        }

        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType != '') {
            // Mapping exists
            if ($loginContainer->userMappingType === 'country' && $parameters['country'] == '') {
                // User mapped by country: show mapped country data and all related provinces and districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('country', 'province', 'district'))
                    ->where('f.country IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
                $tQuery = $tQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('country', 'province', 'district'))
                    ->where('f.country IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'province' && $parameters['province'] == '') {
                // User mapped by province: show only mapped provinces
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('province'))
                    ->where('f.province IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
                $tQuery = $tQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('province'))
                    ->where('f.province IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            } elseif ($loginContainer->userMappingType === 'district' && $parameters['district'] == '') {
                // User mapped by district: show only mapped districts
                $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('district'))
                    ->where('f.district IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
                $tQuery = $tQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('district'))
                    ->where('f.district IN ("' . implode('", "', $loginContainer->userMappedIds) . '")');
            }
        }
        if ((isset($parameters['country']) && $parameters['country'] != '') || (isset($parameters['province']) && $parameters['province'] != '') || (isset($parameters['district']) && $parameters['district'] != '')) {
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('country', 'province', 'district'));
            $tQuery = $tQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('country', 'province', 'district'));

            if (isset($parameters['country']) && $parameters['country'] != '' && is_array($parameters['country'])) {
                $sQuery = $sQuery->where('f.country IN ("' . implode('", "', $parameters['country']) . '")');
                $tQuery = $tQuery->where('f.country IN ("' . implode('", "', $parameters['country']) . '")');
            }
            if (isset($parameters['province']) && $parameters['province'] != '' && is_array($parameters['province'])) {
                $sQuery = $sQuery->where('f.province IN ("' . implode('", "', $parameters['province']) . '")');
                $tQuery = $tQuery->where('f.province IN ("' . implode('", "', $parameters['province']) . '")');
            }
            if (isset($parameters['district']) && $parameters['district'] != '' && is_array($parameters['district'])) {
                $sQuery = $sQuery->where('f.district IN ("' . implode('", "', $parameters['district']) . '")');
                $tQuery = $tQuery->where('f.district IN ("' . implode('", "', $parameters['district']) . '")');
            }
        }

        if (trim($startDate) != "" && trim($endDate) != "") {
            $sQuery = $sQuery->where(array("spiv3.assesmentofaudit >='" . $startDate . "'", "spiv3.assesmentofaudit <='" . $endDate . "'"));
            $tQuery = $tQuery->where(array("spiv3.assesmentofaudit >='" . $startDate . "'", "spiv3.assesmentofaudit <='" . $endDate . "'"));
        }

        if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }

        if (trim($sOrder) === 'desc') {
            $sOrder = '';
        }
        if (isset($sOrder) && $sOrder != "") {
            $sQuery->order($sOrder);
        }

        if (isset($sLimit) && isset($sOffset)) {
            $sQuery->limit($sLimit);
            $sQuery->offset($sOffset);
        }

        $sQueryStr = $sql->buildSqlString($sQuery); // Get the string of the Sql, instead of the Select-instance
        // echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);
        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->buildSqlString($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */

        $tQueryStr = $sql->buildSqlString($tQuery); // Get the string of the Sql, instead of the Select-instance
        $tResult = $dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);
        $iTotal = count($tResult);
        $output = array(
            "sEcho" => (int) $parameters['sEcho'],
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array(),
        );

        //$personalScoreArray = [];
        //$physicalScoreArray = [];
        //$safetyScoreArray = [];
        //$preTestScoreArray = [];
        //$testScoreArray = [];
        //$postTestScoreArray = [];
        //$eqaScoreArray = [];
        //$personalScore = 0;
        //$physicalScore = 0;
        //$safetyScore = 0;
        //$preTestScore = 0;
        //$testScore = 0;
        //$postTestScore = 0;
        //$eqaScore = 0;
        $auditScore = 0;
        $levelZero = [];
        $levelOne = [];
        $levelTwo = [];
        $levelThree = [];
        $levelFour = [];
        foreach ($rResult as $aRow) {
            $row = [];
            if ($parameters['source'] == 'hv' || $parameters['source'] == 'la' || $parameters['source'] == 'apall' || $parameters['source'] == 'apl180' || $parameters['source'] == 'ap') {
                $auditScore += $aRow['AUDIT_SCORE_PERCENTAGE'];
                if (isset($aRow['AUDIT_SCORE_PERCENTAGE']) && $aRow['AUDIT_SCORE_PERCENTAGE'] < 40) {
                    $level = 0;
                    $levelZero[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
                } elseif (isset($aRow['AUDIT_SCORE_PERCENTAGE']) && $aRow['AUDIT_SCORE_PERCENTAGE'] >= 40 && $aRow['AUDIT_SCORE_PERCENTAGE'] < 60) {
                    $level = 1;
                    $levelOne[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
                } elseif (isset($aRow['AUDIT_SCORE_PERCENTAGE']) && $aRow['AUDIT_SCORE_PERCENTAGE'] >= 60 && $aRow['AUDIT_SCORE_PERCENTAGE'] < 80) {
                    $level = 2;
                    $levelTwo[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
                } elseif (isset($aRow['AUDIT_SCORE_PERCENTAGE']) && $aRow['AUDIT_SCORE_PERCENTAGE'] >= 80 && $aRow['AUDIT_SCORE_PERCENTAGE'] < 90) {
                    $level = 3;
                    $levelThree[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
                } elseif (isset($aRow['AUDIT_SCORE_PERCENTAGE']) && $aRow['AUDIT_SCORE_PERCENTAGE'] >= 90) {
                    $level = 4;
                    $levelFour[] = $aRow['AUDIT_SCORE_PERCENTAGE'];
                }
            }
            if ($parameters['source'] == 'hv') {
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = ucwords($aRow['facilityname']);
                $row[] = (isset($aRow['testingpointname']) && $aRow['testingpointname'] != "" ? $aRow['testingpointname'] : $aRow['testingpointtype']);
                $row[] = (isset($aRow['client_tested_HIV_PM']) ? $aRow['client_tested_HIV_PM'] : 0);
                $row[] = (isset($aRow['NumberofTester']) ? $aRow['NumberofTester'] : 0);
                $row[] = $level;
            } elseif ($parameters['source'] == 'la') {
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = ucwords($aRow['facilityname']);
                $row[] = (isset($aRow['testingpointname']) && $aRow['testingpointname'] != "" ? $aRow['testingpointname'] : $aRow['testingpointtype']);
                $row[] = round($aRow['AUDIT_SCORE_PERCENTAGE'], 2);
                $row[] = (isset($aRow['client_tested_HIV_PM']) ? $aRow['client_tested_HIV_PM'] : 0);
            } elseif ($parameters['source'] == 'ad') {
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = $aRow['totalDataPoints'];
            } elseif ($parameters['source'] == 'apall' || $parameters['source'] == 'apl180' || $parameters['source'] == 'ap') {
                $row[] = $aRow['facilityid'];
                $row[] = ucwords($aRow['facilityname']);
                $row[] = $aRow['auditroundno'];
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = ucwords($aRow['testingpointtype']);
                $row[] = ($aRow['testingpointtype'] == 'other') ? ucwords($aRow['testingpointtype_other']) : ucwords($aRow['testingpointname']);
                $row[] = ucwords($aRow['level']);
                $row[] = ucwords($aRow['affiliation']);
                $row[] = $level;
                $row[] = round($aRow['AUDIT_SCORE_PERCENTAGE'], 2);
            } elseif ($parameters['source'] == 'apspi') {
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = $aRow['D0_S_1_DIAGNOSED_HIV_ABOVE_15'];
                $row[] = $aRow['D0_S_2_CANDIDATE_SCREENED_FOR_PARTICIPATION'];
                $row[] = $aRow['D0_S_3_ELIGIBLE_DURING_REVIEW_PERIOD'];
                $row[] = $aRow['D0_S_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD'];
                $row[] = $aRow['D0_S_5_DOCUMENTED_AND_REFUSED'];
                $row[] = $aRow['D0_S_6_PARTICIAPANTS_ENROLLED_IN_RTRI'];
                $row[] = $aRow['D0_S_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI'];
                $row[] = $aRow['D0_S_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI'];
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
        //  $output['avgAuditScore'] = (count($rResult) > 0) ? round($auditScore/count($rResult),2) : 0;
        //  $output['levelZeroCount'] = count($levelZero);
        //  $output['levelOneCount'] = count($levelOne);
        //  $output['levelTwoCount'] = count($levelTwo);
        //  $output['levelThreeCount'] = count($levelThree);
        //  $output['levelFourCount'] = count($levelFour);
        return $output;
    }

    public function fetchAllTestingPointTypeV6()
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $query = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
            ->columns(array(new Expression('DISTINCT(testingpointtype) as testingPointType')))
            ->group('testingpointtype')
            ->order("testingpointtype ASC");
        $queryStr = $sql->buildSqlString($query);

        return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    }

    public function fetchTestingPointTypeNamesByTypeV6($params)
    {
        $typeResult = [];
        if (isset($params['testingPointType']) && trim($params['testingPointType']) != '') {
            if ($params['testingPointType'] == 'other') {
                $column = 'DISTINCT(testingpointtype_other) as testingpointName';
            } else {
                $column = 'DISTINCT(testingpointtype) as testingpointName';
            }
            $dbAdapter = $this->adapter;
            $sql = new Sql($this->adapter);
            $query = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
                ->columns(array(new Expression($column)));
            if (isset($params['testingPointType']) && trim($params['testingPointType']) != '' && $params['testingPointType'] == 'other') {
                $query = $query->where('testingpointtype_other IS NOT NULL');
            } else {
                $query = $query->where(array('testingpointtype' => $params['testingPointType']));
            }
            $queryStr = $sql->buildSqlString($query);
            //print_r($queryStr);
            $typeResult = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        }
        return $typeResult;
    }

    public function fetchSpiV6FormUniqueLevelNames()
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $query = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))->columns(array('facilityname'))
            ->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv6.facility');
        //    ->group("province")
        //    ->order("province ASC");
        $queryStr = $sql->buildSqlString($query);
        //$query = $sql->select()->from(array('spiv6' => 'spi_form_v_3'))
        //                       ->columns(array(new Expression('DISTINCT(level_name) as level_name')))
        //                       ->order("level_name ASC");
        //$queryStr = $sql->buildSqlString($query);
        return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    }
    // public function fetchDistrictData($params)
    // {
    //     $dbAdapter = $this->adapter;
    //     $sql = new Sql($this->adapter);
    //     $query = $sql->select()->from(array('f' => 'spi_rt_3_facilities'))
    //         ->group('district');
    //     if (is_array($params['province'])) {
    //         $query = $query->where('f.province IN ("' . implode('", "', $params['province']) . '") AND f.district!=""');
    //     } else {
    //         $query = $query->where('f.province="' . $params['province'] . '" AND f.district!=""');
    //     }
    //     $queryStr = $sql->buildSqlString($query);
    //     return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    // }

    public function fetchDistrictData($params)
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $query = $sql->select()
            ->quantifier(\Laminas\Db\Sql\Select::QUANTIFIER_DISTINCT)
            ->from(array('f' => 'spi_rt_3_facilities'))
            ->columns(array('district'));
        if (!empty($params['province']) && is_array($params['province'])) {
            $query = $query->where('f.province IN ("' . implode('", "', $params['province']) . '") AND f.district!=""');
        } elseif (!empty($params['province']) && !is_array($params['province'])) {
            $query = $query->where('f.province="' . $params['province'] . '" AND f.district!=""');
        } else {
            $query = $query->where('f.district!=""');
        }
        $queryStr = $sql->buildSqlString($query);
        return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    }

    public function addValidateSpiv3Data($params)
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $vId = explode(",", $params['validateId']);
        $newQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3_temp'))
            ->where('spiv3.id IN ("' . implode('", "', $vId) . '")');
        $newQueryStr = $sql->buildSqlString($newQuery); // Get the string of the Sql, instead of the Select-instance
        $totalResult = $dbAdapter->query($newQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        if ($totalResult) {
            foreach ($totalResult as $newResult) {
                $insert = $sql->insert('spi_form_v_3');
                if (isset($newResult['testingpointname']) && trim($newResult['testingpointname']) == "") {
                    $newResult['testingpointname'] = $newResult['testingpointtype'];
                }
                $newResult['instanceID'] = isset($newResult['instanceID']) ? $newResult['instanceID'] : "";
                $newResult['instanceName'] = isset($newResult['instanceName']) ? $newResult['instanceName'] : "";

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
                    'avgMonthTesting' => (isset($newResult['client_tested_HIV_PM']) && $newResult['client_tested_HIV_PM'] > 0 ? $newResult['client_tested_HIV_PM'] : 0),
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
                    'SUMMARY_NOT_AVL' => $newResult['SUMMARY_NOT_AVL'] ?? null,
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
                    'status' => $newResult['status'],
                );
                $dbAdapter = $this->adapter;
                $insert->values($par);
                $selectString = $sql->buildSqlString($insert);
                /** @var \Laminas\Db\Adapter\Driver\ResultInterface $results */
                $results = $dbAdapter->query($selectString, $dbAdapter::QUERY_MODE_EXECUTE);
                if ($results->getGeneratedValue() > 0) {
                    $spiv3Temp = new \Application\Model\SpiFormVer3TempTable($this->adapter);
                    $spiv3Temp->delete(array('id' => $newResult['id']));
                }
            }
        }
    }

    public function updateSpiv5FacilityInfo($id, $params)
    {
        if ($id > 0) {
            $data = array(
                'facilityid' => $params['facilityId'],
                'facilityname' => $params['facilityName'],
            );
            $this->update($data, array('facility' => $id));
        }
        return $id;
    }

    public function getLatestFormDate($projectId, $formId)
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $query = "SELECT MAX(`meta-submission-date`) as last_added_form_date FROM spi_form_v_6 WHERE central_project_id = ? AND central_form_id = ?";
        return $dbAdapter->query($query, [$projectId, $formId])->toArray();
    }

    public function fetchV6DetailsByMetaInstanceId($metaInstantId)
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from('spi_form_v_6')
            ->where("`meta-instance-id` = '$metaInstantId'");
        $sQueryStr = $sql->buildSqlString($sQuery); // Get the string of the Sql, instead of the Select-instance
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
        return $rResult;
    }

    public function updateSpiv6FacilityInfo($id, $params)
    {
        if ($id > 0) {
            $data = array(
                'facilityid' => $params['facilityId'],
                'facilityname' => $params['facilityName'],
            );
            $this->update($data, array('facility' => $id));
        }
        return $id;
    }

    public function fetchSpiV6FormUniqueLevels()
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $query = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))->columns(array('level' => new Expression("DISTINCT level")))->where("level!=''")
            ->order("level ASC");
        $queryStr = $sql->buildSqlString($query);
        return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    }
}

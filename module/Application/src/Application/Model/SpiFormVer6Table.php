<?php

namespace Application\Model;

use App\Db\Dbal;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Predicate\In;
use Laminas\Db\Sql\Predicate\Literal;
use Laminas\Db\Sql\Predicate\PredicateSet;
use Application\Session\Container;
use Application\I18n\Translator;
use Laminas\Db\Adapter\Adapter;
use Application\Model\GlobalTable;
use Application\Model\EventLogTable;
use Application\Service\CommonService;
use Application\Model\SpiRtFacilitiesTable;



/**
 * Description of Countries
 *
 * @author amit
 */
class SpiFormVer6Table
{
    private const string TABLE = 'spi_form_v_6';

    private readonly \Doctrine\DBAL\Connection $db;

    /** $translator is optional: only the lists render text, and they get the container's. */
    public function __construct(private readonly Adapter $adapter, private readonly ?Translator $translator = null)
    {
        $this->db = Dbal::connection($adapter);
    }

    private function translate(string $text): string
    {
        return $this->translator?->translate($text) ?? $text;
    }

    /**
     * Updates the audits that match $where and returns the number changed.
     * Column names are quoted, as Laminas quoted them.
     */
    private function updateAudits(array $set, array $where): int
    {
        return (int) $this->db->update(self::TABLE, $this->quoteColumns($set), $this->quoteColumns($where));
    }

    /**
     * Quotes each key, so a column such as `meta-instance-id` works in DBAL's
     * insert and update. An array value is written as NULL, as Laminas wrote
     * it. DBAL would write the text "Array".
     */
    private function quoteColumns(array $row): array
    {
        $quoted = [];
        foreach ($row as $column => $value) {
            $quoted[$this->db->quoteSingleIdentifier((string) $column)] = is_array($value) ? null : $value;
        }
        return $quoted;
    }


    public function getAllLabels()
    {
        $rResult = $this->db->fetchAllAssociative('SELECT * FROM spi_v6_form_labels');
        $response = [];
        foreach ($rResult as $row) {
            $response[$row['field']] = array($row['short_label'], $row['label']);
        }
        return $response;
    }

    public function saveOdkCentralData($params, $correctiveActions, $projectId, $formId)
    {
        if ($params == null || $params == "" || empty($params)) {
            return;
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
            $data['meta-date-marked-as-complete'] = explode("T", $submissionData["end"])[0];
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
                $where = [];
                $values = [];
                self::addEquals('spiv6.uuid', $data["uuid"], $where, $values);
                $sResult = $this->db->fetchAssociative('SELECT spiv6.* FROM spi_form_v_6 AS spiv6 WHERE ' . implode(' AND ', $where), $values);
                $currentFormId = null;
                if (empty($sResult) || $data["uuid"] != $sResult["uuid"]) {
                    $this->db->insert(self::TABLE, $this->quoteColumns($par));
                    $currentFormId = $this->db->lastInsertId();
                } else {
                    //Lost manually updated data for auto syn update
                    //$this->update($par, array('uuid' => $data["uuid"]));
                }
                if (!empty($currentFormId) && $currentFormId > 0) {
                    $facilityDb = new SpiRtFacilitiesTable($dbAdapter);
                    // Link on ingest either way, so a pending audit is still visible
                    // to a mapped user. Only approval may create a new facility.
                    $facilityDb->addFacilityBasedOnForm($currentFormId, 6, $approveStatus == 'approved');
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
        $where = ["spiv6.status = 'approved'"];
        $values = [];
        self::addFieldFilter($params, $where, $values);
        self::addRoundNumbers($params, $where, $values);
        self::addDateRange($params, $where, $values);
        if (isset($params['testPoint']) && trim($params['testPoint']) != '') {
            if (trim($params['testPoint']) != 'other') {
                self::addEquals('spiv6.testingpointtype', $params['testPoint'], $where, $values);
            } else {
                self::addEquals('spiv6.testingpointtype_other', $params['testPointName'] ?? '', $where, $values);
            }
        }
        self::addLevelAndAffiliation($params, $where, $values);
        self::addScoreLevel($params, $where);
        self::addTokenRule($loginContainer, $where, $values);
        [$mappingColumns, $mappingJoin] = self::addInlineMapping($loginContainer, $where, $values, 'fm');
        [$filterColumns, $filterJoin] = self::addFacilityFilters($params, true, $where, $values);
        $rResult = $this->db->fetchAllAssociative(
            'SELECT spiv6.assesmentofaudit AS assesmentofaudit, spiv6.AUDIT_SCORE_PERCENTAGE AS AUDIT_SCORE_PERCENTAGE' . $mappingColumns . $filterColumns
            . ' FROM spi_form_v_6 AS spiv6' . $mappingJoin . $filterJoin . ' WHERE ' . implode(' AND ', $where),
            $values
        );

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
            $scorePer = round((float) $aRow['AUDIT_SCORE_PERCENTAGE']);
            $row = [];
            $auditScore += (float) $aRow['AUDIT_SCORE_PERCENTAGE'];
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
        // The builder wrote a date that did not read as ''.
        $startDate = (string) $startDate;
        $endDate = (string) $endDate;

        $where = ["spiv6.status='approved'", '(`assesmentofaudit` BETWEEN ? - INTERVAL DATEDIFF(?, ?) DAY AND ?)'];
        $values = [$startDate, $endDate, $startDate, $startDate, $endDate, $startDate];
        self::addTokenRule($loginContainer, $where, $values);
        self::addAuditRound($params, $where, $values);
        if (isset($params['testPoint']) && trim($params['testPoint']) != '') {
            self::addEquals('spiv6.testingpointtype', $params['testPoint'], $where, $values);
            if (trim($params['testPoint']) != 'other') {
                self::addEquals('spiv6.testingpointname', $params['testPointName'] ?? '', $where, $values);
            } else {
                self::addEquals('spiv6.testingpointtype_other', $params['testPointName'] ?? '', $where, $values);
            }
        }
        self::addLevelAndAffiliation($params, $where, $values);
        [$mappingColumns, $mappingJoin] = self::addInlineMapping($loginContainer, $where, $values, 'fm');
        [$filterColumns, $filterJoin] = self::addFacilityFilters($params, false, $where, $values);
        self::addScoreLevel($params, $where);
        $rResult = $this->db->fetchAllAssociative(
            'SELECT ? AS newestDate, ? AS oldestDate, COUNT(*) AS totalDataPoints,'
            . ' SUM(IF((AUDIT_SCORE_PERCENTAGE) < 40, 1,0)) AS level0,'
            . ' SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 40 and (AUDIT_SCORE_PERCENTAGE) < 60, 1,0)) AS level1,'
            . ' SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 60 and (AUDIT_SCORE_PERCENTAGE) < 80, 1,0)) AS level2,'
            . ' SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 80 and (AUDIT_SCORE_PERCENTAGE) < 90, 1,0)) AS level3,'
            . ' SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 90, 1,0)) AS level4' . $mappingColumns . $filterColumns
            . ' FROM spi_form_v_6 AS spiv6' . $mappingJoin . $filterJoin . ' WHERE ' . implode(' AND ', $where),
            $values
        );


        return $rResult;
    }

    public function getPerformanceLast180DaysV6()
    {
        $loginContainer = new Container('credo');
        $today = date('Y-m-d');
        $last180Date = date('Y-m-d', strtotime('-180 days', strtotime($today)));
        $where = ["spiv6.status = 'approved'", '(`assesmentofaudit` BETWEEN CURDATE() - INTERVAL 180 DAY AND CURDATE())'];
        $values = [$today, $last180Date];
        self::addTokenRule($loginContainer, $where, $values);
        [$mappingColumns, $mappingJoin] = self::addInlineMapping($loginContainer, $where, $values);
        $rResult = $this->db->fetchAllAssociative(
            'SELECT ? AS newestDate, ? AS oldestDate, COUNT(*) AS totalDataPoints,'
            . ' SUM(IF((AUDIT_SCORE_PERCENTAGE) < 40, 1,0)) AS level0,'
            . ' SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 40 and (AUDIT_SCORE_PERCENTAGE) < 60, 1,0)) AS level1,'
            . ' SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 60 and (AUDIT_SCORE_PERCENTAGE) < 80, 1,0)) AS level2,'
            . ' SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 80 and (AUDIT_SCORE_PERCENTAGE) < 90, 1,0)) AS level3,'
            . ' SUM(IF((AUDIT_SCORE_PERCENTAGE) >= 90, 1,0)) AS level4' . $mappingColumns
            . ' FROM spi_form_v_6 AS spiv6' . $mappingJoin . ' WHERE ' . implode(' AND ', $where),
            $values
        );


        return $rResult;
    }

    public function getAllSubmissionsV6($sortOrder = 'DESC', $limit = 100)
    {
        $loginContainer = new Container('credo');
        $where = ["spiv6.status != 'deleted'"];
        $values = [];
        self::addTokenRule($loginContainer, $where, $values);
        [$columns, $join] = self::addInlineMapping($loginContainer, $where, $values);
        return $this->db->fetchAllAssociative(
            'SELECT spiv6.*' . $columns . ' FROM spi_form_v_6 AS spiv6' . $join
            . ' WHERE ' . implode(' AND ', $where)
            . ' ORDER BY spiv6.status DESC, spiv6.id ' . self::direction($sortOrder) . ' LIMIT ' . (int) $limit,
            $values
        );
    }

    public function getAllApprovedTestingVolumeV6($params)
    {
        $loginContainer = new Container('credo');
        $where = [];
        $values = [];
        self::addRoundNumbers($params, $where, $values);
        self::addTokenRule($loginContainer, $where, $values);
        self::addDateRange($params, $where, $values);
        self::addAuditRound($params, $where, $values);
        if (isset($params['testPoint']) && trim($params['testPoint']) != '') {
            self::addEquals('spiv6.testingpointtype', $params['testPoint'], $where, $values);
            if (trim($params['testPoint']) != 'other') {
                self::addEquals('spiv6.testingpointname', $params['testPointName'] ?? '', $where, $values);
            } else {
                self::addEquals('spiv6.testingpointtype_other', $params['testPointName'] ?? '', $where, $values);
            }
        }
        self::addLevelAndAffiliation($params, $where, $values);
        [$mappingColumns, $mappingJoin] = self::addInlineMapping($loginContainer, $where, $values, 'fm');
        [$filterColumns, $filterJoin] = self::addFacilityFilters($params, true, $where, $values);
        self::addScoreLevel($params, $where);
        $rResult = $this->db->fetchAllAssociative(
            'SELECT spiv6.*' . $mappingColumns . $filterColumns . ' FROM spi_form_v_6 AS spiv6' . $mappingJoin . $filterJoin
            . ($where === [] ? '' : ' WHERE ' . implode(' AND ', $where)),
            $values
        );

        return $rResult;
    }

    /**
     * Audits that carry no facility link, same definition as `audits:unlinked`.
     *
     * Every location filter reaches an audit through spi_form_v_6.facility, so a
     * row with that column empty belongs to no location at all.
     */
    private const UNLINKED_AUDIT = '(spiv6.facility IS NULL OR spiv6.facility = 0)';

    /**
     * ($column IN ($ids) OR UNLINKED_AUDIT), with the ids bound.
     *
     * @param list<string> $ids From CommonService::inList().
     */
    private static function inOrUnlinked(string $column, array $ids): PredicateSet
    {
        return new PredicateSet([new In($column, $ids), new Literal(self::UNLINKED_AUDIT)], PredicateSet::OP_OR);
    }

    /**
     * Whether the signed-in user sees every audit: no token and no location
     * mapping, as a Super Admin. auditInScope() passes every audit for them.
     */
    public static function seesEveryAudit(): bool
    {
        $loginContainer = new Container('credo');
        $hasTokens = !empty($loginContainer->token);
        $hasMapping = !empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType != '';

        return !$hasTokens && !$hasMapping;
    }

    /**
     * Whether the signed-in user may open or change this audit, under the
     * same token and location rules as the lists. A Super Admin has neither
     * restriction, so every audit passes. An id the user cannot reach reads
     * as missing.
     */
    public function auditInScope(mixed $id): bool
    {
        // An audit id is digits. MySQL would read "4' OR '1'='1" as 4.
        if (!is_scalar($id) || preg_match('/^\d+$/', trim((string) $id)) !== 1) {
            return false;
        }
        if (self::seesEveryAudit()) {
            return true;
        }
        $loginContainer = new Container('credo');
        $where = ['spiv6.id = ?'];
        $values = [trim((string) $id)];
        self::addTokenRule($loginContainer, $where, $values);
        [, $joins] = self::addLocationScope([], $loginContainer, $where, $values);
        return $this->db->fetchOne('SELECT spiv6.id FROM spi_form_v_6 AS spiv6' . $joins . ' WHERE ' . implode(' AND ', $where), $values) !== false;
    }

    /**
     * Scope a select over spi_form_v_6, aliased 'spiv6', to the user's locations.
     *
     * A mapped user sees the mapped locations only. An audit whose location
     * cannot be resolved (no facility, a facility that is gone, or a facility
     * without that level) belongs to no location, so only a Super Admin sees
     * it: a Super Admin has no mapping (UsersTable::userEventLog()). The
     * listing flags those audits, the Facility Link filter pulls up just
     * those, and assigning a facility on the edit screen returns the audit to
     * its location.
     *
     * The location filters below still admit UNLINKED_AUDIT, so filtering the
     * list does not hide the audits a Super Admin has to fix.
     *
     * The scope goes on the total count as well as the filtered query. A location
     * mapping is a permission boundary, not a search filter, and leaving it off the
     * total is what produced counts like "0 of 0 (filtered from 3009)".
     *
     * Still a primary key lookup on the facilities side, as 1.2.4 intended.
     *
     * @param \Laminas\Db\Sql\Select $query
     * @return \Laminas\Db\Sql\Select
     */
    private function applyV6LocationScope($query, $parameters, $loginContainer)
    {
        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType != '') {
            $mappedIds = CommonService::inList($loginContainer->userMappedIds);
            // Mapping exists
            if ($loginContainer->userMappingType === 'country') {
                // User mapped by country: show mapped country data and all related provinces and districts
                $query = $query->join(array('fm' => 'spi_rt_3_facilities'), 'fm.id=spiv6.facility', array(), 'left')
                    ->where(['fm.country' => $mappedIds]);
            } elseif ($loginContainer->userMappingType === 'province') {
                // User mapped by province: show only mapped provinces
                $query = $query->join(array('fm' => 'spi_rt_3_facilities'), 'fm.id=spiv6.facility', array(), 'left')
                    ->where(['fm.province' => $mappedIds]);
            } elseif ($loginContainer->userMappingType === 'district') {
                // User mapped by district: show only mapped districts
                $query = $query->join(array('fm' => 'spi_rt_3_facilities'), 'fm.id=spiv6.facility', array(), 'left')
                    ->where(['fm.district' => $mappedIds]);
            }
        }
        if (isset($parameters['country']) && $parameters['country'] != '') {
            $query = $query->join(array('f1' => 'spi_rt_3_facilities'), 'f1.id=spiv6.facility', array('country'), 'left')
                ->where(self::inOrUnlinked('f1.country', CommonService::inList($parameters['country'])));
        }
        if (isset($parameters['province']) && $parameters['province'] != '') {
            $query = $query->join(array('f2' => 'spi_rt_3_facilities'), 'f2.id=spiv6.facility', array('province'), 'left')
                ->where(self::inOrUnlinked('f2.province', CommonService::inList($parameters['province'])));
        }
        if (isset($parameters['district']) && $parameters['district'] != '') {
            $query = $query->join(array('f3' => 'spi_rt_3_facilities'), 'f3.id=spiv6.facility', array('district'), 'left')
                ->where(self::inOrUnlinked('f3.district', CommonService::inList($parameters['district'])));
        }
        if (isset($parameters['facilityLink']) && $parameters['facilityLink'] == 'unassigned') {
            $query = $query->where(self::UNLINKED_AUDIT);
        }

        return $query;
    }

    /** "?, ?, ?" for each of $values. */
    private static function placeholders(array $values): string
    {
        return implode(', ', array_fill(0, count($values), '?'));
    }

    /**
     * The user's token rule: audits whose token the user holds. Adds nothing
     * when the user holds no token.
     *
     * @param list<string> $where
     * @param list<mixed> $values
     */
    private static function addTokenRule(Container $loginContainer, array &$where, array &$values): void
    {
        if (empty($loginContainer->token)) {
            return;
        }
        $tokens = CommonService::inList($loginContainer->token);
        $where[] = 'spiv6.token IN (' . self::placeholders($tokens) . ')';
        array_push($values, ...$tokens);
    }

    /**
     * The location mapping several dashboard queries write out in place: an
     * inner join to the audit's facility, which also adds the facility's
     * location columns to each row. A country mapping adds country, province
     * and district. A province or district mapping adds that column only.
     * Returns the columns and the join, as SQL to append. $alias names the
     * join, as each query did. A mapping type this code does not know adds
     * nothing, as before.
     *
     * @param list<string> $where
     * @param list<mixed> $values
     * @return array{0: string, 1: string}
     */
    private static function addInlineMapping(Container $loginContainer, array &$where, array &$values, string $alias = 'f'): array
    {
        if (empty($loginContainer->userMappedIds) || !is_array($loginContainer->userMappedIds) || $loginContainer->userMappingType == '') {
            return ['', ''];
        }
        $columns = match ($loginContainer->userMappingType) {
            'country' => ['country', 'province', 'district'],
            'province' => ['province'],
            'district' => ['district'],
            default => null,
        };
        if ($columns === null) {
            return ['', ''];
        }
        $ids = CommonService::inList($loginContainer->userMappedIds);
        $where[] = $alias . '.' . $loginContainer->userMappingType . ' IN (' . self::placeholders($ids) . ')';
        array_push($values, ...$ids);
        $select = '';
        foreach ($columns as $column) {
            $select .= ', ' . $alias . '.' . $column . ' AS ' . $column;
        }
        return [$select, ' INNER JOIN spi_rt_3_facilities AS ' . $alias . ' ON ' . $alias . '.id = spiv6.facility'];
    }

    /**
     * applyV6LocationScope() as SQL with bound values. Returns the columns and
     * the joins to append. The country, province and district filters add
     * that column of the facility to each row, as the builder did.
     *
     * @param list<string> $where
     * @param list<mixed> $values
     * @return array{0: string, 1: string}
     */
    private static function addLocationScope($parameters, Container $loginContainer, array &$where, array &$values): array
    {
        $select = '';
        $joins = '';
        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType != '') {
            $column = match ($loginContainer->userMappingType) {
                'country', 'province', 'district' => $loginContainer->userMappingType,
                default => null,
            };
            if ($column !== null) {
                $ids = CommonService::inList($loginContainer->userMappedIds);
                $joins .= ' LEFT JOIN spi_rt_3_facilities AS fm ON fm.id = spiv6.facility';
                $where[] = 'fm.' . $column . ' IN (' . self::placeholders($ids) . ')';
                array_push($values, ...$ids);
            }
        }
        foreach (['f1' => 'country', 'f2' => 'province', 'f3' => 'district'] as $alias => $column) {
            if (isset($parameters[$column]) && $parameters[$column] != '') {
                $ids = CommonService::inList($parameters[$column]);
                $select .= ', ' . $alias . '.' . $column . ' AS ' . $column;
                $joins .= ' LEFT JOIN spi_rt_3_facilities AS ' . $alias . ' ON ' . $alias . '.id = spiv6.facility';
                $where[] = '(' . $alias . '.' . $column . ' IN (' . self::placeholders($ids) . ') OR ' . self::UNLINKED_AUDIT . ')';
                array_push($values, ...$ids);
            }
        }
        if (isset($parameters['facilityLink']) && $parameters['facilityLink'] == 'unassigned') {
            $where[] = self::UNLINKED_AUDIT;
        }
        return [$select, $joins];
    }

    /** The builder's [column => value]: IS NULL for null, IN for an array, = for anything else. */
    private static function addEquals(string $column, mixed $value, array &$where, array &$values): void
    {
        if ($value === null) {
            $where[] = $column . ' IS NULL';
            return;
        }
        if (is_array($value)) {
            $list = CommonService::inList($value);
            $where[] = $column . ' IN (' . self::placeholders($list) . ')';
            array_push($values, ...$list);
            return;
        }
        $where[] = $column . ' = ?';
        $values[] = $value;
    }

    /** The roundno filter: audits in any of the chosen rounds. */
    private static function addRoundNumbers($params, array &$where, array &$values): void
    {
        if (isset($params['roundno']) && $params['roundno'] != '') {
            self::addEquals('spiv6.auditroundno', CommonService::inList($params['roundno']), $where, $values);
        }
    }

    /** The auditRndNo filter: audits of one round. */
    private static function addAuditRound($params, array &$where, array &$values): void
    {
        if (isset($params['auditRndNo']) && $params['auditRndNo'] != '') {
            self::addEquals('spiv6.auditroundno', $params['auditRndNo'], $where, $values);
        }
    }

    /** The dateRange filter, "<start> to <end>". Applies only when both dates read. */
    private static function addDateRange($params, array &$where, array &$values): void
    {
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
        if (trim((string) $startDate) != "" && trim((string) $endDate) != "") {
            $where[] = 'spiv6.assesmentofaudit >= ? AND spiv6.assesmentofaudit <= ?';
            $values[] = $startDate;
            $values[] = $endDate;
        }
    }

    /** The level and affiliation filters. */
    private static function addLevelAndAffiliation($params, array &$where, array &$values): void
    {
        if (isset($params['level']) && $params['level'] != '') {
            self::addEquals('spiv6.level', $params['level'], $where, $values);
        }
        if (isset($params['affiliation']) && $params['affiliation'] != '') {
            self::addEquals('spiv6.affiliation', $params['affiliation'], $where, $values);
        }
    }

    /** The scoreLevel filter: the score band of levels 0 to 4. */
    private static function addScoreLevel($params, array &$where): void
    {
        if (!isset($params['scoreLevel']) || $params['scoreLevel'] == '') {
            return;
        }
        $band = match (true) {
            $params['scoreLevel'] == 0 => 'spiv6.AUDIT_SCORE_PERCENTAGE < 40',
            $params['scoreLevel'] == 1 => 'spiv6.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 59',
            $params['scoreLevel'] == 2 => 'spiv6.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 79',
            $params['scoreLevel'] == 3 => 'spiv6.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 89',
            $params['scoreLevel'] == 4 => 'spiv6.AUDIT_SCORE_PERCENTAGE >= 90',
            default => null,
        };
        if ($band !== null) {
            $where[] = $band;
        }
    }

    /**
     * The chart pages' country, province and district filters: an inner join
     * to the audit's facility that adds its country, province and district
     * to each row. When $arraysOnly, a filter applies only when the page sent
     * a list, as some charts did. Returns the columns and the join.
     *
     * @return array{0: string, 1: string}
     */
    private static function addFacilityFilters($params, bool $arraysOnly, array &$where, array &$values): array
    {
        $sent = false;
        foreach (['country', 'province', 'district'] as $column) {
            if (isset($params[$column]) && $params[$column] != '') {
                $sent = true;
            }
        }
        if (!$sent) {
            return ['', ''];
        }
        foreach (['country', 'province', 'district'] as $column) {
            if (isset($params[$column]) && $params[$column] != '' && (!$arraysOnly || is_array($params[$column]))) {
                self::addEquals('f.' . $column, CommonService::inList($params[$column]), $where, $values);
            }
        }
        return [
            ', f.country AS country, f.province AS province, f.district AS district',
            ' INNER JOIN spi_rt_3_facilities AS f ON f.id = spiv6.facility',
        ];
    }

    /**
     * The fieldName and val filter some chart requests may carry. No page
     * sends it. fieldName must be a column name, such as "level" or
     * "spiv6.level". Anything else matches nothing.
     */
    private static function addFieldFilter($params, array &$where, array &$values): void
    {
        if (!isset($params['fieldName']) || trim($params['fieldName']) == '') {
            return;
        }
        $field = trim((string) $params['fieldName']);
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*(\.[A-Za-z_][A-Za-z0-9_]*)?$/', $field) !== 1) {
            $where[] = '1 = 0';
            return;
        }
        self::addEquals('`' . str_replace('.', '`.`', $field) . '`', $params['val'] ?? null, $where, $values);
    }

    /** The audits behind the round-wise charts, newest first. Deleted audits are left out. */
    private function roundWiseRows($params): array
    {
        $loginContainer = new Container('credo');
        $where = ["spiv6.status != 'deleted'"];
        $values = [];
        self::addRoundNumbers($params, $where, $values);
        self::addTokenRule($loginContainer, $where, $values);
        self::addDateRange($params, $where, $values);
        self::addAuditRound($params, $where, $values);
        if (isset($params['testPoint']) && trim($params['testPoint']) != '') {
            if (strtolower(trim($params['testPoint'])) == 'other') {
                self::addEquals('spiv6.testingpointtype_other', $params['testPointName'] ?? '', $where, $values);
            } else {
                self::addEquals('spiv6.testingpointtype', $params['testPoint'], $where, $values);
            }
        }
        self::addLevelAndAffiliation($params, $where, $values);
        self::addScoreLevel($params, $where);
        [$mappingColumns, $mappingJoin] = self::addInlineMapping($loginContainer, $where, $values, 'fm');
        [$filterColumns, $filterJoin] = self::addFacilityFilters($params, true, $where, $values);
        self::addFieldFilter($params, $where, $values);
        return $this->db->fetchAllAssociative(
            'SELECT spiv6.*' . $mappingColumns . $filterColumns . ' FROM spi_form_v_6 AS spiv6' . $mappingJoin . $filterJoin
            . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY id DESC',
            $values
        );
    }

    /** ASC or DESC from a caller's sort order, as the builder read it. */
    private static function direction(mixed $sortOrder): string
    {
        return strtoupper(trim((string) $sortOrder)) === 'DESC' ? 'DESC' : 'ASC';
    }

    public function fetchAllSubmissionsDetails($parameters, $acl)
    {
        $loginContainer = new Container('credo');
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */
        $queryContainer = new Container('query');
        /* Index 0 is the bulk-approve checkbox and index 1 the row expander; neither
         * is sortable and neither has a column of its own, but both need an entry so
         * the DataTables column indices keep lining up with these maps. */
        $aColumns = array('spiv6.id', 'spiv6.id', 'facilityname', 'auditroundno', "DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", 'testingpointtype', 'affiliation', 'AUDIT_SCORE_PERCENTAGE', 'spiv6.status');
        $orderColumns = array('spiv6.id', 'spiv6.id', 'facilityname', 'auditroundno', 'assesmentofaudit', 'testingpointtype', 'affiliation', 'AUDIT_SCORE_PERCENTAGE', 'spiv6.status');

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
                if ($parameters['bSortable_' . (int) $parameters['iSortCol_' . $i]] == "true" && isset($orderColumns[(int) $parameters['iSortCol_' . $i]])) {
                    $sOrder .= $orderColumns[(int) $parameters['iSortCol_' . $i]] . " " . ($parameters['sSortDir_' . $i]) . ", ";
                }
            }
            $sOrder = rtrim($sOrder, ", ");
        }

        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */

        $sWhere = CommonService::dataTablesSearch($aColumns, $parameters);

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
            $sQuery = $sQuery->where(['spiv6.auditroundno' => $parameters['auditRndNo']]);
        }
        if (trim($startDate) != "" && trim($endDate) != "") {
            $sQuery = $sQuery->where(['spiv6.assesmentofaudit >= ?' => $startDate, 'spiv6.assesmentofaudit <= ?' => $endDate]);
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
                $sQuery = $sQuery->where(['spiv6.testingpointtype' => $parameters['testPoint']]);
            } else {
                $sQuery = $sQuery->where(['spiv6.testingpointtype_other' => $parameters['testPointName'] ?? '']);
            }
        }
        if ($parameters['affiliation'] != '') {
            $sQuery = $sQuery->where(['spiv6.affiliation' => $parameters['affiliation']]);
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
        $sQuery = $this->applyV6LocationScope($sQuery, $parameters, $loginContainer);

        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where(['spiv6.token' => CommonService::inList($loginContainer->token)]);
        }
        if ($sWhere !== null) {
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
        $rResult = $this->db->fetchAllAssociative($sQueryStr);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->buildSqlString($sQuery);
        $iFilteredTotal = $this->db->executeQuery($fQuery)->rowCount();

        /* Total data set length */
        $tQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
            ->where('spiv6.status != "deleted"');
        //  ->join(array('f'=>'spi_rt_3_facilities'),
        //                 'f.id=spiv3.facility',
        //                 array('province','district'),'left');
        if ($parameters['auditRndNo'] != '') {
            $tQuery = $tQuery->where(['spiv6.auditroundno' => $parameters['auditRndNo']]);
        }
        if (trim($startDate) != "" && trim($endDate) != "") {
            $tQuery = $tQuery->where(['spiv6.assesmentofaudit >= ?' => $startDate, 'spiv6.assesmentofaudit <= ?' => $endDate]);
        }
        /*if (isset($parameters['testPoint']) && trim($parameters['testPoint']) != '' && strtolower(trim($parameters['testPoint'])) == 'other') {
            $tQuery = $tQuery->where(['spiv6.testingpointtype' => $parameters['testPoint'] ?? '']);
        }

        if (strtolower(trim($parameters['testPoint'])) == 'other') {
            $tQuery = $tQuery->where(['spiv6.testingpointtype_other' => $parameters['testPointName'] ?? '']);
        }*/
        if (isset($parameters['testPoint']) && trim($parameters['testPoint']) != '') {

            if (trim($parameters['testPoint']) != 'other') {
                $tQuery = $tQuery->where(['spiv6.testingpointtype' => $parameters['testPoint']]);
            } else {
                $tQuery = $tQuery->where(['spiv6.testingpointtype_other' => $parameters['testPointName'] ?? '']);
            }
        }
        if ($parameters['affiliation'] != '') {
            $tQuery = $tQuery->where(['spiv6.affiliation' => $parameters['affiliation']]);
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
        $tQuery = $this->applyV6LocationScope($tQuery, $parameters, $loginContainer);

        if (!empty($loginContainer->token)) {
            $tQuery = $tQuery->where(['spiv6.token' => CommonService::inList($loginContainer->token)]);
        }
        $tQueryStr = $sql->buildSqlString($tQuery); // Get the string of the Sql, instead of the Select-instance
        $iTotal = $this->db->executeQuery($tQueryStr)->rowCount();
        $output = array(
            "sEcho" => (int) $parameters['sEcho'],
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array(),
        );

        $role = $loginContainer->roleCode;
        $downloadPdfAction = (bool) $acl->isAllowed($role, 'Application\Controller\SpiV6Controller', 'download-pdf');

        $approveStatusAction = (bool) $acl->isAllowed($role, 'Application\Controller\SpiV6Controller', 'approve-status');

        /* Edit is what fixes an audit the Needs attention card points at, so it
         * belongs on the list itself rather than only on manage-facility. */
        $editAction = (bool) $acl->isAllowed($role, 'Application\Controller\SpiV6Controller', 'edit');

        $deleteAction = (bool) $acl->isAllowed($role, 'Application\Controller\SpiV6Controller', 'delete');

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
            $edit = "";
            $remove = "";
            $aRow['AUDIT_SCORE_PERCENTAGE'] = (float) ($aRow['AUDIT_SCORE_PERCENTAGE'] ?? 0);
            $scorePer = round((float) $aRow['AUDIT_SCORE_PERCENTAGE']);
            $auditScore += (float) $aRow['AUDIT_SCORE_PERCENTAGE'];
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
            /* Bulk approve, replacing the dual listbox on manage-facility. Only
             * pending rows are selectable — approving an approved audit is a no-op
             * that would still rewrite its facility. */
            // A disabled box on the other rows shows what the column is for.
            // It has no approveSelect class, so select-all and the count skip it.
            if (!$approveStatusAction) {
                $row[] = '';
            } elseif ($aRow['status'] == 'pending') {
                $row[] = '<input type="checkbox" class="approveSelect" value="' . (int) $aRow['id'] . '">';
            } else {
                $row[] = '<input type="checkbox" disabled="disabled">';
            }
            $row[] = '';
            /* Escaped because DataTables writes this cell as HTML and the name arrives
             * verbatim from the ODK submission, so a crafted facility name would run
             * as script for everyone viewing the list. */
            $facilityName = CommonService::escapeHtml(trim((string) $aRow['facilityname']));
            if (empty($aRow['facility'])) {
                /* Carries no facility link, so location filters cannot place it. It is
                 * listed anyway rather than hidden; assigning a facility on the edit
                 * screen clears the flag. Blank-named audits show the flag alone. */
                $row[] = ($facilityName != '' ? $facilityName . ' ' : '')
                    . '<span class="label label-warning" title="' . CommonService::escapeHtml($this->translate('Not linked to a facility, so location filters cannot see it. Edit the audit to assign one.')) . '">'
                    . CommonService::escapeHtml($this->translate('Unassigned facility')) . '</span>';
            } else {
                $row[] = $facilityName;
            }

            $row[] = CommonService::escapeHtml($aRow['auditroundno']);
            $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
            //$row[] = (isset($aRow['testingpointname']) && $aRow['testingpointname'] != "" ? $aRow['testingpointname'] : $aRow['testingpointtype']);
            $row[] = CommonService::escapeHtml($aRow['testingpointtype']);
            $row[] = CommonService::escapeHtml($aRow['affiliation']);
            $row[] = round((float) ($aRow['AUDIT_SCORE_PERCENTAGE'] ?? 0), 2);
            $row[] = CommonService::escapeHtml(ucwords($aRow['status']));
            //$print = '<a href="/spi-v3/print/' . $aRow['id'] . '" target="_blank" style="white-space:nowrap;"><i class="fa fa-print"></i> Print</a>';
            if ($aRow['status'] == 'pending' && $approveStatusAction) {
                $approve = '<br><a href="javascript:void(0);" onclick="approveStatus(' . (int) $aRow['id'] . ')"  style="white-space:nowrap;"><i class="fa fa-check"></i>  Approve</a>';
            }

            if ($downloadPdfAction) {
                $downloadPdf = '<br><a href="javascript:void(0);" onclick="downloadPdf(' . (int) $aRow['id'] . ')" style="white-space:nowrap;"><i class="fa fa-download"></i> PDF</a>';
            }
            if ($editAction) {
                $edit = '<br><a href="/spi-v6/edit/' . (int) $aRow['id'] . '" style="white-space:nowrap;"><i class="fa fa-pencil"></i> Edit</a>';
            }
            if ($deleteAction) {
                /* The raw name is handed over rather than scraped from the cell,
                 * which also carries the "Unassigned facility" badge. json_encode
                 * makes it a JS string literal, escapeHtml makes that safe inside
                 * the attribute; the browser undoes the second on the way in. */
                $deleteName = CommonService::escapeHtml(
                    json_encode(trim((string) $aRow['facilityname']))
                );
                $remove = '<br><a href="javascript:void(0);" onclick="deleteAudit(' . (int) $aRow['id'] . ', ' . $deleteName . ');" style="white-space:nowrap;"><i class="fa fa-times"></i> Delete</a>';
            }
            $row[] = $approve . " " . $downloadPdf . " " . $edit . " " . $remove;
            /* Every score column is a varchar carrying whatever ODK submitted, so
             * these are text cells like any other rather than numbers. */
            $row[] = CommonService::escapeHtml($aRow['PERSONAL_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['PHYSICAL_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['SAFETY_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['PRETEST_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['TEST_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['POST_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['EQA_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['RTRI_SCORE']);

            $t = (float) $aRow['PERSONAL_SCORE'] + (float) $aRow['PHYSICAL_SCORE'] + (float) $aRow['SAFETY_SCORE'] + (float) $aRow['PRETEST_SCORE'] + (float) $aRow['TEST_SCORE'] + (float) $aRow['POST_SCORE'] + (float) $aRow['EQA_SCORE'];
            if (trim($aRow['performrtritesting']) == 'Yes') {
                $t += (float) $aRow['RTRI_SCORE'];
            }
            $row[] = $t . "(" . round((float) ($aRow['AUDIT_SCORE_PERCENTAGE']), 2) . ")";
            $output['aaData'][] = $row;
        }
        //get earliest date
        $eQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))->columns(array('assesmentofaudit'))->order('assesmentofaudit ASC');
        // The earliest audit the user may see, scoped as the list is.
        if (!empty($loginContainer->token)) {
            $eQuery->where(['spiv6.token' => CommonService::inList($loginContainer->token)]);
        }
        $eQuery = $this->applyV6LocationScope($eQuery, [], $loginContainer);
        $eQueryStr = $sql->buildSqlString($eQuery); // Get the string of the Sql, instead of the Select-instance
        $eResult = $this->db->fetchAssociative($eQueryStr);
        //get duplicate value
        $dpResult = $this->db->fetchAllAssociative("SELECT `meta-instance-id`, COUNT(*) c FROM spi_form_v_6 GROUP BY `meta-instance-id` HAVING c > 1");

        /* Audits with no facility link, counted across the whole instance rather than
         * the current filters, the way the duplicate count works. They are the rows
         * `audits:unlinked` reports; the banner gives them a way in from the UI. */
        $ulResult = $this->db->fetchAssociative(
            'SELECT COUNT(*) AS c FROM `spi_form_v_6` spiv6
              WHERE spiv6.status != "deleted" AND ' . self::UNLINKED_AUDIT
        );

        $output['avgAuditScore'] = (count($rResult) > 0) ? round($auditScore / count($rResult), 2) : 0;
        $output['levelZeroCount'] = count($levelZero);
        $output['levelOneCount'] = count($levelOne);
        $output['levelTwoCount'] = count($levelTwo);
        $output['levelThreeCount'] = count($levelThree);
        $output['levelFourCount'] = count($levelFour);
        $output['eDate'] = $eResult['assesmentofaudit'];
        $output['duplicate'] = count($dpResult);
        $output['unassigned'] = (int) ($ulResult['c'] ?? 0);
        return $output;
    }

    public function fetchAllV6DuplicateSubmissionsDetails()
    {
        // Every copy of each duplicated submission, oldest first, so the view can
        // keep the first copy and offer the rest for removal by their own id.
        return $this->db->fetchAllAssociative("SELECT s.`meta-instance-id`, s.`id`, s.`facilityname`, s.`status`, s.`auditroundno`, s.`AUDIT_SCORE_PERCENTAGE`, s.`affiliation`, s.`level`, s.`assesmentofaudit`, s.`testingpointtype`, s.`testingpointname`
            FROM spi_form_v_6 s
            INNER JOIN (SELECT `meta-instance-id` FROM spi_form_v_6 GROUP BY `meta-instance-id` HAVING COUNT(*) > 1) d ON d.`meta-instance-id` = s.`meta-instance-id`
            ORDER BY s.`meta-instance-id`, s.`id`");
    }

    public function getFormData($id, $pdfDowload = 'no')
    {
        $loginContainer = new Container('credo');
        $username = $loginContainer->login;
        $trackTable = new EventLogTable($this->adapter);
        $where = [];
        $values = [];
        self::addEquals('spiv6.id', $id, $where, $values);
        $sResult = $this->fetchRowObject('SELECT spiv6.* FROM spi_form_v_6 AS spiv6 WHERE ' . implode(' AND ', $where), $values);
        if ($sResult) {
            if (trim($sResult['facility']) != '' || trim($sResult['facilityid']) != '' || trim($sResult['facilityname']) != '') {
                $match = null;
                if (isset($sResult['facility']) && $sResult['facility'] > 0) {
                    $match = ['spirt3.id', $sResult['facility']];
                } elseif (isset($sResult['facilityid']) && $sResult['facilityid'] != '') {
                    $match = ['spirt3.facility_id', $sResult['facilityid']];
                } elseif (isset($sResult['facilityname']) && $sResult['facilityname'] != '') {
                    $match = ['spirt3.facility_name', $sResult['facilityname']];
                }
                if ($match !== null) {
                    $sResult['facilityInfo'] = $this->fetchRowObject(
                        'SELECT spirt3.id AS fId, spirt3.facility_id AS ffId, spirt3.facility_name AS fName, spirt3.email AS fEmail,'
                        . ' spirt3.contact_person AS fCPerson, spirt3.latitude AS fLatitude, spirt3.longitude AS fLongitude'
                        . ' FROM spi_rt_3_facilities AS spirt3 WHERE ' . $match[0] . ' = ?',
                        [$match[1]]
                    );
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

    /**
     * One row as Laminas returned it: an object whose columns also read as
     * properties, or null. Templates read these rows with `->`.
     */
    private function fetchRowObject(string $sql, array $values = []): ?\ArrayObject
    {
        $row = $this->db->fetchAssociative($sql, $values);
        return $row === false ? null : new \ArrayObject($row, \ArrayObject::ARRAY_AS_PROPS);
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

        $this->updateAudits($updateData, ['id' => $id]);
    }

    public function updateAuditMailSentStatus($auditIds)
    {
        $auditIdArray = explode(",", (string) $auditIds);
        $results = $this->db->fetchAllAssociative(
            'SELECT id, form_metadata FROM spi_form_v_6 WHERE id IN (' . self::placeholders($auditIdArray) . ')',
            $auditIdArray
        );

        foreach ($results as $row) {
            $this->updateFormMetadata($row['id'], 'audit_email_sent', 'yes', $row['form_metadata']);
        }
    }

    public function getAuditRoundWiseDataV6($params)
    {
        $rResult = $this->roundWiseRows($params);

        $response = [];

        foreach ($rResult as $row) {

            $response[0]['PERSONAL_SCORE'][] = (float) $row['PERSONAL_SCORE'];
            $response[0]['PHYSICAL_SCORE'][] = (float) $row['PHYSICAL_SCORE'];
            $response[0]['SAFETY_SCORE'][] = (float) $row['SAFETY_SCORE'];
            $response[0]['PRETEST_SCORE'][] = (float) $row['PRETEST_SCORE'];
            $response[0]['TEST_SCORE'][] = (float) $row['TEST_SCORE'];
            $response[0]['POST_SCORE'][] = (float) $row['POST_SCORE'];
            $response[0]['EQA_SCORE'][] = (float) $row['EQA_SCORE'];
            $response[0]['RTRI_SCORE'][] = (float) $row['RTRI_SCORE'];
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
        $rResult = $this->roundWiseRows($params);

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
        $rResult = $this->roundWiseRows($params);

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
        $where = ["spiv6.status = 'approved'"];
        $values = [];
        self::addRoundNumbers($params, $where, $values);
        self::addTokenRule($loginContainer, $where, $values);
        self::addDateRange($params, $where, $values);
        self::addAuditRound($params, $where, $values);
        if (isset($params['testPoint']) && trim($params['testPoint']) != '') {
            self::addEquals('spiv6.testingpointtype', $params['testPoint'], $where, $values);
            if (trim($params['testPoint']) != 'other') {
                self::addEquals('spiv6.testingpointname', $params['testPointName'] ?? '', $where, $values);
            } else {
                self::addEquals('spiv6.testingpointtype_other', $params['testPointName'] ?? '', $where, $values);
            }
        }
        self::addLevelAndAffiliation($params, $where, $values);
        [$mappingColumns, $mappingJoin] = self::addInlineMapping($loginContainer, $where, $values, 'fm');
        [$filterColumns, $filterJoin] = self::addFacilityFilters($params, true, $where, $values);
        self::addScoreLevel($params, $where);
        $rResult = $this->db->fetchAllAssociative(
            'SELECT spiv6.*' . $mappingColumns . $filterColumns . ' FROM spi_form_v_6 AS spiv6' . $mappingJoin . $filterJoin
            . ($where === [] ? '' : ' WHERE ' . implode(' AND ', $where)) . ' ORDER BY assesmentofaudit DESC',
            $values
        );


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
            $this->updateAudits($data, ['id' => $id]);
        }
        return $id;
    }

    public function fetchAllApprovedSubmissionsV6($sortOrder = 'DESC')
    {
        $loginContainer = new Container('credo');
        $where = ["spiv6.status = 'approved'"];
        $values = [];
        self::addTokenRule($loginContainer, $where, $values);
        [$columns, $join] = self::addInlineMapping($loginContainer, $where, $values);
        return $this->db->fetchAllAssociative(
            'SELECT spiv6.*' . $columns . ' FROM spi_form_v_6 AS spiv6' . $join
            . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY assesmentofaudit ' . self::direction($sortOrder),
            $values
        );
    }

    public function getHighVolumeSites()
    {
        $loginContainer = new Container('credo');
        $where = ["spiv6.status = 'approved'"];
        $values = [];
        self::addTokenRule($loginContainer, $where, $values);
        [$columns, $join] = self::addInlineMapping($loginContainer, $where, $values);
        return $this->db->fetchAllAssociative(
            'SELECT spiv6.*' . $columns . ' FROM spi_form_v_6 AS spiv6' . $join
            . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY client_tested_HIV_PM DESC LIMIT 10',
            $values
        );
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
                if ($parameters['bSortable_' . (int) $parameters['iSortCol_' . $i]] == "true" && isset($orderColumns[(int) $parameters['iSortCol_' . $i]])) {
                    $sOrder .= $orderColumns[(int) $parameters['iSortCol_' . $i]] . " " . ($parameters['sSortDir_' . $i]) . ", ";
                }
            }
            $sOrder = rtrim($sOrder, ", ");
        }

        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */

        $sWhere = CommonService::dataTablesSearch($aColumns, $parameters);

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
        $sQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
            ->where(array('spiv6.status' => 'approved'));
        if ($parameters['auditRndNo'] != '') {
            $sQuery = $sQuery->where(['spiv6.auditroundno' => $parameters['auditRndNo']]);
        }
        if (trim($startDate) != "" && trim($endDate) != "") {
            $sQuery = $sQuery->where(['spiv6.assesmentofaudit >= ?' => $startDate, 'spiv6.assesmentofaudit <= ?' => $endDate]);
        }
        if (isset($parameters['testPoint']) && trim($parameters['testPoint']) != '') {

            if (trim($parameters['testPoint']) != 'other') {
                $sQuery = $sQuery->where(['spiv6.testingpointtype' => $parameters['testPoint']]);
            } else {
                $sQuery = $sQuery->where(['spiv6.testingpointtype_other' => $parameters['testPointName'] ?? '']);
            }
        }

        if ($parameters['affiliation'] != '') {
            $sQuery = $sQuery->where(['spiv6.affiliation' => $parameters['affiliation']]);
        }
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where(['spiv6.token' => CommonService::inList($loginContainer->token)]);
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
            if ($loginContainer->userMappingType === 'country') {
                // User mapped by country: show mapped country data and all related provinces and districts
                $sQuery = $sQuery->join(array('fm' => 'spi_rt_3_facilities'), 'fm.id=spiv6.facility', array('country', 'province', 'district'))
                    ->where(['fm.country' => CommonService::inList($loginContainer->userMappedIds)]);
            } elseif ($loginContainer->userMappingType === 'province') {
                // User mapped by province: show only mapped provinces
                $sQuery = $sQuery->join(array('fm' => 'spi_rt_3_facilities'), 'fm.id=spiv6.facility', array('province'))
                    ->where(['fm.province' => CommonService::inList($loginContainer->userMappedIds)]);
            } elseif ($loginContainer->userMappingType === 'district') {
                // User mapped by district: show only mapped districts
                $sQuery = $sQuery->join(array('fm' => 'spi_rt_3_facilities'), 'fm.id=spiv6.facility', array('district'))
                    ->where(['fm.district' => CommonService::inList($loginContainer->userMappedIds)]);
            }
        }
        if (isset($parameters['country']) && $parameters['country'] != '') {
            $sQuery = $sQuery->join(array('f1' => 'spi_rt_3_facilities'), 'f1.id=spiv6.facility', array('country'))
                ->where(['f1.country' => CommonService::inList($parameters['country'])]);
        }
        if (isset($parameters['province']) && $parameters['province'] != '') {
            $sQuery = $sQuery->join(array('f2' => 'spi_rt_3_facilities'), 'f2.id=spiv6.facility', array('province'))
                ->where(['f2.province' => CommonService::inList($parameters['province'])]);
        }
        if (isset($parameters['district']) && $parameters['district'] != '') {
            $sQuery = $sQuery->join(array('f3' => 'spi_rt_3_facilities'), 'f3.id=spiv6.facility', array('district'))
                ->where(['f3.district' => CommonService::inList($parameters['district'])]);
        }

        if ($sWhere !== null) {
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
        $rResult = $this->db->fetchAllAssociative($sQueryStr);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->buildSqlString($sQuery);
        $iFilteredTotal = $this->db->executeQuery($fQuery)->rowCount();

        /* Total data set length */
        $tQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
            ->where(array('spiv6.status' => 'approved'));
        if ($parameters['auditRndNo'] != '') {
            $tQuery = $tQuery->where(['spiv6.auditroundno' => $parameters['auditRndNo']]);
        }
        if (trim($startDate) != "" && trim($endDate) != "") {
            $tQuery = $tQuery->where(['spiv6.assesmentofaudit >= ?' => $startDate, 'spiv6.assesmentofaudit <= ?' => $endDate]);
        }
        if (isset($parameters['testPoint']) && trim($parameters['testPoint']) != '') {
            if (trim($parameters['testPoint']) != 'other') {
                $tQuery = $tQuery->where(['spiv6.testingpointtype' => $parameters['testPoint']]);
            } else {
                $tQuery = $tQuery->where(['spiv6.testingpointtype_other' => $parameters['testPointName'] ?? '']);
            }
        }

        if ($parameters['affiliation'] != '') {
            $tQuery = $tQuery->where(['spiv6.affiliation' => $parameters['affiliation']]);
        }
        if (!empty($loginContainer->token)) {
            $tQuery = $tQuery->where(['spiv6.token' => CommonService::inList($loginContainer->token)]);
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
        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType != '') {
            // Mapping exists
            if ($loginContainer->userMappingType === 'country') {
                // User mapped by country: show mapped country data and all related provinces and districts
                $tQuery = $tQuery->join(array('fm' => 'spi_rt_3_facilities'), 'fm.id=spiv6.facility', array('country', 'province', 'district'))
                    ->where(['fm.country' => CommonService::inList($loginContainer->userMappedIds)]);
            } elseif ($loginContainer->userMappingType === 'province') {
                // User mapped by province: show only mapped provinces
                $tQuery = $tQuery->join(array('fm' => 'spi_rt_3_facilities'), 'fm.id=spiv6.facility', array('province'))
                    ->where(['fm.province' => CommonService::inList($loginContainer->userMappedIds)]);
            } elseif ($loginContainer->userMappingType === 'district') {
                // User mapped by district: show only mapped districts
                $tQuery = $tQuery->join(array('fm' => 'spi_rt_3_facilities'), 'fm.id=spiv6.facility', array('district'))
                    ->where(['fm.district' => CommonService::inList($loginContainer->userMappedIds)]);
            }
        }
        if (isset($parameters['country']) && $parameters['country'] != '') {
            $tQuery = $tQuery->join(array('f1' => 'spi_rt_3_facilities'), 'f1.id=spiv6.facility', array('country'))
                ->where(['f1.country' => CommonService::inList($parameters['country'])]);
        }
        if (isset($parameters['province']) && $parameters['province'] != '') {
            $tQuery = $tQuery->join(array('f2' => 'spi_rt_3_facilities'), 'f2.id=spiv6.facility', array('province'))
                ->where(['f2.province' => CommonService::inList($parameters['province'])]);
        }
        if (isset($parameters['district']) && $parameters['district'] != '') {
            $tQuery = $tQuery->join(array('f3' => 'spi_rt_3_facilities'), 'f3.id=spiv6.facility', array('district'))
                ->where(['f3.district' => CommonService::inList($parameters['district'])]);
        }
        $tQueryStr = $sql->buildSqlString($tQuery); // Get the string of the Sql, instead of the Select-instance
        $iTotal = $this->db->executeQuery($tQueryStr)->rowCount();
        $output = array(
            "sEcho" => (int) $parameters['sEcho'],
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array(),
        );


        foreach ($rResult as $aRow) {
            $row = [];
            $row[] = CommonService::escapeHtml($aRow['facilityname']);
            $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
            $row[] = CommonService::escapeHtml($aRow['testingpointtype']);
            $row[] = CommonService::escapeHtml($aRow['PERSONAL_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['PHYSICAL_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['SAFETY_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['PRETEST_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['TEST_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['POST_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['EQA_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['RTRI_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['FINAL_AUDIT_SCORE']);
            $row[] = round((float) ($aRow['AUDIT_SCORE_PERCENTAGE']), 2);
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function fetchAllApprovedSubmissionLocationV6($params)
    {
        $loginContainer = new Container('credo');
        $where = ["spiv6.status = 'approved'"];
        $values = [];
        self::addRoundNumbers($params, $where, $values);
        self::addTokenRule($loginContainer, $where, $values);
        self::addDateRange($params, $where, $values);
        self::addAuditRound($params, $where, $values);
        if (isset($params['testPoint']) && trim($params['testPoint']) != '') {
            self::addEquals('spiv6.testingpointtype', $params['testPoint'], $where, $values);
            if (trim($params['testPoint']) != 'other') {
                self::addEquals('spiv6.testingpointname', $params['testPointName'] ?? '', $where, $values);
            } else {
                self::addEquals('spiv6.testingpointtype_other', $params['testPointName'] ?? '', $where, $values);
            }
        }
        self::addLevelAndAffiliation($params, $where, $values);
        [$mappingColumns, $mappingJoin] = self::addInlineMapping($loginContainer, $where, $values, 'fm');
        [$filterColumns, $filterJoin] = self::addFacilityFilters($params, true, $where, $values);
        self::addScoreLevel($params, $where);
        $rResult = $this->db->fetchAllAssociative(
            'SELECT spiv6.*' . $mappingColumns . $filterColumns . ' FROM spi_form_v_6 AS spiv6' . $mappingJoin . $filterJoin
            . ($where === [] ? '' : ' WHERE ' . implode(' AND ', $where)) . ' ORDER BY assesmentofaudit DESC',
            $values
        );


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
            $formId = base64_decode($params['formId']);
            // Without corrective action rows in the post, the column stays NULL.
            $summationData = null;
            if (isset($params['sectionNo'])) {
                $summationData = [];
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
                $fResult = $this->db->fetchAssociative(
                    'SELECT spirt3.id AS id FROM spi_rt_3_facilities AS spirt3 WHERE spirt3.facility_name = ?',
                    [$params['testingFacilityName']]
                );
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

            $result = $this->updateAudits($data, ['id' => $formId]);
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
        $where = [];
        $values = [];
        self::addTokenRule($loginContainer, $where, $values);
        [$columns, $join] = self::addInlineMapping($loginContainer, $where, $values);
        return $this->db->fetchAllAssociative(
            "SELECT spiv6.assesmentofaudit, spiv6.auditroundno, COUNT('auditroundno') AS rowCount" . $columns
            . ' FROM spi_form_v_6 AS spiv6' . $join
            . ($where === [] ? '' : ' WHERE ' . implode(' AND ', $where))
            . ' GROUP BY auditroundno ORDER BY auditroundno ASC',
            $values
        );
    }

    //get all faciltiy name
    public function fetchAllFacilityNames()
    {
        $loginContainer = new Container('credo');
        $where = [];
        $values = [];
        self::addTokenRule($loginContainer, $where, $values);
        [$columns, $joins] = self::addLocationScope([], $loginContainer, $where, $values);
        $uResult = $this->db->fetchAllAssociative(
            'SELECT DISTINCT facilityname AS facilityname' . $columns . ' FROM spi_form_v_6 AS spiv6' . $joins
            . ($where === [] ? '' : ' WHERE ' . implode(' AND ', $where))
            . ' ORDER BY spiv6.facilityname ASC',
            $values
        );

        $facilityWhere = "spirt3.status != 'deleted'";
        [$mapped, $mappedIds] = SpiRtFacilitiesTable::mappingCondition('spirt3');
        if ($mapped !== '') {
            $facilityWhere .= ' AND ' . $mapped;
        }
        $aResult = $this->db->fetchAllAssociative('SELECT spirt3.* FROM spi_rt_3_facilities AS spirt3 WHERE ' . $facilityWhere, $mappedIds);

        return array('uniqueName' => $uResult, 'allName' => $aResult);
    }

    public function fetchFacilitiesAudits($params)
    {
        $audits = [];
        $aResult = "";
        if (isset($params['facilityName']) && trim($params['facilityName']) != '') {
            $loginContainer = new Container('credo');
            $where = ['spiv6.facilityname = ?', "spiv6.status = 'approved'"];
            $values = [$params['facilityName']];
            self::addTokenRule($loginContainer, $where, $values);
            [, $joins] = self::addLocationScope([], $loginContainer, $where, $values);
            if (isset($params['showEmailAudits']) && $params['showEmailAudits'] != '') {
                if ($params['showEmailAudits'] == 'yes') {
                    // Audits whose report was emailed.
                    $where[] = "JSON_UNQUOTE(JSON_EXTRACT(spiv6.form_metadata, '$.audit_email_sent')) = 'yes'";
                } elseif ($params['showEmailAudits'] == 'no') {
                    // Audits whose report was not emailed, or has no record of it.
                    $where[] = "(JSON_UNQUOTE(JSON_EXTRACT(spiv6.form_metadata, '$.audit_email_sent')) != 'yes'
                            OR JSON_EXTRACT(spiv6.form_metadata, '$.audit_email_sent') IS NULL)";
                }
            }
            $audits = $this->db->fetchAllAssociative(
                'SELECT spiv6.id AS id, spiv6.assesmentofaudit AS assesmentofaudit, spiv6.form_metadata AS form_metadata FROM spi_form_v_6 AS spiv6'
                . $joins . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY id DESC',
                $values
            );

            $aResult = $this->fetchRowObject(
                'SELECT spiv3_fclt.facility_name AS facility_name, spiv3_fclt.email AS email FROM spi_rt_3_facilities AS spiv3_fclt WHERE spiv3_fclt.facility_name = ?',
                [$params['facilityName']]
            );

        }
        return array('audits' => $audits, 'facilityProfile' => $aResult);
    }

    public function getSpiV6PendingCount()
    {
        $loginContainer = new Container('credo');
        $where = ["spiv6.status = 'pending'"];
        $values = [];
        self::addTokenRule($loginContainer, $where, $values);
        return (int) $this->db->fetchOne('SELECT COUNT(*) FROM spi_form_v_6 AS spiv6 WHERE ' . implode(' AND ', $where), $values);
    }

    public function deleteAuditRowData($params)
    {
        $result = 0;
        $deleteId = trim((string) ($params['deleteId'] ?? ''));
        if ($deleteId !== '') {
            /* Read the audit before it goes, so the log can name what was deleted.
             * The id on its own is no help afterwards: it is shown in no column, and
             * once the row reads "deleted" nothing in the log says which site it
             * belonged to. */
            $before = $this->db->fetchAssociative(
                'SELECT facilityname, assesmentofaudit, auditroundno, status FROM spi_form_v_6 WHERE id = ?',
                [$deleteId]
            );

            $result = $this->updateAudits(['status' => 'deleted'], ['id' => $deleteId]);
            if ($result > 0) {
                $loginContainer = new Container('credo');
                $userName = $loginContainer->login ?? 'system';
                $eventTable = new EventLogTable($this->adapter);

                $detail = $userName . ' deleted SPI-RRT record #' . $deleteId;
                if ($before) {
                    $facility = trim((string) $before['facilityname']);
                    $detail .= ' - ' . ($facility !== '' ? $facility : '(no facility name)')
                        . ', ' . CommonService::humanReadableDateFormat($before['assesmentofaudit'])
                        . ', round ' . (trim((string) $before['auditroundno']) !== '' ? $before['auditroundno'] : '-')
                        . ', was ' . $before['status'];
                }

                $eventTable->addEventLog(
                    '',
                    'delete',
                    // action is varchar(255); trim rather than let MySQL cut it off
                    // mid-word, since a long facility name would push the rest out.
                    mb_substr($detail, 0, 255),
                    'SPI-RRT'
                );
            }
        }
        return $result;
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
        // An unrecognised source matches no columns rather than failing on them.
        $aColumns = [];
        $orderColumns = [];
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
                if ($parameters['bSortable_' . (int) $parameters['iSortCol_' . $i]] == "true" && isset($orderColumns[(int) $parameters['iSortCol_' . $i]])) {
                    $sOrder .= $orderColumns[(int) $parameters['iSortCol_' . $i]] . " " . ($parameters['sSortDir_' . $i]) . ", ";
                }
            }
            $sOrder = rtrim($sOrder, ", ");
        }

        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */

        $sWhere = CommonService::dataTablesSearch($aColumns, $parameters);

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
                ->columns(array('facilityid', 'facilityname', 'auditroundno', 'assesmentofaudit', 'client_tested_HIV_PM', 'testingpointtype', 'testingpointname', 'testingpointtype_other', 'level', 'affiliation', 'AUDIT_SCORE_PERCENTAGE' => new Expression('ROUND(AUDIT_SCORE_PERCENTAGE)')))
                ->where(array('spiv3.status' => 'approved'));
            $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array('facilityid', 'facilityname', 'auditroundno', 'assesmentofaudit', 'client_tested_HIV_PM', 'testingpointtype', 'testingpointname', 'testingpointtype_other', 'level', 'affiliation', 'AUDIT_SCORE_PERCENTAGE' => new Expression('ROUND(AUDIT_SCORE_PERCENTAGE)')))
                ->where(array('spiv3.status' => 'approved'));
            if ($parameters['source'] == 'apl180') {
                $sQuery = $sQuery->where("(`assesmentofaudit` BETWEEN CURDATE() - INTERVAL 180 DAY AND CURDATE())");
                $tQuery = $tQuery->where("(`assesmentofaudit` BETWEEN CURDATE() - INTERVAL 180 DAY AND CURDATE())");
            }
            if (isset($parameters['testPoint']) && trim($parameters['testPoint']) != '') {
                $sQuery = $sQuery->where(['spiv3.testingpointtype' => $parameters['testPoint']]);
                $tQuery = $tQuery->where(['spiv3.testingpointtype' => $parameters['testPoint']]);
                if (isset($parameters['testPointName']) && trim($parameters['testPointName']) != '') {
                    if (trim($parameters['testPoint']) != 'other') {
                        $sQuery = $sQuery->where(['spiv3.testingpointname' => $parameters['testPointName']]);
                        $tQuery = $tQuery->where(['spiv3.testingpointname' => $parameters['testPointName']]);
                    } else {
                        $sQuery = $sQuery->where(['spiv3.testingpointtype_other' => $parameters['testPointName']]);
                        $tQuery = $tQuery->where(['spiv3.testingpointtype_other' => $parameters['testPointName']]);
                    }
                }
            }
            if (isset($parameters['auditRndNo']) && $parameters['auditRndNo'] != '') {
                $sQuery = $sQuery->where(['spiv3.auditroundno' => $parameters['auditRndNo']]);
                $tQuery = $tQuery->where(['spiv3.auditroundno' => $parameters['auditRndNo']]);
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
                $sQuery = $sQuery->where(['spiv3.level' => $parameters['level']]);
                $tQuery = $tQuery->where(['spiv3.level' => $parameters['level']]);
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
                $sQuery = $sQuery->where(['spiv3.auditroundno' => CommonService::inList($parameters['roundno'])]);
                $tQuery = $tQuery->where(['spiv3.auditroundno' => CommonService::inList($parameters['roundno'])]);
            }
        } else {
            //For Others
            $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array('assesmentofaudit', 'facilityname', 'testingpointtype', 'client_tested_HIV_PM', 'NumberofTester', 'AUDIT_SCORE_PERCENTAGE'));

            $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array('assesmentofaudit', 'facilityname', 'testingpointtype', 'client_tested_HIV_PM', 'NumberofTester', 'AUDIT_SCORE_PERCENTAGE'));
        }

        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
            $tQuery = $tQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }

        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType != '') {
            // Mapping exists
            if ($loginContainer->userMappingType === 'country') {
                // User mapped by country: show mapped country data and all related provinces and districts
                $sQuery = $sQuery->join(array('fm' => 'spi_rt_3_facilities'), 'fm.id=spiv3.facility', array())
                    ->where(['fm.country' => CommonService::inList($loginContainer->userMappedIds)]);
                $tQuery = $tQuery->join(array('fm' => 'spi_rt_3_facilities'), 'fm.id=spiv3.facility', array())
                    ->where(['fm.country' => CommonService::inList($loginContainer->userMappedIds)]);
            } elseif ($loginContainer->userMappingType === 'province') {
                // User mapped by province: show only mapped provinces
                $sQuery = $sQuery->join(array('fm' => 'spi_rt_3_facilities'), 'fm.id=spiv3.facility', array())
                    ->where(['fm.province' => CommonService::inList($loginContainer->userMappedIds)]);
                $tQuery = $tQuery->join(array('fm' => 'spi_rt_3_facilities'), 'fm.id=spiv3.facility', array())
                    ->where(['fm.province' => CommonService::inList($loginContainer->userMappedIds)]);
            } elseif ($loginContainer->userMappingType === 'district') {
                // User mapped by district: show only mapped districts
                $sQuery = $sQuery->join(array('fm' => 'spi_rt_3_facilities'), 'fm.id=spiv3.facility', array())
                    ->where(['fm.district' => CommonService::inList($loginContainer->userMappedIds)]);
                $tQuery = $tQuery->join(array('fm' => 'spi_rt_3_facilities'), 'fm.id=spiv3.facility', array())
                    ->where(['fm.district' => CommonService::inList($loginContainer->userMappedIds)]);
            }
        }
        if ((isset($parameters['country']) && $parameters['country'] != '') || (isset($parameters['province']) && $parameters['province'] != '') || (isset($parameters['district']) && $parameters['district'] != '')) {
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array());
            $tQuery = $tQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array());

            // Apply filters dynamically
            if (isset($parameters['country']) && $parameters['country'] != '') {
                $sQuery = $sQuery->where(['f.country' => CommonService::inList($parameters['country'])]);
                $tQuery = $tQuery->where(['f.country' => CommonService::inList($parameters['country'])]);
            }
            if (isset($parameters['province']) && $parameters['province'] != '') {
                $sQuery = $sQuery->where(['f.province' => CommonService::inList($parameters['province'])]);
                $tQuery = $tQuery->where(['f.province' => CommonService::inList($parameters['province'])]);
            }
            if (isset($parameters['district']) && $parameters['district'] != '') {
                $sQuery = $sQuery->where(['f.district' => CommonService::inList($parameters['district'])]);
                $tQuery = $tQuery->where(['f.district' => CommonService::inList($parameters['district'])]);
            }
        }

        if (trim($startDate) != "" && trim($endDate) != "") {
            $sQuery = $sQuery->where(['spiv3.assesmentofaudit >= ?' => $startDate, 'spiv3.assesmentofaudit <= ?' => $endDate]);
            $tQuery = $tQuery->where(['spiv3.assesmentofaudit >= ?' => $startDate, 'spiv3.assesmentofaudit <= ?' => $endDate]);
        }

        if ($sWhere !== null) {
            $sQuery->where($sWhere);
        }

        /*
         * The figures above the table cover every filtered audit, not only
         * the page, so they come from their own query over the filtered set.
         * The score column is text: "+ 0" reads it as PHP's (float) did.
         */
        $isScored = in_array($parameters['source'], ['hv', 'la', 'apall', 'apl180', 'ap'], true);
        $summaryColumns = ['filtered' => new Expression('COUNT(*)')];
        if ($isScored) {
            $score = 'COALESCE(t.AUDIT_SCORE_PERCENTAGE + 0, 0)';
            $summaryColumns['scoreTotal'] = new Expression("SUM($score)");
            $summaryColumns['levelZero'] = new Expression("SUM($score < 40)");
            $summaryColumns['levelOne'] = new Expression("SUM($score >= 40 AND $score < 60)");
            $summaryColumns['levelTwo'] = new Expression("SUM($score >= 60 AND $score < 80)");
            $summaryColumns['levelThree'] = new Expression("SUM($score >= 80 AND $score < 90)");
            $summaryColumns['levelFour'] = new Expression("SUM($score >= 90)");
        }
        $summaryQuery = $sql->select()->from(['t' => $sQuery])->columns($summaryColumns);
        $summary = $this->db->fetchAssociative($sql->buildSqlString($summaryQuery));
        $iFilteredTotal = (int) $summary['filtered'];

        $totalQuery = $sql->select()->from(['t' => $tQuery])->columns(['total' => new Expression('COUNT(*)')]);
        $iTotal = (int) $this->db->fetchOne($sql->buildSqlString($totalQuery));

        if (trim($sOrder) === 'desc') {
            $sOrder = '';
        }
        if (isset($sOrder) && $sOrder != "") {
            $sQuery->order($sOrder);
        }
        // Rows that tie on the sort column come back in no fixed order, so a
        // page could repeat or skip one. The last sort key is unique per row:
        // the audit date for the sources grouped by it, the id for the rest.
        if ($parameters['source'] == 'ad' || $parameters['source'] == 'apspi') {
            $sQuery->order('spiv3.assesmentofaudit DESC');
        } else {
            $sQuery->order('spiv3.id DESC');
        }

        // The export reads every filtered audit, so it keeps the query without the page limit.
        $queryContainer->exportViewDataV6Query = clone $sQuery;

        if ($sLimit !== '') {
            $sQuery->limit((int) $sLimit);
            $sQuery->offset((int) $sOffset);
        }

        $sQueryStr = $sql->buildSqlString($sQuery);
        $rResult = $this->db->fetchAllAssociative($sQueryStr);

        $output = array(
            "sEcho" => (int) $parameters['sEcho'],
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array(),
        );

        foreach ($rResult as $aRow) {
            $row = [];
            $level = null;

            if ($isScored) {
                $scorePer = (float) $aRow['AUDIT_SCORE_PERCENTAGE'];
                if ($scorePer < 40) {
                    $level = 0;
                } elseif ($scorePer < 60) {
                    $level = 1;
                } elseif ($scorePer < 80) {
                    $level = 2;
                } elseif ($scorePer < 90) {
                    $level = 3;
                } else {
                    $level = 4;
                }
            }

            if ($parameters['source'] == 'hv') {
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = CommonService::escapeHtml(ucwords($aRow['facilityname']));
                $row[] = CommonService::escapeHtml(isset($aRow['testingpointname']) && $aRow['testingpointname'] != "" ? $aRow['testingpointname'] : $aRow['testingpointtype']);
                $row[] = CommonService::escapeHtml($aRow['client_tested_HIV_PM'] ?? 0);
                $row[] = CommonService::escapeHtml($aRow['NumberofTester'] ?? 0);
                $row[] = $level; //level
            } elseif ($parameters['source'] == 'la') {
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = CommonService::escapeHtml(ucwords($aRow['facilityname']));
                $row[] = CommonService::escapeHtml(isset($aRow['testingpointname']) && $aRow['testingpointname'] != "" ? $aRow['testingpointname'] : $aRow['testingpointtype']);
                $row[] = round((float) ($aRow['AUDIT_SCORE_PERCENTAGE'] ?? 0), 2);
                $row[] = CommonService::escapeHtml($aRow['client_tested_HIV_PM'] ?? 0);
            } elseif ($parameters['source'] == 'ad') {
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = CommonService::escapeHtml($aRow['totalDataPoints']);
            } elseif ($parameters['source'] == 'apall' || $parameters['source'] == 'apl180' || $parameters['source'] == 'ap') {
                $row[] = CommonService::escapeHtml($aRow['facilityid']);
                $row[] = CommonService::escapeHtml(ucwords($aRow['facilityname']));
                $row[] = CommonService::escapeHtml($aRow['auditroundno']);
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = CommonService::escapeHtml(ucwords($aRow['testingpointtype']));
                $row[] = CommonService::escapeHtml(($aRow['testingpointtype'] == 'other') ? ucwords((string) $aRow['testingpointtype_other']) : ucwords((string) $aRow['testingpointname']));
                $row[] = CommonService::escapeHtml(ucwords($aRow['level']));
                $row[] = CommonService::escapeHtml(ucwords($aRow['affiliation']));
                $row[] = $level; //level
                $row[] = round((float) ($aRow['AUDIT_SCORE_PERCENTAGE'] ?? 0), 2);
            } elseif ($parameters['source'] == 'apspi') {
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = round((float) ($aRow['PERSONAL_SCORE']), 2);
                $row[] = round((float) ($aRow['PHYSICAL_SCORE']), 2);
                $row[] = round((float) ($aRow['SAFETY_SCORE']), 2);
                $row[] = round((float) ($aRow['PRETEST_SCORE']), 2);
                $row[] = round((float) ($aRow['TEST_SCORE']), 2);
                $row[] = round((float) ($aRow['POST_SCORE']), 2);
                $row[] = round((float) ($aRow['EQA_SCORE']), 2);
            }
            // Add more data to the row as needed
            $output['aaData'][] = $row;
        }

        $output['avgAuditScore'] = ($iFilteredTotal > 0) ? round((float) ($summary['scoreTotal'] ?? 0) / $iFilteredTotal, 2) : 0;
        $output['levelZeroCount'] = (int) ($summary['levelZero'] ?? 0);
        $output['levelOneCount'] = (int) ($summary['levelOne'] ?? 0);
        $output['levelTwoCount'] = (int) ($summary['levelTwo'] ?? 0);
        $output['levelThreeCount'] = (int) ($summary['levelThree'] ?? 0);
        $output['levelFourCount'] = (int) ($summary['levelFour'] ?? 0);

        return $output;
    }

    public function fetchViewDataS0Details($parameters)
    {
        $loginContainer = new Container('credo');
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */
        // An unrecognised source matches no columns rather than failing on them.
        $aColumns = [];
        $orderColumns = [];
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
                if ($parameters['bSortable_' . (int) $parameters['iSortCol_' . $i]] == "true" && isset($orderColumns[(int) $parameters['iSortCol_' . $i]])) {
                    $sOrder .= $orderColumns[(int) $parameters['iSortCol_' . $i]] . " " . ($parameters['sSortDir_' . $i]) . ", ";
                }
            }
            $sOrder = rtrim($sOrder, ", ");
        }

        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */

        $sWhere = CommonService::dataTablesSearch($aColumns, $parameters);

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
                $sQuery = $sQuery->where(['spiv3.testingpointtype' => $parameters['testPoint']]);
                $tQuery = $tQuery->where(['spiv3.testingpointtype' => $parameters['testPoint']]);
                if (isset($parameters['testPointName']) && trim($parameters['testPointName']) != '') {
                    if (trim($parameters['testPoint']) != 'other') {
                        $sQuery = $sQuery->where(['spiv3.testingpointname' => $parameters['testPointName']]);
                        $tQuery = $tQuery->where(['spiv3.testingpointname' => $parameters['testPointName']]);
                    } else {
                        $sQuery = $sQuery->where(['spiv3.testingpointtype_other' => $parameters['testPointName']]);
                        $tQuery = $tQuery->where(['spiv3.testingpointtype_other' => $parameters['testPointName']]);
                    }
                }
            }
            if (isset($parameters['auditRndNo']) && $parameters['auditRndNo'] != '') {
                $sQuery = $sQuery->where(['spiv3.auditroundno' => $parameters['auditRndNo']]);
                $tQuery = $tQuery->where(['spiv3.auditroundno' => $parameters['auditRndNo']]);
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
                $sQuery = $sQuery->where(['spiv3.level' => $parameters['level']]);
                $tQuery = $tQuery->where(['spiv3.level' => $parameters['level']]);
            }
            if (isset($parameters['affiliation']) && $parameters['affiliation'] != '') {
                $sQuery = $sQuery->where(['spiv3.affiliation' => $parameters['affiliation']]);
                $tQuery = $tQuery->where(['spiv3.affiliation' => $parameters['affiliation']]);
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
                $sQuery = $sQuery->where(['spiv3.auditroundno' => CommonService::inList($parameters['roundno'])]);
                $tQuery = $tQuery->where(['spiv3.auditroundno' => CommonService::inList($parameters['roundno'])]);
            }
        } else {
            //For Others
            $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array('assesmentofaudit', 'facilityname', 'testingpointtype', 'NumberofTester', 'AUDIT_SCORE_PERCENTAGE'));

            $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array('assesmentofaudit', 'facilityname', 'testingpointtype', 'NumberofTester', 'AUDIT_SCORE_PERCENTAGE'));
        }

        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
            $tQuery = $tQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }

        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType != '') {
            // Mapping exists
            if ($loginContainer->userMappingType === 'country') {
                // User mapped by country: show mapped country data and all related provinces and districts
                $sQuery = $sQuery->join(array('fm' => 'spi_rt_3_facilities'), 'fm.id=spiv3.facility', array('country', 'province', 'district'))
                    ->where(['fm.country' => CommonService::inList($loginContainer->userMappedIds)]);
                $tQuery = $tQuery->join(array('fm' => 'spi_rt_3_facilities'), 'fm.id=spiv3.facility', array('country', 'province', 'district'))
                    ->where(['fm.country' => CommonService::inList($loginContainer->userMappedIds)]);
            } elseif ($loginContainer->userMappingType === 'province') {
                // User mapped by province: show only mapped provinces
                $sQuery = $sQuery->join(array('fm' => 'spi_rt_3_facilities'), 'fm.id=spiv3.facility', array('province'))
                    ->where(['fm.province' => CommonService::inList($loginContainer->userMappedIds)]);
                $tQuery = $tQuery->join(array('fm' => 'spi_rt_3_facilities'), 'fm.id=spiv3.facility', array('province'))
                    ->where(['fm.province' => CommonService::inList($loginContainer->userMappedIds)]);
            } elseif ($loginContainer->userMappingType === 'district') {
                // User mapped by district: show only mapped districts
                $sQuery = $sQuery->join(array('fm' => 'spi_rt_3_facilities'), 'fm.id=spiv3.facility', array('district'))
                    ->where(['fm.district' => CommonService::inList($loginContainer->userMappedIds)]);
                $tQuery = $tQuery->join(array('fm' => 'spi_rt_3_facilities'), 'fm.id=spiv3.facility', array('district'))
                    ->where(['fm.district' => CommonService::inList($loginContainer->userMappedIds)]);
            }
        }
        if ((isset($parameters['country']) && $parameters['country'] != '') || (isset($parameters['province']) && $parameters['province'] != '') || (isset($parameters['district']) && $parameters['district'] != '')) {
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('country', 'province', 'district'));
            $tQuery = $tQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('country', 'province', 'district'));

            if (isset($parameters['country']) && $parameters['country'] != '' && is_array($parameters['country'])) {
                $sQuery = $sQuery->where(['f.country' => CommonService::inList($parameters['country'])]);
                $tQuery = $tQuery->where(['f.country' => CommonService::inList($parameters['country'])]);
            }
            if (isset($parameters['province']) && $parameters['province'] != '' && is_array($parameters['province'])) {
                $sQuery = $sQuery->where(['f.province' => CommonService::inList($parameters['province'])]);
                $tQuery = $tQuery->where(['f.province' => CommonService::inList($parameters['province'])]);
            }
            if (isset($parameters['district']) && $parameters['district'] != '' && is_array($parameters['district'])) {
                $sQuery = $sQuery->where(['f.district' => CommonService::inList($parameters['district'])]);
                $tQuery = $tQuery->where(['f.district' => CommonService::inList($parameters['district'])]);
            }
        }

        if (trim($startDate) != "" && trim($endDate) != "") {
            $sQuery = $sQuery->where(['spiv3.assesmentofaudit >= ?' => $startDate, 'spiv3.assesmentofaudit <= ?' => $endDate]);
            $tQuery = $tQuery->where(['spiv3.assesmentofaudit >= ?' => $startDate, 'spiv3.assesmentofaudit <= ?' => $endDate]);
        }

        if ($sWhere !== null) {
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
        $rResult = $this->db->fetchAllAssociative($sQueryStr);
        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->buildSqlString($sQuery);
        $iFilteredTotal = $this->db->executeQuery($fQuery)->rowCount();

        /* Total data set length */

        $tQueryStr = $sql->buildSqlString($tQuery); // Get the string of the Sql, instead of the Select-instance
        $iTotal = $this->db->executeQuery($tQueryStr)->rowCount();
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
            $level = null;
            if ($parameters['source'] == 'hv' || $parameters['source'] == 'la' || $parameters['source'] == 'apall' || $parameters['source'] == 'apl180' || $parameters['source'] == 'ap') {
                $auditScore += (float) $aRow['AUDIT_SCORE_PERCENTAGE'];
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
                $row[] = CommonService::escapeHtml(ucwords($aRow['facilityname']));
                $row[] = CommonService::escapeHtml(isset($aRow['testingpointname']) && $aRow['testingpointname'] != "" ? $aRow['testingpointname'] : $aRow['testingpointtype']);
                $row[] = CommonService::escapeHtml($aRow['client_tested_HIV_PM'] ?? 0);
                $row[] = CommonService::escapeHtml($aRow['NumberofTester'] ?? 0);
                $row[] = $level;
            } elseif ($parameters['source'] == 'la') {
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = CommonService::escapeHtml(ucwords($aRow['facilityname']));
                $row[] = CommonService::escapeHtml(isset($aRow['testingpointname']) && $aRow['testingpointname'] != "" ? $aRow['testingpointname'] : $aRow['testingpointtype']);
                $row[] = round((float) ($aRow['AUDIT_SCORE_PERCENTAGE']), 2);
                $row[] = CommonService::escapeHtml($aRow['client_tested_HIV_PM'] ?? 0);
            } elseif ($parameters['source'] == 'ad') {
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = CommonService::escapeHtml($aRow['totalDataPoints']);
            } elseif ($parameters['source'] == 'apall' || $parameters['source'] == 'apl180' || $parameters['source'] == 'ap') {
                $row[] = CommonService::escapeHtml($aRow['facilityid']);
                $row[] = CommonService::escapeHtml(ucwords($aRow['facilityname']));
                $row[] = CommonService::escapeHtml($aRow['auditroundno']);
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = CommonService::escapeHtml(ucwords($aRow['testingpointtype']));
                $row[] = CommonService::escapeHtml(($aRow['testingpointtype'] == 'other') ? ucwords($aRow['testingpointtype_other']) : ucwords($aRow['testingpointname']));
                $row[] = CommonService::escapeHtml(ucwords($aRow['level']));
                $row[] = CommonService::escapeHtml(ucwords($aRow['affiliation']));
                $row[] = $level;
                $row[] = round((float) ($aRow['AUDIT_SCORE_PERCENTAGE']), 2);
            } elseif ($parameters['source'] == 'apspi') {
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = CommonService::escapeHtml($aRow['S0_Q_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY']);
                $row[] = CommonService::escapeHtml($aRow['S0_Q_2_COUNSELORS_FOLLOWING_PROTOCOL']);
                $row[] = CommonService::escapeHtml($aRow['S0_Q_3_TESTS_RECORDED_RECENCY']);
                $row[] = CommonService::escapeHtml($aRow['S0_Q_4_PROCESS_DOCUMENTED']);
                $row[] = CommonService::escapeHtml($aRow['S0_Q_5_RESULTS_RETURNED_IN_TWO_WEEKS']);
                $row[] = CommonService::escapeHtml($aRow['S0_Q_6_PROTOCOL_VIOLATION_DOCUMENTED']);
                $row[] = CommonService::escapeHtml($aRow['S0_Q_7_DOCUMENTING_PROTOCOL_ERRORS']);
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
        // An unrecognised source matches no columns rather than failing on them.
        $aColumns = [];
        $orderColumns = [];
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
                if ($parameters['bSortable_' . (int) $parameters['iSortCol_' . $i]] == "true" && isset($orderColumns[(int) $parameters['iSortCol_' . $i]])) {
                    $sOrder .= $orderColumns[(int) $parameters['iSortCol_' . $i]] . " " . ($parameters['sSortDir_' . $i]) . ", ";
                }
            }
            $sOrder = rtrim($sOrder, ", ");
        }

        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */

        $sWhere = CommonService::dataTablesSearch($aColumns, $parameters);

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
                $sQuery = $sQuery->where(['spiv3.testingpointtype' => $parameters['testPoint']]);
                $tQuery = $tQuery->where(['spiv3.testingpointtype' => $parameters['testPoint']]);
                if (isset($parameters['testPointName']) && trim($parameters['testPointName']) != '') {
                    if (trim($parameters['testPoint']) != 'other') {
                        $sQuery = $sQuery->where(['spiv3.testingpointname' => $parameters['testPointName']]);
                        $tQuery = $tQuery->where(['spiv3.testingpointname' => $parameters['testPointName']]);
                    } else {
                        $sQuery = $sQuery->where(['spiv3.testingpointtype_other' => $parameters['testPointName']]);
                        $tQuery = $tQuery->where(['spiv3.testingpointtype_other' => $parameters['testPointName']]);
                    }
                }
            }
            if (isset($parameters['auditRndNo']) && $parameters['auditRndNo'] != '') {
                $sQuery = $sQuery->where(['spiv3.auditroundno' => $parameters['auditRndNo']]);
                $tQuery = $tQuery->where(['spiv3.auditroundno' => $parameters['auditRndNo']]);
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
                $sQuery = $sQuery->where(['spiv3.level' => $parameters['level']]);
                $tQuery = $tQuery->where(['spiv3.level' => $parameters['level']]);
            }
            if (isset($parameters['affiliation']) && $parameters['affiliation'] != '') {
                $sQuery = $sQuery->where(['spiv3.affiliation' => $parameters['affiliation']]);
                $tQuery = $tQuery->where(['spiv3.affiliation' => $parameters['affiliation']]);
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
                $sQuery = $sQuery->where(['spiv3.auditroundno' => CommonService::inList($parameters['roundno'])]);
                $tQuery = $tQuery->where(['spiv3.auditroundno' => CommonService::inList($parameters['roundno'])]);
            }
        } else {
            //For Others
            $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array('assesmentofaudit', 'facilityname', 'testingpointtype', 'NumberofTester', 'AUDIT_SCORE_PERCENTAGE'));

            $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_6'))
                ->columns(array('assesmentofaudit', 'facilityname', 'testingpointtype', 'NumberofTester', 'AUDIT_SCORE_PERCENTAGE'));
        }

        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
            $tQuery = $tQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }

        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType != '') {
            // Mapping exists
            if ($loginContainer->userMappingType === 'country') {
                // User mapped by country: show mapped country data and all related provinces and districts
                $sQuery = $sQuery->join(array('fm' => 'spi_rt_3_facilities'), 'fm.id=spiv3.facility', array('country', 'province', 'district'))
                    ->where(['fm.country' => CommonService::inList($loginContainer->userMappedIds)]);
                $tQuery = $tQuery->join(array('fm' => 'spi_rt_3_facilities'), 'fm.id=spiv3.facility', array('country', 'province', 'district'))
                    ->where(['fm.country' => CommonService::inList($loginContainer->userMappedIds)]);
            } elseif ($loginContainer->userMappingType === 'province') {
                // User mapped by province: show only mapped provinces
                $sQuery = $sQuery->join(array('fm' => 'spi_rt_3_facilities'), 'fm.id=spiv3.facility', array('province'))
                    ->where(['fm.province' => CommonService::inList($loginContainer->userMappedIds)]);
                $tQuery = $tQuery->join(array('fm' => 'spi_rt_3_facilities'), 'fm.id=spiv3.facility', array('province'))
                    ->where(['fm.province' => CommonService::inList($loginContainer->userMappedIds)]);
            } elseif ($loginContainer->userMappingType === 'district') {
                // User mapped by district: show only mapped districts
                $sQuery = $sQuery->join(array('fm' => 'spi_rt_3_facilities'), 'fm.id=spiv3.facility', array('district'))
                    ->where(['fm.district' => CommonService::inList($loginContainer->userMappedIds)]);
                $tQuery = $tQuery->join(array('fm' => 'spi_rt_3_facilities'), 'fm.id=spiv3.facility', array('district'))
                    ->where(['fm.district' => CommonService::inList($loginContainer->userMappedIds)]);
            }
        }
        if ((isset($parameters['country']) && $parameters['country'] != '') || (isset($parameters['province']) && $parameters['province'] != '') || (isset($parameters['district']) && $parameters['district'] != '')) {
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('country', 'province', 'district'));
            $tQuery = $tQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('country', 'province', 'district'));

            if (isset($parameters['country']) && $parameters['country'] != '' && is_array($parameters['country'])) {
                $sQuery = $sQuery->where(['f.country' => CommonService::inList($parameters['country'])]);
                $tQuery = $tQuery->where(['f.country' => CommonService::inList($parameters['country'])]);
            }
            if (isset($parameters['province']) && $parameters['province'] != '' && is_array($parameters['province'])) {
                $sQuery = $sQuery->where(['f.province' => CommonService::inList($parameters['province'])]);
                $tQuery = $tQuery->where(['f.province' => CommonService::inList($parameters['province'])]);
            }
            if (isset($parameters['district']) && $parameters['district'] != '' && is_array($parameters['district'])) {
                $sQuery = $sQuery->where(['f.district' => CommonService::inList($parameters['district'])]);
                $tQuery = $tQuery->where(['f.district' => CommonService::inList($parameters['district'])]);
            }
        }

        if (trim($startDate) != "" && trim($endDate) != "") {
            $sQuery = $sQuery->where(['spiv3.assesmentofaudit >= ?' => $startDate, 'spiv3.assesmentofaudit <= ?' => $endDate]);
            $tQuery = $tQuery->where(['spiv3.assesmentofaudit >= ?' => $startDate, 'spiv3.assesmentofaudit <= ?' => $endDate]);
        }

        if ($sWhere !== null) {
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
        $rResult = $this->db->fetchAllAssociative($sQueryStr);
        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->buildSqlString($sQuery);
        $iFilteredTotal = $this->db->executeQuery($fQuery)->rowCount();

        /* Total data set length */

        $tQueryStr = $sql->buildSqlString($tQuery); // Get the string of the Sql, instead of the Select-instance
        $iTotal = $this->db->executeQuery($tQueryStr)->rowCount();
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
            $level = null;
            if ($parameters['source'] == 'hv' || $parameters['source'] == 'la' || $parameters['source'] == 'apall' || $parameters['source'] == 'apl180' || $parameters['source'] == 'ap') {
                $auditScore += (float) $aRow['AUDIT_SCORE_PERCENTAGE'];
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
                $row[] = CommonService::escapeHtml(ucwords($aRow['facilityname']));
                $row[] = CommonService::escapeHtml(isset($aRow['testingpointname']) && $aRow['testingpointname'] != "" ? $aRow['testingpointname'] : $aRow['testingpointtype']);
                $row[] = CommonService::escapeHtml($aRow['client_tested_HIV_PM'] ?? 0);
                $row[] = CommonService::escapeHtml($aRow['NumberofTester'] ?? 0);
                $row[] = $level;
            } elseif ($parameters['source'] == 'la') {
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = CommonService::escapeHtml(ucwords($aRow['facilityname']));
                $row[] = CommonService::escapeHtml(isset($aRow['testingpointname']) && $aRow['testingpointname'] != "" ? $aRow['testingpointname'] : $aRow['testingpointtype']);
                $row[] = round((float) ($aRow['AUDIT_SCORE_PERCENTAGE']), 2);
                $row[] = CommonService::escapeHtml($aRow['client_tested_HIV_PM'] ?? 0);
            } elseif ($parameters['source'] == 'ad') {
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = CommonService::escapeHtml($aRow['totalDataPoints']);
            } elseif ($parameters['source'] == 'apall' || $parameters['source'] == 'apl180' || $parameters['source'] == 'ap') {
                $row[] = CommonService::escapeHtml($aRow['facilityid']);
                $row[] = CommonService::escapeHtml(ucwords($aRow['facilityname']));
                $row[] = CommonService::escapeHtml($aRow['auditroundno']);
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = CommonService::escapeHtml(ucwords($aRow['testingpointtype']));
                $row[] = CommonService::escapeHtml(($aRow['testingpointtype'] == 'other') ? ucwords($aRow['testingpointtype_other']) : ucwords($aRow['testingpointname']));
                $row[] = CommonService::escapeHtml(ucwords($aRow['level']));
                $row[] = CommonService::escapeHtml(ucwords($aRow['affiliation']));
                $row[] = $level;
                $row[] = round((float) ($aRow['AUDIT_SCORE_PERCENTAGE']), 2);
            } elseif ($parameters['source'] == 'apspi') {
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = CommonService::escapeHtml($aRow['D0_S_1_DIAGNOSED_HIV_ABOVE_15']);
                $row[] = CommonService::escapeHtml($aRow['D0_S_2_CANDIDATE_SCREENED_FOR_PARTICIPATION']);
                $row[] = CommonService::escapeHtml($aRow['D0_S_3_ELIGIBLE_DURING_REVIEW_PERIOD']);
                $row[] = CommonService::escapeHtml($aRow['D0_S_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD']);
                $row[] = CommonService::escapeHtml($aRow['D0_S_5_DOCUMENTED_AND_REFUSED']);
                $row[] = CommonService::escapeHtml($aRow['D0_S_6_PARTICIAPANTS_ENROLLED_IN_RTRI']);
                $row[] = CommonService::escapeHtml($aRow['D0_S_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI']);
                $row[] = CommonService::escapeHtml($aRow['D0_S_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI']);
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
        return $this->db->fetchAllAssociative(
            'SELECT DISTINCT(testingpointtype) AS testingPointType FROM spi_form_v_6 GROUP BY testingpointtype ORDER BY testingpointtype ASC'
        );
    }

    public function fetchTestingPointTypeNamesByTypeV6($params)
    {
        if (!isset($params['testingPointType']) || trim($params['testingPointType']) == '') {
            return [];
        }
        if ($params['testingPointType'] == 'other') {
            return $this->db->fetchAllAssociative(
                'SELECT DISTINCT(testingpointtype_other) AS testingpointName FROM spi_form_v_6 WHERE testingpointtype_other IS NOT NULL'
            );
        }
        return $this->db->fetchAllAssociative(
            'SELECT DISTINCT(testingpointtype) AS testingpointName FROM spi_form_v_6 WHERE testingpointtype = ?',
            [$params['testingPointType']]
        );
    }

    public function fetchSpiV6FormUniqueLevelNames()
    {
        return $this->db->fetchAllAssociative(
            'SELECT spiv6.facilityname AS facilityname, f.* FROM spi_form_v_6 AS spiv6 INNER JOIN spi_rt_3_facilities AS f ON f.id = spiv6.facility'
        );
    }

    public function getLatestFormDate($projectId, $formId)
    {
        $query = "SELECT MAX(`meta-submission-date`) as last_added_form_date FROM spi_form_v_6 WHERE central_project_id = ? AND central_form_id = ?";
        return $this->db->fetchAllAssociative($query, [$projectId, $formId]);
    }

    public function fetchV6DetailsByMetaInstanceId($metaInstantId)
    {
        return Dbal::connection($this->adapter)->fetchAssociative(
            'SELECT * FROM spi_form_v_6 WHERE `meta-instance-id` = ?',
            [$metaInstantId],
        );
    }

    public function updateSpiv6FacilityInfo($id, $params)
    {
        if ($id > 0) {
            $data = array(
                'facilityid' => $params['facilityId'],
                'facilityname' => $params['facilityName'],
            );
            $this->updateAudits($data, ['facility' => $id]);
        }
        return $id;
    }

    public function fetchSpiV6FormUniqueLevels()
    {
        return $this->db->fetchAllAssociative("SELECT DISTINCT level AS level FROM spi_form_v_6 WHERE level != '' ORDER BY level ASC");
    }
}

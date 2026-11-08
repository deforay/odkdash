<?php

namespace Application\Model;

use App\Db\Dbal;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Expression;
use Application\Session\Container;
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
class SpiFormVer3Table
{
    private const string TABLE = 'spi_form_v_3';

    /**
     * Limits an audit query, aliased spiv3, to the locations the user is
     * mapped to. Every query restricted by the user's tokens gets this too.
     *
     * An audit whose location cannot be resolved belongs to no location, so a
     * mapped user does not see it. A Super Admin has no mapping and sees it
     * (UsersTable::userEventLog()), as in V6's applyV6LocationScope().
     */
    private static function applyV3LocationScope(Select $query): Select
    {
        $loginContainer = new Container('credo');
        if (empty($loginContainer->userMappedIds) || !is_array($loginContainer->userMappedIds) || $loginContainer->userMappingType == '') {
            return $query;
        }
        $column = match ($loginContainer->userMappingType) {
            'country' => 'fm.country',
            'province' => 'fm.province',
            'district' => 'fm.district',
            default => null,
        };
        if ($column === null) {
            // A mapping type this code does not know shows nothing rather than everything.
            return $query->where('1 = 0');
        }
        return $query->join(['fm' => 'spi_rt_3_facilities'], 'fm.id=spiv3.facility', [])
            ->where([$column => CommonService::inList($loginContainer->userMappedIds)]);
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
        $hasTokens = !empty($loginContainer->token);
        $sql = new Sql($this->adapter);
        $query = $sql->select()->from(['spiv3' => 'spi_form_v_3'])->columns(['id'])
            ->where(['spiv3.id' => trim((string) $id)]);
        if ($hasTokens) {
            $query->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }
        $query = self::applyV3LocationScope($query);
        return $this->db->fetchOne($sql->buildSqlString($query)) !== false;
    }

    private readonly \Doctrine\DBAL\Connection $db;

    public function __construct(private readonly Adapter $adapter)
    {
        $this->db = Dbal::connection($adapter);
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

    /**
     * One row as Laminas returned it: an object whose columns also read as
     * properties, or null. Templates read these rows with `->`.
     */
    private function fetchRowObject(string $sql): ?\ArrayObject
    {
        $row = $this->db->fetchAssociative($sql);
        return $row === false ? null : new \ArrayObject($row, \ArrayObject::ARRAY_AS_PROPS);
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

        //error_log(print_r($params,true));

        foreach ($params as $submissionData) {
            $par = [];
            $data = [];

            //$data['token'] = $formDetails["enketoId"];

            $data['content'] = 'record';
            $data['formId'] = $formId;
            $data['formVersion'] = $submissionData['__system']["formVersion"];
            $data['meta-instance-id'] = $submissionData['meta']['instanceID'];
            $data['meta-model-version'] = '';
            $data['meta-ui-version'] = '';
            $data['meta-submission-date'] = $submissionData['__system']["submissionDate"];
            $data['meta-is-complete'] = '1';
            $data['meta-date-marked-as-complete'] =  explode("T", $submissionData["end"])[0];

            $data['start'] = $submissionData['start'];
            $data['end'] = $submissionData['end'];
            $data['today'] = $submissionData['today'];
            $data['deviceid'] = $submissionData['deviceid'];
            $data['subscriberid'] = $submissionData['subscriberid'];
            $data['text_image'] = $submissionData['text_image'];
            $data['info1'] = $submissionData['info1'];
            $data['info2'] = $submissionData['TESTSITE']['FACILITY']['info2'];
            $data['assesmentofaudit'] = $submissionData['TESTSITE']['assesmentofaudit'];
            $data['auditroundno'] = $submissionData['TESTSITE']['auditroundno'];
            $data['facilityname'] = $submissionData['TESTSITE']['facilityname'];
            $data['facilityid'] = $submissionData['TESTSITE']['facilityid'];
            $data['district'] = $submissionData['TESTSITE']['district'] ?? null;
            $data['testingpointname'] = $submissionData['TESTSITE']['testingpointname'];
            $data['testingpointtype'] = $submissionData['TESTSITE']['testingpointtype'];
            $data['testingpointtype_other'] = $submissionData['TESTSITE']['testingpointtype_other'];
            $data['locationaddress'] = $submissionData['TESTSITE']['locationaddress'];
            $data['level'] = $submissionData['TESTSITE']['level'];
            $data['level_other'] = $submissionData['TESTSITE']['level_other'];
            $data['level_name'] = $submissionData['TESTSITE']['level_name'];
            $data['affiliation'] = $submissionData['TESTSITE']['affiliation'];
            $data['affiliation_other'] = $submissionData['TESTSITE']['affiliation_other'];
            $data['NumberofTester'] = (isset($submissionData['TESTSITE']['NumberofTester']) && $submissionData['TESTSITE']['NumberofTester'] > 0 ? $submissionData['TESTSITE']['NumberofTester'] : 0);
            $data['avgMonthTesting'] = (isset($submissionData['TESTSITE']['avgMonthTesting']) && $submissionData['TESTSITE']['avgMonthTesting'] > 0 ? $submissionData['TESTSITE']['avgMonthTesting'] : 0);
            $data['name_auditor_lead'] = $submissionData['TESTSITE']['name_auditor_lead'];
            $data['name_auditor2'] = $submissionData['TESTSITE']['name_auditor2'];
            $data['info4'] = $submissionData['SPIRT']['info4'];
            $data['INSTANCE'] = $submissionData['INSTANCE'];
            $data['PERSONAL_Q_1_1'] = $submissionData['SPIRT']['PER_G_1_1']['PERSONAL_Q_1_1'];
            $data['PERSONAL_C_1_1'] = $submissionData['SPIRT']['PER_G_1_1']['PERSONAL_C_1_1'];
            $data['PERSONAL_Q_1_2'] = $submissionData['SPIRT']['PER_G_1_2']['PERSONAL_Q_1_2'];
            $data['PERSONAL_C_1_2'] = $submissionData['SPIRT']['PER_G_1_2']['PERSONAL_C_1_2'];
            $data['PERSONAL_Q_1_3'] = $submissionData['SPIRT']['PER_G_1_3']['PERSONAL_Q_1_3'];
            $data['PERSONAL_C_1_3'] = $submissionData['SPIRT']['PER_G_1_3']['PERSONAL_C_1_3'];
            $data['PERSONAL_Q_1_4'] = $submissionData['SPIRT']['PER_G_1_4']['PERSONAL_Q_1_4'];
            $data['PERSONAL_C_1_4'] = $submissionData['SPIRT']['PER_G_1_4']['PERSONAL_C_1_4'];
            $data['PERSONAL_Q_1_5'] = $submissionData['SPIRT']['PER_G_1_5']['PERSONAL_Q_1_5'];
            $data['PERSONAL_C_1_5'] = $submissionData['SPIRT']['PER_G_1_5']['PERSONAL_C_1_5'];
            $data['PERSONAL_Q_1_6'] = $submissionData['SPIRT']['PER_G_1_6']['PERSONAL_Q_1_6'];
            $data['PERSONAL_C_1_6'] = $submissionData['SPIRT']['PER_G_1_6']['PERSONAL_C_1_6'];
            $data['PERSONAL_Q_1_7'] = $submissionData['SPIRT']['PER_G_1_7']['PERSONAL_Q_1_7'];
            $data['PERSONAL_C_1_7'] = $submissionData['SPIRT']['PER_G_1_7']['PERSONAL_C_1_7'];
            $data['PERSONAL_Q_1_8'] = $submissionData['SPIRT']['PER_G_1_8']['PERSONAL_Q_1_8'];
            $data['PERSONAL_C_1_8'] = $submissionData['SPIRT']['PER_G_1_8']['PERSONAL_C_1_8'];
            $data['PERSONAL_Q_1_9'] = $submissionData['SPIRT']['PER_G_1_9']['PERSONAL_Q_1_9'];
            $data['PERSONAL_C_1_9'] = $submissionData['SPIRT']['PER_G_1_9']['PERSONAL_C_1_9'];
            $data['PERSONAL_Q_1_10'] = $submissionData['SPIRT']['PER_G_1_10']['PERSONAL_Q_1_10'];
            $data['PERSONAL_C_1_10'] = $submissionData['SPIRT']['PER_G_1_10']['PERSONAL_C_1_10'];
            $data['PERSONAL_SCORE'] = $submissionData['SPIRT']['PERSONAL_SCORE'];
            $data['PERSONAL_Display'] = $submissionData['SPIRT']['PERSONAL_Display'];
            $data['PERSONALPHOTO'] = $submissionData['SPIRT']['PERSONALPHOTO'];
            $data['PHYSICAL_Q_2_1'] = $submissionData['PHYSICAL']['PHY_G_2_1']['PHYSICAL_Q_2_1'];
            $data['PHYSICAL_C_2_1'] = $submissionData['PHYSICAL']['PHY_G_2_1']['PHYSICAL_C_2_1'];
            $data['PHYSICAL_Q_2_2'] = $submissionData['PHYSICAL']['PHY_G_2_2']['PHYSICAL_Q_2_2'];
            $data['PHYSICAL_C_2_2'] = $submissionData['PHYSICAL']['PHY_G_2_2']['PHYSICAL_C_2_2'];
            $data['PHYSICAL_Q_2_3'] = $submissionData['PHYSICAL']['PHY_G_2_3']['PHYSICAL_Q_2_3'];
            $data['PHYSICAL_C_2_3'] = $submissionData['PHYSICAL']['PHY_G_2_3']['PHYSICAL_C_2_3'];
            $data['PHYSICAL_Q_2_4'] = $submissionData['PHYSICAL']['PHY_G_2_4']['PHYSICAL_Q_2_4'];
            $data['PHYSICAL_C_2_4'] = $submissionData['PHYSICAL']['PHY_G_2_4']['PHYSICAL_C_2_4'];
            $data['PHYSICAL_Q_2_5'] = $submissionData['PHYSICAL']['PHY_G_2_5']['PHYSICAL_Q_2_5'];
            $data['PHYSICAL_C_2_5'] = $submissionData['PHYSICAL']['PHY_G_2_5']['PHYSICAL_C_2_5'];
            $data['PHYSICAL_SCORE'] = $submissionData['PHYSICAL']['PHYSICAL_SCORE'];
            $data['PHYSICAL_Display'] = $submissionData['PHYSICAL']['PHYSICAL_Display'];
            $data['PHYSICALPHOTO'] = $submissionData['PHYSICAL']['PHYSICALPHOTO'];
            $data['SAFETY_Q_3_1'] = $submissionData['SAFETY']['SAF_3_1']['SAFETY_Q_3_1'];
            $data['SAFETY_C_3_1'] = $submissionData['SAFETY']['SAF_3_1']['SAFETY_C_3_1'];
            $data['SAFETY_Q_3_2'] = $submissionData['SAFETY']['SAF_3_2']['SAFETY_Q_3_2'];
            $data['SAFETY_C_3_2'] = $submissionData['SAFETY']['SAF_3_2']['SAFETY_C_3_2'];
            $data['SAFETY_Q_3_3'] = $submissionData['SAFETY']['SAF_3_3']['SAFETY_Q_3_3'];
            $data['SAFETY_C_3_3'] = $submissionData['SAFETY']['SAF_3_3']['SAFETY_C_3_3'];
            $data['SAFETY_Q_3_4'] = $submissionData['SAFETY']['SAF_3_4']['SAFETY_Q_3_4'];
            $data['SAFETY_C_3_4'] = $submissionData['SAFETY']['SAF_3_4']['SAFETY_C_3_4'];
            $data['SAFETY_Q_3_5'] = $submissionData['SAFETY']['SAF_3_5']['SAFETY_Q_3_5'];
            $data['SAFETY_C_3_5'] = $submissionData['SAFETY']['SAF_3_5']['SAFETY_C_3_5'];
            $data['SAFETY_Q_3_6'] = $submissionData['SAFETY']['SAF_3_6']['SAFETY_Q_3_6'];
            $data['SAFETY_C_3_6'] = $submissionData['SAFETY']['SAF_3_6']['SAFETY_C_3_6'];
            $data['SAFETY_Q_3_7'] = $submissionData['SAFETY']['SAF_3_7']['SAFETY_Q_3_7'];
            $data['SAFETY_C_3_7'] = $submissionData['SAFETY']['SAF_3_7']['SAFETY_C_3_7'];
            $data['SAFETY_Q_3_8'] = $submissionData['SAFETY']['SAF_3_8']['SAFETY_Q_3_8'];
            $data['SAFETY_C_3_8'] = $submissionData['SAFETY']['SAF_3_8']['SAFETY_C_3_8'];
            $data['SAFETY_Q_3_9'] = $submissionData['SAFETY']['SAF_3_9']['SAFETY_Q_3_9'];
            $data['SAFETY_C_3_9'] = $submissionData['SAFETY']['SAF_3_9']['SAFETY_C_3_9'];
            $data['SAFETY_Q_3_10'] = $submissionData['SAFETY']['SAF_3_10']['SAFETY_Q_3_10'];
            $data['SAFETY_C_3_10'] = $submissionData['SAFETY']['SAF_3_10']['SAFETY_C_3_10'];
            $data['SAFETY_Q_3_11'] = $submissionData['SAFETY']['SAF_3_11']['SAFETY_Q_3_11'];
            $data['SAFETY_C_3_11'] = $submissionData['SAFETY']['SAF_3_11']['SAFETY_C_3_11'];
            $data['SAFETY_SCORE'] = $submissionData['SAFETY']['SAFETY_SCORE'];
            $data['SAFETY_DISPLAY'] = $submissionData['SAFETY']['SAFETY_DISPLAY'];
            $data['SAFETYPHOTO'] = $submissionData['SAFETY']['SAFETYPHOTO'];
            $data['PRE_Q_4_1'] = $submissionData['PRETEST']['PRE_4_1']['PRE_Q_4_1'];
            $data['PRE_C_4_1'] = $submissionData['PRETEST']['PRE_4_1']['PRE_C_4_1'];
            $data['PRE_Q_4_2'] = $submissionData['PRETEST']['PRE_4_2']['PRE_Q_4_2'];
            $data['PRE_C_4_2'] = $submissionData['PRETEST']['PRE_4_2']['PRE_C_4_2'];
            $data['PRE_Q_4_3'] = $submissionData['PRETEST']['PRE_4_3']['PRE_Q_4_3'];
            $data['PRE_C_4_3'] = $submissionData['PRETEST']['PRE_4_3']['PRE_C_4_3'];
            $data['PRE_Q_4_4'] = $submissionData['PRETEST']['PRE_4_4']['PRE_Q_4_4'];
            $data['PRE_C_4_4'] = $submissionData['PRETEST']['PRE_4_4']['PRE_C_4_4'];
            $data['PRE_Q_4_5'] = $submissionData['PRETEST']['PRE_4_5']['PRE_Q_4_5'];
            $data['PRE_C_4_5'] = $submissionData['PRETEST']['PRE_4_5']['PRE_C_4_5'];
            $data['PRE_Q_4_6'] = $submissionData['PRETEST']['PRE_4_6']['PRE_Q_4_6'];
            $data['PRE_C_4_6'] = $submissionData['PRETEST']['PRE_4_6']['PRE_C_4_6'];
            $data['PRE_Q_4_7'] = $submissionData['PRETEST']['PRE_4_7']['PRE_Q_4_7'];
            $data['PRE_C_4_7'] = $submissionData['PRETEST']['PRE_4_7']['PRE_C_4_7'];
            $data['PRE_Q_4_8'] = $submissionData['PRETEST']['PRE_4_8']['PRE_Q_4_8'];
            $data['PRE_C_4_8'] = $submissionData['PRETEST']['PRE_4_8']['PRE_C_4_8'];
            $data['PRE_Q_4_9'] = $submissionData['PRETEST']['PRE_4_9']['PRE_Q_4_9'];
            $data['PRE_C_4_9'] = $submissionData['PRETEST']['PRE_4_9']['PRE_C_4_9'];
            $data['PRE_Q_4_10'] = $submissionData['PRETEST']['PRE_4_10']['PRE_Q_4_10'];
            $data['PRE_C_4_10'] = $submissionData['PRETEST']['PRE_4_10']['PRE_C_4_10'];
            $data['PRE_Q_4_11'] = $submissionData['PRETEST']['PRE_4_11']['PRE_Q_4_11'];
            $data['PRE_C_4_11'] = $submissionData['PRETEST']['PRE_4_11']['PRE_C_4_11'];
            $data['PRE_Q_4_12'] = $submissionData['PRETEST']['PRE_4_12']['PRE_Q_4_12'];
            $data['PRE_C_4_12'] = $submissionData['PRETEST']['PRE_4_12']['PRE_C_4_12'];
            $data['PRETEST_SCORE'] = $submissionData['PRETEST']['PRETEST_SCORE'];
            $data['PRETEST_Display'] = $submissionData['PRETEST']['PRETEST_Display'];
            $data['PRETESTPHOTO'] = $submissionData['PRETEST']['PRETESTPHOTO'];
            $data['TEST_Q_5_1'] = $submissionData['TEST']['TEST_5_1']['TEST_Q_5_1'];
            $data['TEST_C_5_1'] = $submissionData['TEST']['TEST_5_1']['TEST_C_5_1'];
            $data['TEST_Q_5_2'] = $submissionData['TEST']['TEST_5_2']['TEST_Q_5_2'];
            $data['TEST_C_5_2'] = $submissionData['TEST']['TEST_5_2']['TEST_C_5_2'];
            $data['TEST_Q_5_3'] = $submissionData['TEST']['TEST_5_3']['TEST_Q_5_3'];
            $data['TEST_C_5_3'] = $submissionData['TEST']['TEST_5_3']['TEST_C_5_3'];
            $data['TEST_Q_5_4'] = $submissionData['TEST']['TEST_5_4']['TEST_Q_5_4'];
            $data['TEST_C_5_4'] = $submissionData['TEST']['TEST_5_4']['TEST_C_5_4'];
            $data['TEST_Q_5_5'] = $submissionData['TEST']['TEST_5_5']['TEST_Q_5_5'];
            $data['TEST_C_5_5'] = $submissionData['TEST']['TEST_5_5']['TEST_C_5_5'];
            $data['TEST_Q_5_6'] = $submissionData['TEST']['TEST_5_6']['TEST_Q_5_6'];
            $data['TEST_C_5_6'] = $submissionData['TEST']['TEST_5_6']['TEST_C_5_6'];
            $data['TEST_Q_5_7'] = $submissionData['TEST']['TEST_5_7']['TEST_Q_5_7'];
            $data['TEST_C_5_7'] = $submissionData['TEST']['TEST_5_7']['TEST_C_5_7'];
            $data['TEST_Q_5_8'] = $submissionData['TEST']['TEST_5_8']['TEST_Q_5_8'];
            $data['TEST_C_5_8'] = $submissionData['TEST']['TEST_5_8']['TEST_C_5_8'];
            $data['TEST_Q_5_9'] = $submissionData['TEST']['TEST_5_9']['TEST_Q_5_9'];
            $data['TEST_C_5_9'] = $submissionData['TEST']['TEST_5_9']['TEST_C_5_9'];
            $data['TEST_SCORE'] = $submissionData['TEST']['TEST_SCORE'];
            $data['TEST_DISPLAY'] = $submissionData['TEST']['TEST_DISPLAY'];
            $data['TESTPHOTO'] = $submissionData['TEST']['TESTPHOTO'];
            $data['POST_Q_6_1'] = $submissionData['POSTTEST']['POST_6_1']['POST_Q_6_1'];
            $data['POST_C_6_1'] = $submissionData['POSTTEST']['POST_6_1']['POST_C_6_1'];
            $data['POST_Q_6_2'] = $submissionData['POSTTEST']['POST_6_2']['POST_Q_6_2'];
            $data['POST_C_6_2'] = $submissionData['POSTTEST']['POST_6_2']['POST_C_6_2'];
            $data['POST_Q_6_3'] = $submissionData['POSTTEST']['POST_6_3']['POST_Q_6_3'];
            $data['POST_C_6_3'] = $submissionData['POSTTEST']['POST_6_3']['POST_C_6_3'];
            $data['POST_Q_6_4'] = $submissionData['POSTTEST']['POST_6_4']['POST_Q_6_4'];
            $data['POST_C_6_4'] = $submissionData['POSTTEST']['POST_6_4']['POST_C_6_4'];
            $data['POST_Q_6_5'] = $submissionData['POSTTEST']['POST_6_5']['POST_Q_6_5'];
            $data['POST_C_6_5'] = $submissionData['POSTTEST']['POST_6_5']['POST_C_6_5'];
            $data['POST_Q_6_6'] = $submissionData['POSTTEST']['POST_6_6']['POST_Q_6_6'];
            $data['POST_C_6_6'] = $submissionData['POSTTEST']['POST_6_6']['POST_C_6_6'];
            $data['POST_Q_6_7'] = $submissionData['POSTTEST']['POST_6_7']['POST_Q_6_7'];
            $data['POST_C_6_7'] = $submissionData['POSTTEST']['POST_6_7']['POST_C_6_7'];
            $data['POST_Q_6_8'] = $submissionData['POSTTEST']['POST_6_8']['POST_Q_6_8'];
            $data['POST_C_6_8'] = $submissionData['POSTTEST']['POST_6_8']['POST_C_6_8'];
            $data['POST_Q_6_9'] = $submissionData['POSTTEST']['POST_6_9']['POST_Q_6_9'];
            $data['POST_C_6_9'] = $submissionData['POSTTEST']['POST_6_9']['POST_C_6_9'];
            $data['POST_SCORE'] = $submissionData['POSTTEST']['POST_SCORE'];
            $data['POST_DISPLAY'] = $submissionData['POSTTEST']['POST_DISPLAY'];
            $data['POSTTESTPHOTO'] = $submissionData['POSTTEST']['POSTTESTPHOTO'];
            $data['EQA_Q_7_1'] = $submissionData['EQA']['EQA_7_1']['EQA_Q_7_1'];
            $data['EQA_C_7_1'] = $submissionData['EQA']['EQA_7_1']['EQA_C_7_1'];
            $data['EQA_Q_7_2'] = $submissionData['EQA']['EQA_7_2']['EQA_Q_7_2'];
            $data['EQA_C_7_2'] = $submissionData['EQA']['EQA_7_2']['EQA_C_7_2'];
            $data['EQA_Q_7_3'] = $submissionData['EQA']['EQA_7_3']['EQA_Q_7_3'];
            $data['EQA_C_7_3'] = $submissionData['EQA']['EQA_7_3']['EQA_C_7_3'];
            $data['EQA_Q_7_4'] = $submissionData['EQA']['EQA_7_4']['EQA_Q_7_4'];
            $data['EQA_C_7_4'] = $submissionData['EQA']['EQA_7_4']['EQA_C_7_4'];
            $data['EQA_Q_7_5'] = $submissionData['EQA']['EQA_7_5']['EQA_Q_7_5'];
            $data['EQA_C_7_5'] = $submissionData['EQA']['EQA_7_5']['EQA_C_7_5'];
            $data['EQA_Q_7_6'] = $submissionData['EQA']['EQA_7_6']['EQA_Q_7_6'];
            $data['EQA_C_7_6'] = $submissionData['EQA']['EQA_7_6']['EQA_C_7_6'];
            $data['EQA_Q_7_7'] = $submissionData['EQA']['EQA_7_7']['EQA_Q_7_7'];
            $data['EQA_C_7_7'] = $submissionData['EQA']['EQA_7_7']['EQA_C_7_7'];
            $data['EQA_Q_7_8'] = $submissionData['EQA']['EQA_7_8']['EQA_Q_7_8'];
            $data['EQA_C_7_8'] = $submissionData['EQA']['EQA_7_8']['EQA_C_7_8'];
            $data['sampleretesting'] = $submissionData['EQA']['sampleretesting'];
            $data['EQA_Q_7_9'] = $submissionData['EQA']['SAMPLEREF']['EQA_7_9']['EQA_Q_7_9'];
            $data['EQA_C_7_9'] = $submissionData['EQA']['SAMPLEREF']['EQA_7_9']['EQA_C_7_9'];
            $data['EQA_Q_7_10'] = $submissionData['EQA']['SAMPLEREF']['EQA_7_10']['EQA_Q_7_10'];
            $data['EQA_C_7_10'] = $submissionData['EQA']['SAMPLEREF']['EQA_7_10']['EQA_C_7_10'];
            $data['EQA_Q_7_11'] = $submissionData['EQA']['SAMPLEREF']['EQA_7_11']['EQA_Q_7_11'];
            $data['EQA_C_7_11'] = $submissionData['EQA']['SAMPLEREF']['EQA_7_11']['EQA_C_7_11'];
            $data['EQA_Q_7_12'] = $submissionData['EQA']['SAMPLEREF']['EQA_7_12']['EQA_Q_7_12'];
            $data['EQA_C_7_12'] = $submissionData['EQA']['SAMPLEREF']['EQA_7_12']['EQA_C_7_12'];
            $data['EQA_Q_7_13'] = $submissionData['EQA']['SAMPLEREF']['EQA_7_13']['EQA_Q_7_13'];
            $data['EQA_C_7_13'] = $submissionData['EQA']['SAMPLEREF']['EQA_7_13']['EQA_C_7_13'];
            $data['EQA_Q_7_14'] = $submissionData['EQA']['SAMPLEREF']['EQA_7_14']['EQA_Q_7_14'];
            $data['EQA_C_7_14'] = $submissionData['EQA']['SAMPLEREF']['EQA_7_14']['EQA_C_7_14'];
            $data['EQA_MAX_SCORE'] = $submissionData['EQA']['EQA_MAX_SCORE'];
            $data['EQA_REQ'] = $submissionData['EQA']['EQA_REQ'];
            $data['EQA_OPT'] = $submissionData['EQA']['EQA_OPT'];
            $data['EQA_SCORE'] = $submissionData['EQA']['EQA_SCORE'];
            $data['EQA_DISPLAY'] = $submissionData['EQA']['EQA_DISPLAY'];
            $data['EQAPHOTO'] = $submissionData['EQA']['EQAPHOTO'];
            $data['FINAL_AUDIT_SCORE'] = $submissionData['FINAL_AUDIT_SCORE'];
            $data['MAX_AUDIT_SCORE'] = $submissionData['MAX_AUDIT_SCORE'];
            $data['AUDIT_SCORE_PERCANTAGE'] = $submissionData['AUDIT_SCORE_PERCANTAGE'] ?? null;
            $data['staffaudited'] = $submissionData['staffaudited'] ?? null;
            $data['durationaudit'] = $submissionData['durationaudit'] ?? null;
            $data['personincharge'] = $submissionData['personincharge'] ?? null;
            $data['endofsurvey'] = $submissionData['endofsurvey'] ?? null;
            $data['info5'] = $submissionData['scoring']['info5'] ?? null;
            $data['info6'] = $submissionData['scoring']['info6'] ?? null;
            $data['info10'] = $submissionData['scoring']['info10'] ?? null;
            $data['info11'] = $submissionData['scoring']['info11'] ?? null;
            $data['summarypage'] = $submissionData['summarypage'] ?? null;
            $data['SUMMARY_NOT_AVL'] = $submissionData['SUMMARY_NOT_AVL'] ?? null;
            $data['info12'] = $submissionData['SUMMARY']['info12'] ?? null;
            $data['info17'] = $submissionData['SUMMARY']['info17'] ?? null;
            $data['info21'] = $submissionData['SUMMARY']['info21'] ?? null;
            $data['info22'] = $submissionData['SUMMARY']['info22'] ?? null;
            $data['info23'] = $submissionData['SUMMARY']['info23'] ?? null;
            $data['info24'] = $submissionData['SUMMARY']['info24'] ?? null;
            $data['info25'] = $submissionData['SUMMARY']['info25'] ?? null;
            $data['info26'] = $submissionData['SUMMARY']['info26'] ?? null;
            $data['info27'] = $submissionData['info27'] ?? null;
            $data['correctiveaction'] = json_encode($correctiveActions[$submissionData['__id']]);
            $data['sitephoto'] = $submissionData['sitephoto'] ?? null;
            $data['Latitude'] = $submissionData["lab_geopoint"]["coordinates"][1] ?? null;
            $data['Longitude'] = $submissionData["lab_geopoint"]["coordinates"][0] ?? null;
            $data['Altitude'] = $submissionData["lab_geopoint"]["coordinates"][2] ?? null;
            $data['Accuracy'] = $submissionData["lab_geopoint"]["properties"]['accuracy'] ?? null;
            $data['auditorSignature'] = $submissionData['auditorSignature'] ?? null;
            $data['instanceID'] = $submissionData['meta']['instanceID'];
            $data['instanceName'] = $submissionData['meta']['instanceName'];
            $data['status'] = $approveStatus;

            try {

                $sql = new Sql($this->adapter);

                if (isset($data['testingpointname']) && trim($data['testingpointname']) == "") {
                    $data['testingpointname'] = $data['testingpointtype'];
                }

                $data['instanceID'] = isset($data['instanceID']) ? $data['instanceID'] : "";
                $data['instanceName'] = isset($data['instanceName']) ? $data['instanceName'] : "";

                $par = array(
                    'token' => $projectId . ':' . $formId,
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
                    'NumberofTester' => (isset($data['NumberofTester']) && $data['NumberofTester'] > 0 ? $data['NumberofTester'] : 0),
                    'avgMonthTesting' => (isset($data['avgMonthTesting']) && $data['avgMonthTesting'] > 0 ? $data['avgMonthTesting'] : 0),
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
                    'status' => $approveStatus,
                    'central_project_id' => $projectId,
                    'central_form_id' => $formId,
                );
                $dbAdapter = $this->adapter;
                $this->db->insert(self::TABLE, $this->quoteColumns($par));
                $insertedId = $this->db->lastInsertId();

                if ($approveStatus == 'approved') {
                    $facilityDb = new SpiRtFacilitiesTable($dbAdapter);
                    $facilityResult = $facilityDb->addFacilityBasedOnForm($insertedId);
                }
            } catch (\Exception $e) {
                error_log($e->getMessage());
                error_log($e->getTraceAsString());
            }
        }
    }

    public function getPerformance($params)
    {
        $loginContainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->columns(array(
                'oldestDate' => new Expression("MIN(`assesmentofaudit`)"),
                'newestDate' => new Expression("MAX(`assesmentofaudit`)"),
                'totalDataPoints' => new Expression("COUNT(*)"),
                'level0' => new Expression("SUM(IF((AUDIT_SCORE_PERCANTAGE) < 40, 1,0))"),
                'level1' => new Expression("SUM(IF((AUDIT_SCORE_PERCANTAGE) >= 40 and (AUDIT_SCORE_PERCANTAGE) < 60, 1,0))"),
                'level2' => new Expression("SUM(IF((AUDIT_SCORE_PERCANTAGE) >= 60 and (AUDIT_SCORE_PERCANTAGE) < 80, 1,0))"),
                'level3' => new Expression("SUM(IF((AUDIT_SCORE_PERCANTAGE) >= 80 and (AUDIT_SCORE_PERCANTAGE) < 90, 1,0))"),
                'level4' => new Expression("SUM(IF((AUDIT_SCORE_PERCANTAGE) >= 90, 1,0))"),
            ))
            ->where(array('spiv3.status' => 'approved'));
        if (isset($params['fieldName']) && trim($params['fieldName']) != '') {
            $sQuery = $sQuery->where(array($params['fieldName'] => $params['val']));
        }

        if (isset($params['roundno']) && $params['roundno'] != '') {
            $sQuery = $sQuery->where(['spiv3.auditroundno' => CommonService::inList($params['roundno'])]);
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
            $sQuery = $sQuery->where(['spiv3.assesmentofaudit >= ?' => $startDate, 'spiv3.assesmentofaudit <= ?' => $endDate]);
        }

        if (isset($params['testPoint']) && trim($params['testPoint']) != '') {
            $sQuery = $sQuery->where(['spiv3.testingpointtype' => $params['testPoint']]);
            if (isset($params['testPointName']) && trim($params['testPointName']) != '') {
                if (trim($params['testPoint']) != 'other') {
                    $sQuery = $sQuery->where(['spiv3.testingpointname' => $params['testPointName']]);
                } else {
                    $sQuery = $sQuery->where(['spiv3.testingpointtype_other' => $params['testPointName']]);
                }
            }
        }
        if (isset($params['level']) && $params['level'] != '') {
            $sQuery = $sQuery->where(['spiv3.level' => $params['level']]);
        }
        if (isset($params['affiliation']) && $params['affiliation'] != '') {
            $sQuery = $sQuery->where(['spiv3.affiliation' => $params['affiliation']]);
        }
        if (isset($params['province']) && is_array($params['province']) && $params['province'] !== []) {
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('province', 'district'))
                ->where(['f.province' => CommonService::inList($params['province'])]);
            if (is_array($params['district']) && $params['district'] !== []) {
                $sQuery = $sQuery->where(['f.province' => CommonService::inList($params['province'])]);
            }
        } elseif (isset($params['province']) && $params['province'] != '') {
            $provinces = explode(",", $params['province']);
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('province', 'district'))
                ->where(['f.province' => CommonService::inList($provinces)]);
        }
        if (isset($params['province']) && $params['province'] != '') {
            if (is_array($params['district']) && $params['district'] !== []) {
                $sQuery = $sQuery->where(['f.district' => CommonService::inList($params['district'])]);
            } elseif ($params['district'] != '') {
                $provinces = explode(",", $params['district']);
                $sQuery = $sQuery->where(['f.district' => CommonService::inList($provinces)]);
            }
        }
        //if(isset($params['province']) && is_array($params['province']) && count($params['province'])>0 ){
        //    $sQuery = $sQuery->where('spiv3.level_name IN ("' . implode('", "', $params['province']) . '")');
        //}else{
        //    if(isset($params['province']) && $params['province']!=''){
        //        $provinces = explode(",",$params['province']);
        //        $sQuery = $sQuery->where('spiv3.level_name IN ("' . implode('", "', $provinces) . '")');
        //    }
        //}
        if (isset($params['scoreLevel']) && $params['scoreLevel'] != '') {
            if ($params['scoreLevel'] == 0) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE < 40");
            } elseif ($params['scoreLevel'] == 1) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 40 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 59");
            } elseif ($params['scoreLevel'] == 2) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 60 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 79");
            } elseif ($params['scoreLevel'] == 3) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 80 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 89");
            } elseif ($params['scoreLevel'] == 4) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 90");
            }
        }

        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }
        $sQuery = self::applyV3LocationScope($sQuery);

        $sQueryStr = $sql->buildSqlString($sQuery);
        //        die($sQueryStr);
        $rResult = $this->db->fetchAllAssociative($sQueryStr);
        return $rResult;
    }

    public function getPerformanceLast30Days($params)
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

        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->columns(array(
                'newestDate' => new Expression("'$startDate'"),
                'oldestDate' => new Expression("'$endDate'"),
                'totalDataPoints' => new Expression("COUNT(*)"),
                'level0' => new Expression("SUM(IF((AUDIT_SCORE_PERCANTAGE) < 40, 1,0))"),
                'level1' => new Expression("SUM(IF((AUDIT_SCORE_PERCANTAGE) >= 40 and (AUDIT_SCORE_PERCANTAGE) < 60, 1,0))"),
                'level2' => new Expression("SUM(IF((AUDIT_SCORE_PERCANTAGE) >= 60 and (AUDIT_SCORE_PERCANTAGE) < 80, 1,0))"),
                'level3' => new Expression("SUM(IF((AUDIT_SCORE_PERCANTAGE) >= 80 and (AUDIT_SCORE_PERCANTAGE) < 90, 1,0))"),
                'level4' => new Expression("SUM(IF((AUDIT_SCORE_PERCANTAGE) >= 90, 1,0))"),
            ))
            ->where("spiv3.status='approved'")
            ->where(['(`assesmentofaudit` BETWEEN ? - INTERVAL DATEDIFF(?, ?) DAY AND ?)' => [$startDate, $startDate, $endDate, $startDate]]);
        //    $sQuery = $sQuery->where("(`assesmentofaudit` BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE())");
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }
        $sQuery = self::applyV3LocationScope($sQuery);
        if (isset($params['auditRndNo']) && $params['auditRndNo'] != '') {
            $sQuery = $sQuery->where(['spiv3.auditroundno' => $params['auditRndNo']]);
        }
        if (isset($params['testPoint']) && trim($params['testPoint']) != '') {
            $sQuery = $sQuery->where(['spiv3.testingpointtype' => $params['testPoint']]);
            if (isset($params['testPointName']) && trim($params['testPointName']) != '') {
                if (trim($params['testPoint']) != 'other') {
                    $sQuery = $sQuery->where(['spiv3.testingpointname' => $params['testPointName']]);
                } else {
                    $sQuery = $sQuery->where(['spiv3.testingpointtype_other' => $params['testPointName']]);
                }
            }
        }
        if (isset($params['level']) && $params['level'] != '') {
            $sQuery = $sQuery->where(['spiv3.level' => $params['level']]);
        }
        if (isset($params['affiliation']) && $params['affiliation'] != '') {
            $sQuery = $sQuery->where(['spiv3.affiliation' => $params['affiliation']]);
        }
        if (isset($params['province']) && is_array($params['province']) && $params['province'] !== []) {
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('province', 'district'))
                ->where(['f.province' => CommonService::inList($params['province'])]);
            if (is_array($params['district']) && $params['district'] !== []) {
                $sQuery = $sQuery->where(['f.province' => CommonService::inList($params['province'])]);
            }
        } elseif (isset($params['province']) && $params['province'] != '') {
            $provinces = explode(",", $params['province']);
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('province', 'district'))
                ->where(['f.province' => CommonService::inList($provinces)]);
        }
        if (isset($params['province']) && $params['province'] != '') {
            if (is_array($params['district']) && $params['district'] !== []) {
                $sQuery = $sQuery->where(['f.district' => CommonService::inList($params['district'])]);
            } elseif ($params['district'] != '') {
                $provinces = explode(",", $params['district']);
                $sQuery = $sQuery->where(['f.district' => CommonService::inList($provinces)]);
            }
        }
        if (isset($params['scoreLevel']) && $params['scoreLevel'] != '') {
            if ($params['scoreLevel'] == 0) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE < 40");
            } elseif ($params['scoreLevel'] == 1) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 40 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 59");
            } elseif ($params['scoreLevel'] == 2) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 60 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 79");
            } elseif ($params['scoreLevel'] == 3) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 80 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 89");
            } elseif ($params['scoreLevel'] == 4) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 90");
            }
        }

        $sQueryStr = $sql->buildSqlString($sQuery);
        //die($sQueryStr);
        $rResult = $this->db->fetchAllAssociative($sQueryStr);

        return $rResult;
    }

    public function getPerformanceLast180Days()
    {
        $loginContainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $today = date('Y-m-d');
        $last180Date = date('Y-m-d', strtotime('-180 days', strtotime($today)));
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->columns(array(
                'newestDate' => new Expression("'$today'"),
                'oldestDate' => new Expression("'$last180Date'"),
                'totalDataPoints' => new Expression("COUNT(*)"),
                'level0' => new Expression("SUM(IF((AUDIT_SCORE_PERCANTAGE) < 40, 1,0))"),
                'level1' => new Expression("SUM(IF((AUDIT_SCORE_PERCANTAGE) >= 40 and (AUDIT_SCORE_PERCANTAGE) < 60, 1,0))"),
                'level2' => new Expression("SUM(IF((AUDIT_SCORE_PERCANTAGE) >= 60 and (AUDIT_SCORE_PERCANTAGE) < 80, 1,0))"),
                'level3' => new Expression("SUM(IF((AUDIT_SCORE_PERCANTAGE) >= 80 and (AUDIT_SCORE_PERCANTAGE) < 90, 1,0))"),
                'level4' => new Expression("SUM(IF((AUDIT_SCORE_PERCANTAGE) >= 90, 1,0))"),
            ))
            ->where(array('spiv3.status' => 'approved'))
            ->where("(`assesmentofaudit` BETWEEN CURDATE() - INTERVAL 180 DAY AND CURDATE())");
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }
        $sQuery = self::applyV3LocationScope($sQuery);
        $sQueryStr = $sql->buildSqlString($sQuery);
        //echo $sQueryStr;die;
        $rResult = $this->db->fetchAllAssociative($sQueryStr);

        return $rResult;
    }

    public function getAllSubmissions($sortOrder = 'DESC')
    {
        $loginContainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->where('spiv3.status != "deleted"')
            ->order(array("spiv3.status DESC", "spiv3.id $sortOrder"));
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }
        $sQuery = self::applyV3LocationScope($sQuery);
        $sQueryStr = $sql->buildSqlString($sQuery);
        //echo $sQueryStr;die;
        $rResult = $this->db->fetchAllAssociative($sQueryStr);
        return $rResult;
    }

    public function getAllApprovedTestingVolume($params)
    {
        $loginContainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->order(array("avgMonthTesting DESC"));
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }
        $sQuery = self::applyV3LocationScope($sQuery);
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
            $sQuery = $sQuery->where(['spiv3.assesmentofaudit >= ?' => $startDate, 'spiv3.assesmentofaudit <= ?' => $endDate]);
        }

        if (isset($params['auditRndNo']) && $params['auditRndNo'] != '') {
            $sQuery = $sQuery->where(['spiv3.auditroundno' => $params['auditRndNo']]);
        }
        if (isset($params['testPoint']) && trim($params['testPoint']) != '') {
            $sQuery = $sQuery->where(['spiv3.testingpointtype' => $params['testPoint']]);
            if (isset($params['testPointName']) && trim($params['testPointName']) != '') {
                if (trim($params['testPoint']) != 'other') {
                    $sQuery = $sQuery->where(['spiv3.testingpointname' => $params['testPointName']]);
                } else {
                    $sQuery = $sQuery->where(['spiv3.testingpointtype_other' => $params['testPointName']]);
                }
            }
        }
        if (isset($params['level']) && $params['level'] != '') {
            $sQuery = $sQuery->where(['spiv3.level' => $params['level']]);
        }
        if (isset($params['affiliation']) && $params['affiliation'] != '') {
            $sQuery = $sQuery->where(['spiv3.affiliation' => $params['affiliation']]);
        }
        if (isset($params['province']) && is_array($params['province']) && $params['province'] !== []) {
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('province', 'district'))
                ->where(['f.province' => CommonService::inList($params['province'])]);
            if (is_array($params['district']) && $params['district'] !== []) {
                $sQuery = $sQuery->where(['f.province' => CommonService::inList($params['province'])]);
            }
        } elseif (isset($params['province']) && $params['province'] != '') {
            $provinces = explode(",", $params['province']);
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('province', 'district'))
                ->where(['f.province' => CommonService::inList($provinces)]);
        }
        if (isset($params['province']) && $params['province'] != '') {
            if (is_array($params['district']) && $params['district'] !== []) {
                $sQuery = $sQuery->where(['f.district' => CommonService::inList($params['district'])]);
            } elseif ($params['district'] != '') {
                $provinces = explode(",", $params['district']);
                $sQuery = $sQuery->where(['f.district' => CommonService::inList($provinces)]);
            }
        }
        if (isset($params['scoreLevel']) && $params['scoreLevel'] != '') {
            if ($params['scoreLevel'] == 0) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE < 40");
            } elseif ($params['scoreLevel'] == 1) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 40 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 59");
            } elseif ($params['scoreLevel'] == 2) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 60 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 79");
            } elseif ($params['scoreLevel'] == 3) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 80 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 89");
            } elseif ($params['scoreLevel'] == 4) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 90");
            }
        }
        $sQueryStr = $sql->buildSqlString($sQuery);
        //echo $sQueryStr;die;
        $rResult = $this->db->fetchAllAssociative($sQueryStr);
        return $rResult;
    }

    public function fetchAllSubmissionsDetails($parameters, $acl)
    {
        $loginContainer = new Container('credo');
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */
        $queryContainer = new Container('query');
        $aColumns = array('spiv3.id', 'facilityname', 'auditroundno', "DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", 'testingpointname', 'testingpointtype', 'level', 'affiliation', 'AUDIT_SCORE_PERCANTAGE', 'spiv3.status');
        $orderColumns = array('spiv3.id', 'facilityname', 'auditroundno', 'assesmentofaudit', 'testingpointname', 'testingpointtype', 'level', 'affiliation', 'AUDIT_SCORE_PERCANTAGE', 'spiv3.status');

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

        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->where('spiv3.status != "deleted"')
            ->join(
                array('f' => 'spi_rt_3_facilities'),
                'f.id=spiv3.facility',
                array('province', 'district'),
                'left'
            );

        if ($parameters['auditRndNo'] != '') {
            $sQuery = $sQuery->where(['spiv3.auditroundno' => $parameters['auditRndNo']]);
        }
        if (trim($startDate) != "" && trim($endDate) != "") {
            $sQuery = $sQuery->where(['spiv3.assesmentofaudit >= ?' => $startDate, 'spiv3.assesmentofaudit <= ?' => $endDate]);
        }
        if (isset($parameters['testPoint']) && trim($parameters['testPoint']) != '') {
            $sQuery = $sQuery->where(['spiv3.testingpointtype' => $parameters['testPoint']]);
            if (isset($parameters['testPointName']) && trim($parameters['testPointName']) != '') {
                if (trim($parameters['testPoint']) != 'other') {
                    $sQuery = $sQuery->where(['spiv3.testingpointname' => $parameters['testPointName']]);
                } else {
                    $sQuery = $sQuery->where(['spiv3.testingpointtype_other' => $parameters['testPointName']]);
                }
            }
        }
        if ($parameters['level'] != '') {
            $sQuery = $sQuery->where(['spiv3.level' => $parameters['level']]);
        }
        if ($parameters['affiliation'] != '') {
            $sQuery = $sQuery->where(['spiv3.affiliation' => $parameters['affiliation']]);
        }
        if (is_array($parameters['province']) && $parameters['province'] !== []) {
            $sQuery = $sQuery->where(['f.province' => CommonService::inList($parameters['province'])]);
            if (is_array($parameters['district']) && $parameters['district'] !== []) {
                $sQuery = $sQuery->where(['f.province' => CommonService::inList($parameters['province'])]);
            }
        } elseif ($parameters['province'] != '') {
            $provinces = explode(",", $parameters['province']);
            $sQuery = $sQuery->where(['f.province' => CommonService::inList($provinces)]);
        }
        if ($parameters['province'] != '') {
            if (is_array($parameters['district']) && $parameters['district'] !== []) {
                $sQuery = $sQuery->where(['f.district' => CommonService::inList($parameters['district'])]);
            } elseif ($parameters['district'] != '') {
                $provinces = explode(",", $parameters['district']);
                $sQuery = $sQuery->where(['f.district' => CommonService::inList($provinces)]);
            }
        }
        if (isset($parameters['scoreLevel']) && $parameters['scoreLevel'] != '') {
            if ($parameters['scoreLevel'] == 0) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE < 40");
            } elseif ($parameters['scoreLevel'] == 1) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 40 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 59");
            } elseif ($parameters['scoreLevel'] == 2) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 60 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 79");
            } elseif ($parameters['scoreLevel'] == 3) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 80 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 89");
            } elseif ($parameters['scoreLevel'] == 4) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 90");
            }
        }
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }
        $sQuery = self::applyV3LocationScope($sQuery);
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

        //echo $sQueryStr;die;
        $rResult = $this->db->fetchAllAssociative($sQueryStr);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->buildSqlString($sQuery);
        $iFilteredTotal = $this->db->executeQuery($fQuery)->rowCount();

        /* Total data set length */
        $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->where('spiv3.status != "deleted"')
            ->join(
                array('f' => 'spi_rt_3_facilities'),
                'f.id=spiv3.facility',
                array('province', 'district'),
                'left'
            );
        if ($parameters['auditRndNo'] != '') {
            $tQuery = $tQuery->where(['spiv3.auditroundno' => $parameters['auditRndNo']]);
        }
        if (trim($startDate) != "" && trim($endDate) != "") {
            $tQuery = $tQuery->where(['spiv3.assesmentofaudit >= ?' => $startDate, 'spiv3.assesmentofaudit <= ?' => $endDate]);
        }
        if (isset($parameters['testPoint']) && trim($parameters['testPoint']) != '') {
            $tQuery = $tQuery->where(['spiv3.testingpointtype' => $parameters['testPoint']]);
            if (isset($parameters['testPointName']) && trim($parameters['testPointName']) != '') {
                if (trim($parameters['testPoint']) != 'other') {
                    $tQuery = $tQuery->where(['spiv3.testingpointname' => $parameters['testPointName']]);
                } else {
                    $tQuery = $tQuery->where(['spiv3.testingpointtype_other' => $parameters['testPointName']]);
                }
            }
        }
        if ($parameters['level'] != '') {
            $tQuery = $tQuery->where(['spiv3.level' => $parameters['level']]);
        }
        if ($parameters['affiliation'] != '') {
            $tQuery = $tQuery->where(['spiv3.affiliation' => $parameters['affiliation']]);
        }
        if (is_array($parameters['province']) && $parameters['province'] !== []) {
            $tQuery = $tQuery->where(['f.province' => CommonService::inList($parameters['province'])]);
            if (is_array($parameters['district']) && $parameters['district'] !== []) {
                $tQuery = $tQuery->where(['f.province' => CommonService::inList($parameters['province'])]);
            }
        } elseif ($parameters['province'] != '') {
            $provinces = explode(",", $parameters['province']);
            $tQuery = $tQuery->where(['f.province' => CommonService::inList($provinces)]);
        }
        if ($parameters['province'] != '') {
            if (is_array($parameters['district']) && $parameters['district'] !== []) {
                $tQuery = $tQuery->where(['f.district' => CommonService::inList($parameters['district'])]);
            } elseif ($parameters['district'] != '') {
                $provinces = explode(",", $parameters['district']);
                $tQuery = $tQuery->where(['f.district' => CommonService::inList($provinces)]);
            }
        }
        if (isset($parameters['scoreLevel']) && $parameters['scoreLevel'] != '') {
            if ($parameters['scoreLevel'] == 0) {
                $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE < 40");
            } elseif ($parameters['scoreLevel'] == 1) {
                $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 40 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 59");
            } elseif ($parameters['scoreLevel'] == 2) {
                $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 60 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 79");
            } elseif ($parameters['scoreLevel'] == 3) {
                $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 80 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 89");
            } elseif ($parameters['scoreLevel'] == 4) {
                $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 90");
            }
        }
        if (!empty($loginContainer->token)) {
            $tQuery = $tQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }
        $tQuery = self::applyV3LocationScope($tQuery);
        $tQueryStr = $sql->buildSqlString($tQuery); // Get the string of the Sql, instead of the Select-instance
        $iTotal = $this->db->executeQuery($tQueryStr)->rowCount();
        $output = array(
            "sEcho" => (int) $parameters['sEcho'],
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array(),
        );

        $role = $loginContainer->roleCode;
        $downloadPdfAction = (bool) $acl->isAllowed($role, 'Application\Controller\SpiV3Controller', 'download-pdf');

        $approveStatusAction = (bool) $acl->isAllowed($role, 'Application\Controller\SpiV3Controller', 'approve-status');

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
            $auditScore += (int) $aRow['AUDIT_SCORE_PERCANTAGE'];
            if (isset($aRow['AUDIT_SCORE_PERCANTAGE']) && $aRow['AUDIT_SCORE_PERCANTAGE'] < 40) {
                $levelZero[] = $aRow['AUDIT_SCORE_PERCANTAGE'];
            } elseif (isset($aRow['AUDIT_SCORE_PERCANTAGE']) && $aRow['AUDIT_SCORE_PERCANTAGE'] >= 40 && $aRow['AUDIT_SCORE_PERCANTAGE'] < 60) {
                $levelOne[] = $aRow['AUDIT_SCORE_PERCANTAGE'];
            } elseif (isset($aRow['AUDIT_SCORE_PERCANTAGE']) && $aRow['AUDIT_SCORE_PERCANTAGE'] >= 60 && $aRow['AUDIT_SCORE_PERCANTAGE'] < 80) {
                $levelTwo[] = $aRow['AUDIT_SCORE_PERCANTAGE'];
            } elseif (isset($aRow['AUDIT_SCORE_PERCANTAGE']) && $aRow['AUDIT_SCORE_PERCANTAGE'] >= 80 && $aRow['AUDIT_SCORE_PERCANTAGE'] < 90) {
                $levelThree[] = $aRow['AUDIT_SCORE_PERCANTAGE'];
            } elseif (isset($aRow['AUDIT_SCORE_PERCANTAGE']) && $aRow['AUDIT_SCORE_PERCANTAGE'] >= 90) {
                $levelFour[] = $aRow['AUDIT_SCORE_PERCANTAGE'];
            }
            $row['DT_RowId'] = $aRow['id'];
            $level = isset($aRow['level_other']) && $aRow['level_other'] != "" ? " - " . $aRow['level_other'] : '';
            $row[] = '';
            $row[] = CommonService::escapeHtml($aRow['facilityname']);
            $row[] = CommonService::escapeHtml($aRow['province']);
            $row[] = CommonService::escapeHtml($aRow['district']);
            $row[] = CommonService::escapeHtml($aRow['auditroundno']);
            $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
            $row[] = CommonService::escapeHtml(isset($aRow['testingpointname']) && $aRow['testingpointname'] != "" ? $aRow['testingpointname'] : $aRow['testingpointtype']);
            $row[] = CommonService::escapeHtml($aRow['testingpointtype']);
            $row[] = CommonService::escapeHtml($aRow['level'] . $level);
            $row[] = CommonService::escapeHtml($aRow['affiliation']);
            $row[] = round((float) ($aRow['AUDIT_SCORE_PERCANTAGE']), 2);
            $row[] = CommonService::escapeHtml(ucwords($aRow['status']));
            //$print = '<a href="/spi-v3/print/' . $aRow['id'] . '" target="_blank" style="white-space:nowrap;"><i class="fa fa-print"></i> Print</a>';
            if ($aRow['status'] == 'pending' && $approveStatusAction) {
                $approve = '<br><a href="javascript:void(0);" onclick="approveStatus(' . (int) $aRow['id'] . ')"  style="white-space:nowrap;"><i class="fa fa-check"></i>  Approve</a>';
            }

            if ($downloadPdfAction) {
                $downloadPdf = '<br><a href="javascript:void(0);" onclick="downloadPdf(' . (int) $aRow['id'] . ')" style="white-space:nowrap;"><i class="fa fa-download"></i> PDF</a>';
            }
            //$pending = '<br><a href="/spi-v3/edit/' . $aRow['id'] . '" style="white-space:nowrap;"><i class="fa fa-pencil"></i> Edit</a>';
            $row[] = $approve . " " . $downloadPdf;
            /* Score columns are varchars carrying whatever ODK submitted, so they
             * are text cells like any other rather than numbers. */
            $row[] = CommonService::escapeHtml($aRow['PERSONAL_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['PHYSICAL_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['SAFETY_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['PRETEST_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['TEST_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['POST_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['EQA_SCORE']);
            $t = (float) $aRow['PERSONAL_SCORE'] + (float) $aRow['PHYSICAL_SCORE'] + (float) $aRow['SAFETY_SCORE'] + (float) $aRow['PRETEST_SCORE'] + (float) $aRow['TEST_SCORE'] + (float) $aRow['POST_SCORE'] + (float) $aRow['EQA_SCORE'];
            $row[] = $t . "(" . round((float) ($aRow['AUDIT_SCORE_PERCANTAGE']), 2) . ")";
            $output['aaData'][] = $row;
        }
        //get earliest date
        $eQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))->columns(array('assesmentofaudit'))->order('assesmentofaudit ASC');
        // The earliest audit the user may see, scoped as the list is.
        if (!empty($loginContainer->token)) {
            $eQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }
        $eQuery = self::applyV3LocationScope($eQuery);
        $eQueryStr = $sql->buildSqlString($eQuery); // Get the string of the Sql, instead of the Select-instance
        $eResult = $this->db->fetchAssociative($eQueryStr);
        //get duplicate value
        $dpResult = $this->db->fetchAllAssociative("SELECT `meta-instance-id`, COUNT(*) c FROM spi_form_v_3 GROUP BY `meta-instance-id` HAVING c > 1");

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

    public function fetchAllDuplicateSubmissionsDetails()
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        // Every copy of each duplicated submission, oldest first, so the view can
        // keep the first copy and offer the rest for removal by their own id.
        return $this->db->fetchAllAssociative("SELECT s.`meta-instance-id`, s.`id`, s.`facilityname`, s.`status`, s.`auditroundno`, s.`AUDIT_SCORE_PERCANTAGE`, s.`affiliation`, s.`level`, s.`assesmentofaudit`, s.`testingpointtype`, s.`testingpointname`
            FROM spi_form_v_3 s
            INNER JOIN (SELECT `meta-instance-id` FROM spi_form_v_3 GROUP BY `meta-instance-id` HAVING COUNT(*) > 1) d ON d.`meta-instance-id` = s.`meta-instance-id`
            ORDER BY s.`meta-instance-id`, s.`id`");
    }

    public function fetchAllSubmissionsDatas($parameters, $acl)
    {
        $loginContainer = new Container('credo');
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */

        $aColumns = array('facilityname', 'auditroundno', 'assesmentofaudit', 'testingpointtype', 'level', 'affiliation', 'AUDIT_SCORE_PERCANTAGE', 'spiv3.status');
        $orderColumns = array('facilityname', 'auditroundno', 'assesmentofaudit', 'testingpointtype', 'level', 'affiliation', 'AUDIT_SCORE_PERCANTAGE', 'spiv3.status');

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
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->where('spiv3.status != "deleted"');
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }
        $sQuery = self::applyV3LocationScope($sQuery);
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

        $sQueryStr = $sql->buildSqlString($sQuery); // Get the string of the Sql, instead of the Select-instance
        //echo $sQueryStr;die;
        $rResult = $this->db->fetchAllAssociative($sQueryStr);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->buildSqlString($sQuery);
        $iFilteredTotal = $this->db->executeQuery($fQuery)->rowCount();

        /* Total data set length */
        $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->where('spiv3.status != "deleted"');
        if (!empty($loginContainer->token)) {
            $tQuery = $tQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }
        $tQuery = self::applyV3LocationScope($tQuery);
        $tQueryStr = $sql->buildSqlString($tQuery); // Get the string of the Sql, instead of the Select-instance
        $iTotal = $this->db->executeQuery($tQueryStr)->rowCount();
        $output = array(
            "sEcho" => (int) $parameters['sEcho'],
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array(),
        );
        $role = $loginContainer->roleCode;
        $update = (bool) $acl->isAllowed($role, 'Application\Controller\SpiV3Controller', 'edit');

        $delete = (bool) $acl->isAllowed($role, 'Application\Controller\SpiV3Controller', 'delete');

        $downloadPdfAction = (bool) $acl->isAllowed($role, 'Application\Controller\SpiV3Controller', 'download-pdf');


        foreach ($rResult as $aRow) {
            $row = [];
            $downloadPdf = "";
            $edit = "";
            $remove = "";
            $row['DT_RowId'] = $aRow['id'];
            $row[] = CommonService::escapeHtml($aRow['facilityname']);
            $row[] = CommonService::escapeHtml($aRow['auditroundno']);
            $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
            $row[] = CommonService::escapeHtml($aRow['testingpointtype']);
            $row[] = CommonService::escapeHtml($aRow['level']);
            $row[] = CommonService::escapeHtml($aRow['affiliation']);
            $row[] = round((float) ($aRow['AUDIT_SCORE_PERCANTAGE']), 2);
            $row[] = CommonService::escapeHtml(ucwords($aRow['status']));
            if ($downloadPdfAction) {
                //$downloadPdf = '<a href="javascript:void(0);" onclick="downloadPdf('.$aRow['id'].')" style="white-space:nowrap;"><i class="fa fa-download"></i> PDF</a>';
            }
            if ($update) {
                $edit = '&nbsp;<a href="/spi-v3/edit/' . (int) $aRow['id'] . '" style="white-space:nowrap;"><i class="fa fa-pencil"></i> Edit</a>';
            }
            if ($delete) {
                $remove = '&nbsp;<a href="javascript:void(0);" onclick="deleteAudit(' . (int) $aRow['id'] . ');" style="white-space:nowrap;"><i class="fa fa-times"></i> Delete</a>';
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
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->where(array('spiv3.status' => 'pending'));
        $sQueryStr = $sql->buildSqlString($sQuery);
        return $this->db->fetchAllAssociative($sQueryStr);
    }

    public function getFormData($id, $pdfDowload = 'no')
    {
        $loginContainer = new Container('credo');
        $username = $loginContainer->login;
        $dbAdapter = $this->adapter;
        $trackTable = new EventLogTable($dbAdapter);
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->where(array('spiv3.id' => $id));
        $sQueryStr = $sql->buildSqlString($sQuery);
        $sResult = $this->fetchRowObject($sQueryStr);
        if ($sResult) {
            if (trim($sResult['facility']) != '' || trim($sResult['facilityid']) != '' || trim($sResult['facilityname']) != '') {
                $fQuery = $sql->select()->from(array('spirt3' => 'spi_rt_3_facilities'))
                    ->columns(array('fId' => 'id', 'ffId' => 'facility_id', 'fName' => 'facility_name', 'fEmail' => 'email', 'fCPerson' => 'contact_person', 'fDistrict' => 'district', 'fProvince' => 'province', 'fLatitude' => 'latitude', 'fLongitude' => 'longitude'));
                if (isset($sResult['facility']) && $sResult['facility'] > 0) {
                    $fQuery = $fQuery->where(['spirt3.id' => $sResult['facility']]);
                } elseif (isset($sResult['facilityid']) && $sResult['facilityid'] != '') {
                    $fQuery = $fQuery->where(['spirt3.facility_id' => $sResult['facilityid']]);
                } elseif (isset($sResult['facilityname']) && $sResult['facilityname'] != '') {
                    $fQuery = $fQuery->where(['spirt3.facility_name' => $sResult['facilityname']]);
                }
                $fQueryStr = $sql->buildSqlString($fQuery);
                $sResult['facilityInfo'] = $this->fetchRowObject($fQueryStr);
            }
            if ($pdfDowload == 'yes') {
                $subject = '';
                $eventType = 'Print-SPI RT Form 3-PDF';
                $action = $username . ' has printed the SPI RT Form 3 PDF';
                $resourceName = 'Print-SPI-RT-Form-3-PDF';
                $trackTable->addEventLog($subject, $eventType, $action, $resourceName);
            }
        }
        return $sResult;
    }

    public function getAuditRoundWiseData($params)
    {
        $loginContainer = new Container('credo');
        //$rResult = $this->getAllSubmissions();
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->order(array("spiv3.id DESC"));
        if (isset($params['roundno']) && $params['roundno'] != '') {
            $sQuery = $sQuery->where(['spiv3.auditroundno' => CommonService::inList($params['roundno'])]);
        }
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }
        $sQuery = self::applyV3LocationScope($sQuery);
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
            $sQuery = $sQuery->where(['spiv3.assesmentofaudit >= ?' => $startDate, 'spiv3.assesmentofaudit <= ?' => $endDate]);
        }
        if ($params['auditRndNo'] != '') {
            $sQuery = $sQuery->where(['spiv3.auditroundno' => $params['auditRndNo']]);
        }

        if (isset($params['testPoint']) && trim($params['testPoint']) != '') {
            $sQuery = $sQuery->where(['spiv3.testingpointtype' => $params['testPoint']]);
            if (isset($params['testPointName']) && trim($params['testPointName']) != '') {
                if (trim($params['testPoint']) != 'other') {
                    $sQuery = $sQuery->where(['spiv3.testingpointname' => $params['testPointName']]);
                } else {
                    $sQuery = $sQuery->where(['spiv3.testingpointtype_other' => $params['testPointName']]);
                }
            }
        }
        if (isset($params['level']) && $params['level'] != '') {
            $sQuery = $sQuery->where(['spiv3.level' => $params['level']]);
        }
        if (isset($params['affiliation']) && $params['affiliation'] != '') {
            $sQuery = $sQuery->where(['spiv3.affiliation' => $params['affiliation']]);
        }
        if (is_array($params['province']) && $params['province'] !== []) {
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('province', 'district'))
                ->where(['f.province' => CommonService::inList($params['province'])]);
            if (is_array($params['district']) && $params['district'] !== []) {
                $sQuery = $sQuery->where(['f.province' => CommonService::inList($params['province'])]);
            }
        } elseif ($params['province'] != '') {
            $provinces = explode(",", $params['province']);
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('province', 'district'))
                ->where(['f.province' => CommonService::inList($provinces)]);
        }
        if ($params['province'] != '') {
            if (is_array($params['district']) && $params['district'] !== []) {
                $sQuery = $sQuery->where(['f.district' => CommonService::inList($params['district'])]);
            } elseif ($params['district'] != '') {
                $provinces = explode(",", $params['district']);
                $sQuery = $sQuery->where(['f.district' => CommonService::inList($provinces)]);
            }
        }
        //if(isset($params['province']) && is_array($params['province']) && count($params['province'])>0 ){
        //    $sQuery = $sQuery->where('spiv3.level_name IN ("' . implode('", "', $params['province']) . '")');
        //}else{
        //    if(isset($params['province']) && $params['province']!=''){
        //        $provinces = explode(",",$params['province']);
        //        $sQuery = $sQuery->where('spiv3.level_name IN ("' . implode('", "', $provinces) . '")');
        //    }
        //}
        if (isset($params['scoreLevel']) && $params['scoreLevel'] != '') {
            if ($params['scoreLevel'] == 0) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE < 40");
            } elseif ($params['scoreLevel'] == 1) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 40 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 59");
            } elseif ($params['scoreLevel'] == 2) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 60 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 79");
            } elseif ($params['scoreLevel'] == 3) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 80 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 89");
            } elseif ($params['scoreLevel'] == 4) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 90");
            }
        }

        if (isset($params['fieldName']) && trim($params['fieldName']) != '') {
            $sQuery = $sQuery->where(array($params['fieldName'] => $params['val']));
        }
        $sQueryStr = $sql->buildSqlString($sQuery);
        $rResult = $this->db->fetchAllAssociative($sQueryStr);

        $response = [];

        foreach ($rResult as $row) {
            //$response[$row['auditroundno']]['PERSONAL_SCORE'][]=  $row['PERSONAL_SCORE'];
            //$response[$row['auditroundno']]['PHYSICAL_SCORE'][]=  $row['PHYSICAL_SCORE'];
            //$response[$row['auditroundno']]['SAFETY_SCORE'][]=  $row['SAFETY_SCORE'];
            //$response[$row['auditroundno']]['PRETEST_SCORE'][]=  $row['PRETEST_SCORE'];
            //$response[$row['auditroundno']]['TEST_SCORE'][]=  $row['TEST_SCORE'];
            //$response[$row['auditroundno']]['POST_SCORE'][]=  $row['POST_SCORE'];
            //$response[$row['auditroundno']]['EQA_SCORE'][]=  $row['EQA_SCORE'];
            $response[0]['PERSONAL_SCORE'][] = $row['PERSONAL_SCORE'];
            $response[0]['PHYSICAL_SCORE'][] = $row['PHYSICAL_SCORE'];
            $response[0]['SAFETY_SCORE'][] = $row['SAFETY_SCORE'];
            $response[0]['PRETEST_SCORE'][] = $row['PRETEST_SCORE'];
            $response[0]['TEST_SCORE'][] = $row['TEST_SCORE'];
            $response[0]['POST_SCORE'][] = $row['POST_SCORE'];
            $response[0]['EQA_SCORE'][] = $row['EQA_SCORE'];
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
        }
        $response = array('');
        return $auditRoundWiseData;
    }

    public function getZeroQuestionCounts($params)
    {
        $loginContainer = new Container('credo');
        //$rResult = $this->fetchAllApprovedSubmissions();
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->where(array('spiv3.status' => 'approved'))
            ->order(array("assesmentofaudit DESC"));
        if (isset($params['roundno']) && $params['roundno'] != '') {
            $sQuery = $sQuery->where(['spiv3.auditroundno' => CommonService::inList($params['roundno'])]);
        }

        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }
        $sQuery = self::applyV3LocationScope($sQuery);
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
            $sQuery = $sQuery->where(['spiv3.assesmentofaudit >= ?' => $startDate, 'spiv3.assesmentofaudit <= ?' => $endDate]);
        }
        if ($params['auditRndNo'] != '') {
            $sQuery = $sQuery->where(['spiv3.auditroundno' => $params['auditRndNo']]);
        }

        if (isset($params['testPoint']) && trim($params['testPoint']) != '') {
            $sQuery = $sQuery->where(['spiv3.testingpointtype' => $params['testPoint']]);
            if (isset($params['testPointName']) && trim($params['testPointName']) != '') {
                if (trim($params['testPoint']) != 'other') {
                    $sQuery = $sQuery->where(['spiv3.testingpointname' => $params['testPointName']]);
                } else {
                    $sQuery = $sQuery->where(['spiv3.testingpointtype_other' => $params['testPointName']]);
                }
            }
        }
        if (isset($params['level']) && $params['level'] != '') {
            $sQuery = $sQuery->where(['spiv3.level' => $params['level']]);
        }
        if (isset($params['affiliation']) && $params['affiliation'] != '') {
            $sQuery = $sQuery->where(['spiv3.affiliation' => $params['affiliation']]);
        }
        if (is_array($params['province']) && $params['province'] !== []) {
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('province', 'district'))
                ->where(['f.province' => CommonService::inList($params['province'])]);
            if (is_array($params['district']) && $params['district'] !== []) {
                $sQuery = $sQuery->where(['f.province' => CommonService::inList($params['province'])]);
            }
        } elseif ($params['province'] != '') {
            $provinces = explode(",", $params['province']);
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('province', 'district'))
                ->where(['f.province' => CommonService::inList($provinces)]);
        }
        if ($params['province'] != '') {
            if (is_array($params['district']) && $params['district'] !== []) {
                $sQuery = $sQuery->where(['f.district' => CommonService::inList($params['district'])]);
            } elseif ($params['district'] != '') {
                $provinces = explode(",", $params['district']);
                $sQuery = $sQuery->where(['f.district' => CommonService::inList($provinces)]);
            }
        }
        if (isset($params['scoreLevel']) && $params['scoreLevel'] != '') {
            if ($params['scoreLevel'] == 0) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE < 40");
            } elseif ($params['scoreLevel'] == 1) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 40 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 59");
            } elseif ($params['scoreLevel'] == 2) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 60 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 79");
            } elseif ($params['scoreLevel'] == 3) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 80 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 89");
            } elseif ($params['scoreLevel'] == 4) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 90");
            }
        }

        $sQueryStr = $sql->buildSqlString($sQuery);
        //echo $sQueryStr;die;
        $rResult = $this->db->fetchAllAssociative($sQueryStr);

        $response = [];

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
            $this->updateAudits($data, array('id' => $id));
        }
        return $id;
    }

    public function fetchAllApprovedSubmissions($sortOrder = 'DESC')
    {
        $loginContainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->where(array('spiv3.status' => 'approved'))
            ->order(array("assesmentofaudit $sortOrder"));
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }
        $sQuery = self::applyV3LocationScope($sQuery);
        $sQueryStr = $sql->buildSqlString($sQuery);
        //echo $sQueryStr;die;
        $rResult = $this->db->fetchAllAssociative($sQueryStr);
        return $rResult;
    }

    public function fecthAllApprovedSubmissionsTable($parameters)
    {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */
        $queryContainer = new Container('query');
        $loginContainer = new Container('credo');
        $aColumns = array('facilityname', 'assesmentofaudit', 'testingpointname', 'PERSONAL_SCORE', 'PHYSICAL_SCORE', 'SAFETY_SCORE', 'PRETEST_SCORE', 'TEST_SCORE', 'POST_SCORE', 'EQA_SCORE', 'FINAL_AUDIT_SCORE', 'AUDIT_SCORE_PERCANTAGE');
        $orderColumns = array('facilityname', 'assesmentofaudit', 'testingpointname', 'PERSONAL_SCORE', 'PHYSICAL_SCORE', 'SAFETY_SCORE', 'PRETEST_SCORE', 'TEST_SCORE', 'POST_SCORE', 'EQA_SCORE', 'FINAL_AUDIT_SCORE', 'AUDIT_SCORE_PERCANTAGE');

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
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->where(array('spiv3.status' => 'approved'));
        if ($parameters['auditRndNo'] != '') {
            $sQuery = $sQuery->where(['spiv3.auditroundno' => $parameters['auditRndNo']]);
        }
        if (trim($startDate) != "" && trim($endDate) != "") {
            $sQuery = $sQuery->where(['spiv3.assesmentofaudit >= ?' => $startDate, 'spiv3.assesmentofaudit <= ?' => $endDate]);
        }
        if (isset($parameters['testPoint']) && trim($parameters['testPoint']) != '') {
            $sQuery = $sQuery->where(['spiv3.testingpointtype' => $parameters['testPoint']]);
            if (isset($parameters['testPointName']) && trim($parameters['testPointName']) != '') {
                if (trim($parameters['testPoint']) != 'other') {
                    $sQuery = $sQuery->where(['spiv3.testingpointname' => $parameters['testPointName']]);
                } else {
                    $sQuery = $sQuery->where(['spiv3.testingpointtype_other' => $parameters['testPointName']]);
                }
            }
        }
        if ($parameters['level'] != '') {
            $sQuery = $sQuery->where(['spiv3.level' => $parameters['level']]);
        }
        if (is_array($parameters['province']) && $parameters['province'] !== []) {
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('province', 'district'))
                ->where(['f.province' => CommonService::inList($parameters['province'])]);
            if (is_array($parameters['district']) && $parameters['district'] !== []) {
                $sQuery = $sQuery->where(['f.province' => CommonService::inList($parameters['province'])]);
            }
        } elseif ($parameters['province'] != '') {
            $provinces = explode(",", $parameters['province']);
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('province', 'district'))
                ->where(['f.province' => CommonService::inList($provinces)]);
        }
        if ($parameters['province'] != '') {
            if (is_array($parameters['district']) && $parameters['district'] !== []) {
                $sQuery = $sQuery->where(['f.district' => CommonService::inList($parameters['district'])]);
            } elseif ($parameters['district'] != '') {
                $provinces = explode(",", $parameters['district']);
                $sQuery = $sQuery->where(['f.district' => CommonService::inList($provinces)]);
            }
        }
        if ($parameters['affiliation'] != '') {
            $sQuery = $sQuery->where(['spiv3.affiliation' => $parameters['affiliation']]);
        }
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }
        $sQuery = self::applyV3LocationScope($sQuery);
        if (isset($parameters['scoreLevel']) && $parameters['scoreLevel'] != '') {
            if ($parameters['scoreLevel'] == 0) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE < 40");
            } elseif ($parameters['scoreLevel'] == 1) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 40 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 59");
            } elseif ($parameters['scoreLevel'] == 2) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 60 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 79");
            } elseif ($parameters['scoreLevel'] == 3) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 80 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 89");
            } elseif ($parameters['scoreLevel'] == 4) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 90");
            }
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
        $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->where(array('spiv3.status' => 'approved'));
        if ($parameters['auditRndNo'] != '') {
            $tQuery = $tQuery->where(['spiv3.auditroundno' => $parameters['auditRndNo']]);
        }
        if (trim($startDate) != "" && trim($endDate) != "") {
            $tQuery = $tQuery->where(['spiv3.assesmentofaudit >= ?' => $startDate, 'spiv3.assesmentofaudit <= ?' => $endDate]);
        }
        if (isset($parameters['testPoint']) && trim($parameters['testPoint']) != '') {
            $tQuery = $tQuery->where(['spiv3.testingpointtype' => $parameters['testPoint']]);
            if (isset($parameters['testPointName']) && trim($parameters['testPointName']) != '') {
                if (trim($parameters['testPoint']) != 'other') {
                    $tQuery = $tQuery->where(['spiv3.testingpointname' => $parameters['testPointName']]);
                } else {
                    $tQuery = $tQuery->where(['spiv3.testingpointtype_other' => $parameters['testPointName']]);
                }
            }
        }
        if ($parameters['level'] != '') {
            $tQuery = $tQuery->where(['spiv3.level' => $parameters['level']]);
        }
        if (is_array($parameters['province']) && $parameters['province'] !== []) {
            $tQuery = $tQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('province', 'district'))
                ->where(['f.province' => CommonService::inList($parameters['province'])]);
            if (is_array($parameters['district']) && $parameters['district'] !== []) {
                $tQuery = $tQuery->where(['f.province' => CommonService::inList($parameters['province'])]);
            }
        } elseif ($parameters['province'] != '') {
            $provinces = explode(",", $parameters['province']);
            $tQuery = $tQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('province', 'district'))
                ->where(['f.province' => CommonService::inList($provinces)]);
        }
        if ($parameters['province'] != '') {
            if (is_array($parameters['district']) && $parameters['district'] !== []) {
                $tQuery = $tQuery->where(['f.district' => CommonService::inList($parameters['district'])]);
            } elseif ($parameters['district'] != '') {
                $provinces = explode(",", $parameters['district']);
                $tQuery = $tQuery->where(['f.district' => CommonService::inList($provinces)]);
            }
        }
        if ($parameters['affiliation'] != '') {
            $tQuery = $tQuery->where(['spiv3.affiliation' => $parameters['affiliation']]);
        }
        if (!empty($loginContainer->token)) {
            $tQuery = $tQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }
        $tQuery = self::applyV3LocationScope($tQuery);
        if (isset($parameters['scoreLevel']) && $parameters['scoreLevel'] != '') {
            if ($parameters['scoreLevel'] == 0) {
                $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE < 40");
            } elseif ($parameters['scoreLevel'] == 1) {
                $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 40 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 59");
            } elseif ($parameters['scoreLevel'] == 2) {
                $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 60 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 79");
            } elseif ($parameters['scoreLevel'] == 3) {
                $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 80 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 89");
            } elseif ($parameters['scoreLevel'] == 4) {
                $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 90");
            }
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
            $row[] = CommonService::escapeHtml($aRow['testingpointname'] . " - " . $aRow['testingpointtype']);
            $row[] = CommonService::escapeHtml($aRow['PERSONAL_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['PHYSICAL_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['SAFETY_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['PRETEST_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['TEST_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['POST_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['EQA_SCORE']);
            $row[] = CommonService::escapeHtml($aRow['FINAL_AUDIT_SCORE']);
            $row[] = round((float) ($aRow['AUDIT_SCORE_PERCANTAGE']), 2);
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function fetchAllApprovedSubmissionLocation($params)
    {
        $loginContainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->where(array('spiv3.status' => 'approved'))
            ->order(array("assesmentofaudit DESC"));


        $auditRound = $params['roundno'] ?: array($this->fetchLatestSpiV3FormAuditNo());

        $sQuery = $sQuery->where(['spiv3.auditroundno' => CommonService::inList($auditRound)]);

        // if (isset($params['roundno']) && $params['roundno'] != '') {
        //     $sQuery = $sQuery->where('spiv3.auditroundno IN ("' . implode('", "', $params['roundno']) . '")');
        // }


        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }
        $sQuery = self::applyV3LocationScope($sQuery);
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
            $sQuery = $sQuery->where(['spiv3.assesmentofaudit >= ?' => $startDate, 'spiv3.assesmentofaudit <= ?' => $endDate]);
        }
        if ($params['auditRndNo'] != '') {
            $sQuery = $sQuery->where(['spiv3.auditroundno' => $params['auditRndNo']]);
        }

        if (isset($params['testPoint']) && trim($params['testPoint']) != '') {
            $sQuery = $sQuery->where(['spiv3.testingpointtype' => $params['testPoint']]);
            if (isset($params['testPointName']) && trim($params['testPointName']) != '') {
                if (trim($params['testPoint']) != 'other') {
                    $sQuery = $sQuery->where(['spiv3.testingpointname' => $params['testPointName']]);
                } else {
                    $sQuery = $sQuery->where(['spiv3.testingpointtype_other' => $params['testPointName']]);
                }
            }
        }
        if (isset($params['level']) && $params['level'] != '') {
            $sQuery = $sQuery->where(['spiv3.level' => $params['level']]);
        }
        if (isset($params['affiliation']) && $params['affiliation'] != '') {
            $sQuery = $sQuery->where(['spiv3.affiliation' => $params['affiliation']]);
        }
        if (is_array($params['province']) && $params['province'] !== []) {
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('province', 'district'))
                ->where(['f.province' => CommonService::inList($params['province'])]);
            if (is_array($params['district']) && $params['district'] !== []) {
                $sQuery = $sQuery->where(['f.province' => CommonService::inList($params['province'])]);
            }
        } elseif ($params['province'] != '') {
            $provinces = explode(",", $params['province']);
            $sQuery = $sQuery->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('province', 'district'))
                ->where(['f.province' => CommonService::inList($provinces)]);
        }
        if ($params['province'] != '') {
            if (is_array($params['district']) && $params['district'] !== []) {
                $sQuery = $sQuery->where(['f.district' => CommonService::inList($params['district'])]);
            } elseif ($params['district'] != '') {
                $provinces = explode(",", $params['district']);
                $sQuery = $sQuery->where(['f.district' => CommonService::inList($provinces)]);
            }
        }
        if (isset($params['scoreLevel']) && $params['scoreLevel'] != '') {
            if ($params['scoreLevel'] == 0) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE < 40");
            } elseif ($params['scoreLevel'] == 1) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 40 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 59");
            } elseif ($params['scoreLevel'] == 2) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 60 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 79");
            } elseif ($params['scoreLevel'] == 3) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 80 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 89");
            } elseif ($params['scoreLevel'] == 4) {
                $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 90");
            }
        }
        $sQueryStr = $sql->buildSqlString($sQuery);
        //echo $sQueryStr;die;
        return $this->db->fetchAllAssociative($sQueryStr);
    }


    public function updateSpiFormDetails($params)
    {
        //\Zend\Debug\Debug::dump($params);die;
        if (trim($params['formId']) != "") {
            $sessionLogin = new Container('credo');
            $user_name = $sessionLogin->login;
            $dbAdapter = $this->adapter;
            $eventTable = new EventLogTable($dbAdapter);
            $sql = new Sql($this->adapter);
            $formId = base64_decode($params['formId']);
            // Without corrective action rows in the post, the column stays NULL.
            $summationData = null;
            if (isset($params['sectionNo'])) {
                $summationData = [];
                $n = count($params['sectionNo']);
                for ($i = 0; $i < $n; $i++) {
                    $rowId = $params['rowId'][$i];
                    if (isset($params['sectionNo'][$i]) && trim($params['sectionNo'][$i]) != "" && trim($params['deficiency'][$i]) != "" && trim($params['correction' . $rowId]) != "") {
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
            }
            if ($params['province'] == 'other' && trim($params['provinceTextBox']) != '') {
                $params['province'] = $params['provinceTextBox'];
            }
            if ($params['district'] == 'other' && trim($params['districtTextBox']) != '') {
                $params['district'] = $params['districtTextBox'];
            }

            //update facility tbl
            $id = 0;
            $facilityDb = new SpiRtFacilitiesTable($dbAdapter);
            if (trim($params['testingFacility']) != '') {
                $id = base64_decode($params['testingFacility']);
                $facilityDb->updateFacilityInfo($id, $params);
            } elseif (trim($params['testingFacilityName']) != '') {
                $fQuery = $sql->select()->from(array('spirt3' => 'spi_rt_3_facilities'))
                    ->columns(array('id'))
                    ->where(['spirt3.facility_name' => $params['testingFacilityName']]);
                $fQueryStr = $sql->buildSqlString($fQuery);
                $fResult = $this->db->fetchAssociative($fQueryStr);
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
                'testingpointname' => $params['testingPointName'],
                'testingpointtype' => $params['testingPointType'],
                'testingpointtype_other' => $params['testingPointTypeOther'],
                'locationaddress' => $params['location'],
                'level' => $params['level'],
                'level_other' => $params['levelOther'],
                'level_name' => $params['levelName'],
                'affiliation' => $params['affiliation'],
                'affiliation_other' => $params['affiliationOther'],
                'NumberofTester' => (isset($params['NumberofTester']) && $params['NumberofTester'] > 0 ? $params['NumberofTester'] : 0),
                'avgMonthTesting' => (isset($params['avgMonthTesting']) && $params['avgMonthTesting'] > 0 ? $params['avgMonthTesting'] : 0),
                'name_auditor_lead' => $params['name_auditor_lead'],
                'name_auditor2' => $params['name_auditor2'],
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
                'durationaudit' => $params['durationaudit'],
                //'FINAL_AUDIT_SCORE' => $params['totalPointsScored'],
                //'MAX_AUDIT_SCORE' => $params['totalScoreExpect'],
                //'AUDIT_SCORE_PERCANTAGE' => $params['auditScorePercentage'],
                'correctiveaction' => $summationData,
            );
            $result = $this->updateAudits($data, array('id' => $formId));
            $subject = '';
            $eventType = 'Update-SPI RT Form 3-Request';
            $action = $user_name . ' has updated the SPI RT Form 3 information';
            $resourceName = 'SPI-RT-Form-3';
            $eventTable->addEventLog($subject, $eventType, $action, $resourceName);

            return $formId;
        }
    }
    public function fetchSpiV3FormAuditNo()
    {
        $loginContainer = new Container('credo');
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->columns(array('assesmentofaudit', 'auditroundno', 'rowCount' => new Expression("COUNT('auditroundno')")))
            ->group('auditroundno')
            ->order("auditroundno ASC");
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }
        $sQuery = self::applyV3LocationScope($sQuery);
        $sQueryStr = $sql->buildSqlString($sQuery);
        return $this->db->fetchAllAssociative($sQueryStr);
    }

    public function fetchLatestSpiV3FormAuditNo()
    {
        $loginContainer = new Container('credo');
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->columns(array('auditroundno'))
            ->group('auditroundno')
            ->order(array("assesmentofaudit DESC", "auditroundno DESC"))
            ->limit(1);
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }
        $sQuery = self::applyV3LocationScope($sQuery);
        $sQueryStr = $sql->buildSqlString($sQuery);
        $res = $this->db->fetchAssociative($sQueryStr);
        return $res === false ? null : $res['auditroundno'];
    }

    public function fetchSpiV3FormFacilityAuditNo($params)
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        // The facility list names the field it filters on, as "facilityid" or
        // "facilityId". Only these two fields exist.
        $columns = ['facilityid' => 'spiv3.facilityid', 'facilityname' => 'spiv3.facilityname'];
        $column = $columns[strtolower((string) ($params['fieldName'] ?? ''))] ?? null;
        if ($column === null) {
            return [];
        }
        $loginContainer = new Container('credo');
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->columns(array(new Expression('DISTINCT(auditroundno) as auditroundno'), 'rowCount' => new Expression("COUNT('auditroundno')")))
            ->where([$column => $params['val'] ?? ''])
            ->group('auditroundno')
            ->order("auditroundno ASC");
        if (!empty($loginContainer->token)) {
            $sQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }
        $sQuery = self::applyV3LocationScope($sQuery);
        $sQueryStr = $sql->buildSqlString($sQuery);
        //echo $sQueryStr;die;
        $rResult = $this->db->fetchAllAssociative($sQueryStr);

        return $rResult;
    }

    public function mergeFacilityName($params)
    {
        $result = 0;
        $result1 = 0;
        $result2 = 0;
        $loginContainer = new Container('credo');
        $userName = $loginContainer->login;
        $dbAdapter = $this->adapter;
        $auditTable = new EventLogTable($dbAdapter);
        $sql = new Sql($this->adapter);
        $facilityDb = new SpiRtFacilitiesTable($this->adapter);
        if (isset($params['editFacilityName']) && trim($params['editFacilityName']) != '') {
            $facilityQuery = $sql->select()->from(array('spirt3' => 'spi_rt_3_facilities'))->columns(array('facility_name'))
                ->where(array('spirt3.facility_name' => $params['defaultFacilityName']));
            $facilityQueryStr = $sql->buildSqlString($facilityQuery);
            $facilityResult = $this->db->fetchAssociative($facilityQueryStr);
            if ($facilityResult) {
                $data = array(
                    'facility_id' => $params['facilityId'],
                    'facility_name' => $params['editFacilityName'],
                );
                $facilityDb->update($data, array('facility_name' => $params['defaultFacilityName']));
            }

            $aQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))->columns(array('facilityname'))
                ->where(array('spiv3.facilityname' => $params['defaultFacilityName']));
            $aQueryStr = $sql->buildSqlString($aQuery);
            $aResult = $this->db->fetchAssociative($aQueryStr);
            if ($aResult) {
                $data = array(
                    'facilityid' => $params['facilityId'],
                    'facilityname' => $params['editFacilityName'],
                );
                $this->updateAudits($data, array('facilityname' => $params['defaultFacilityName']));
            }
        }
        $c = count($params['upFaciltyName']);
        for ($i = 0; $i < $c; $i++) {
            $aQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))->columns(array('facilityname', 'id'))
                ->where(array('spiv3.facilityname' => $params['upFaciltyName'][$i]));
            $aQueryStr = $sql->buildSqlString($aQuery);
            $aResult = $this->db->fetchAllAssociative($aQueryStr);
            //error_log($aQueryStr);
            if (count($aResult) > 0) {
                $counter = count($aResult);
                for ($k = 0; $k < $counter; $k++) {
                    $data = array(
                        'facilityid' => $params['facilityId'],
                        'facilityname' => $params['editFacilityName'],
                    );
                    $result1 = $this->updateAudits($data, array('id' => $aResult[$k]['id']));
                }
            }

            //Update status in Facility table
            $facilityQuery = $sql->select()->from(array('spirt3' => 'spi_rt_3_facilities'))->columns(array('facility_name'))
                ->where(array('spirt3.facility_name' => $params['upFaciltyName'][$i]));
            $facilityQueryStr = $sql->buildSqlString($facilityQuery);
            $facilityResult = $this->db->fetchAssociative($facilityQueryStr);
            if ($facilityResult) {
                $facilityDb->update(array('status' => 'deleted'), array('facility_name' => $params['upFaciltyName'][$i]));
            }
        }
        $result = (int) $result1 || (int) $result2;
        $subject = '';
        $eventType = 'Merge-Facility-Name';
        $action = $userName . ' has merged the facility name ' . $params['editFacilityName'];
        $resourceName = 'Merge-Facility-Name';
        $auditTable->addEventLog($subject, $eventType, $action, $resourceName);
        return $result;
    }

    //get all faciltiy name
    public function fetchAllFacilityNames()
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $loginContainer = new Container('credo');
        $uQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))->columns(array('facilityname' => new Expression("DISTINCT spiv3.facilityname")))->order('spiv3.facilityname ASC');
        if (!empty($loginContainer->token)) {
            $uQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }
        $uQuery = self::applyV3LocationScope($uQuery);
        $uQueryStr = $sql->buildSqlString($uQuery);
        $uResult = $this->db->fetchAllAssociative($uQueryStr);

        $aQuery = $sql->select()->from(array('spirt3' => 'spi_rt_3_facilities'))
            ->where('spirt3.status != "deleted"')->order('spirt3.facility_name ASC');
        $aQuery = SpiRtFacilitiesTable::applyMappingToFacilities($aQuery, 'spirt3');
        $aQueryStr = $sql->buildSqlString($aQuery);
        $aResult = $this->db->fetchAllAssociative($aQueryStr);

        return array('uniqueName' => $uResult, 'allName' => $aResult);
    }

    public function fetchAllTestingPointsBasedOnFacility($parameters, $acl)
    {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */

        $aColumns = array('facilityid', 'facilityname', 'testingpointname', "DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", 'AUDIT_SCORE_PERCANTAGE');
        $orderColumns = array('facilityid', 'facilityname', 'testingpointname', 'assesmentofaudit', 'AUDIT_SCORE_PERCANTAGE');

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
        $loginContainer = new Container('credo');
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))->columns(array('id', 'facilityid', 'facilityname', 'testingpointname', 'testingpointtype', 'assesmentofaudit', 'AUDIT_SCORE_PERCANTAGE'));
        if (!empty($loginContainer->token)) {
            $sQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }
        $sQuery = self::applyV3LocationScope($sQuery);

        if (isset($parameters['fieldName']) && $parameters['fieldName'] == 'facilityId') {
            $sQuery = $sQuery->where(array('facilityid' => $parameters['val']));
        } elseif (isset($parameters['fieldName']) && $parameters['fieldName'] == 'facilityName') {
            $sQuery = $sQuery->where(array('facilityname' => $parameters['val']));
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

        $sQueryStr = $sql->buildSqlString($sQuery); // Get the string of the Sql, instead of the Select-instance
        //echo $sQueryStr;die;
        $rResult = $this->db->fetchAllAssociative($sQueryStr);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->buildSqlString($sQuery);
        $iFilteredTotal = $this->db->executeQuery($fQuery)->rowCount();

        /* Total data set length */
        $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'));
        if (!empty($loginContainer->token)) {
            $tQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }
        $tQuery = self::applyV3LocationScope($tQuery);
        if (isset($parameters['fieldName']) && $parameters['fieldName'] == 'facilityId') {
            $tQuery = $tQuery->where(array('facilityid' => $parameters['val']));
        } elseif (isset($parameters['fieldName']) && $parameters['fieldName'] == 'facilityName') {
            $tQuery = $tQuery->where(array('facilityname' => $parameters['val']));
        }
        $tQueryStr = $sql->buildSqlString($tQuery); // Get the string of the Sql, instead of the Select-instance

        $iTotal = $this->db->executeQuery($tQueryStr)->rowCount();
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

            $row[] = CommonService::escapeHtml($aRow['facilityid']);
            $row[] = CommonService::escapeHtml(ucwords($aRow['facilityname']));
            $row[] = CommonService::escapeHtml($aRow['testingpointtype']);
            $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
            $row[] = round((float) ($aRow['AUDIT_SCORE_PERCANTAGE']), 2);
            if ($downloadPdfAction) {
                $downloadPdf = '<br><a href="javascript:void(0);" onclick="downloadPdf(' . (int) $aRow['id'] . ')" style="white-space:nowrap;"><i class="fa fa-download"></i> PDF</a>';
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
            $loginContainer = new Container('credo');
            $query = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                ->columns(array('id', 'testingpointname', 'assesmentofaudit'))
                ->where(array('spiv3.facilityname' => $params['facilityName'], 'spiv3.status' => 'approved'));
            if (!empty($loginContainer->token)) {
                $query->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
            }
            $query = self::applyV3LocationScope($query);
            $queryStr = $sql->buildSqlString($query);
            $audits = $this->db->fetchAllAssociative($queryStr);

            $aQuery = $sql->select()->from(array('spiv3_fclt' => 'spi_rt_3_facilities'))
                ->columns(array('facility_name', 'email'))
                ->where(array('spiv3_fclt.facility_name' => $params['facilityName']));
            $aQueryStr = $sql->buildSqlString($aQuery);
            $aResult = $this->fetchRowObject($aQueryStr);
        }
        return array('audits' => $audits, 'facilityProfile' => $aResult);
    }

    public function getSpiV3PendingCount()
    {
        $loginContainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->where('spiv3.status = "pending"');
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }
        $sQuery = self::applyV3LocationScope($sQuery);
        $sQueryStr = $sql->buildSqlString($sQuery);
        //echo $sQueryStr;die;
        $rResult = $this->db->fetchAllAssociative($sQueryStr);
        return count($rResult);
    }

    public function deleteAuditRowData($params)
    {
        $result = 0;
        $deleteId = trim((string) ($params['deleteId'] ?? ''));
        if ($deleteId !== '') {
            $result = $this->updateAudits(['status' => 'deleted'], ['id' => $deleteId]);
            if ($result > 0) {
                $loginContainer = new Container('credo');
                $userName = $loginContainer->login ?? 'system';
                $eventTable = new EventLogTable($this->adapter);
                $eventTable->addEventLog(
                    '',
                    'delete',
                    $userName . ' deleted SPI-RT v3 record #' . $deleteId,
                    'SPI-RT v3'
                );
            }
        }
        return $result;
    }

    public function fetchAllApprovedSubmissionsDetailsBasedOnAuditDate($parameters, $acl)
    {
        $loginContainer = new Container('credo');
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */

        $aColumns = array('facilityname', 'auditroundno', 'assesmentofaudit', 'testingpointname', 'testingpointtype', 'level', 'affiliation', 'AUDIT_SCORE_PERCANTAGE', 'spiv3.status');
        $orderColumns = array('facilityname', 'auditroundno', 'assesmentofaudit', 'testingpointname', 'testingpointtype', 'level', 'affiliation', 'AUDIT_SCORE_PERCANTAGE', 'spiv3.status');

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
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->where('spiv3.status != "deleted"');

        $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->where('spiv3.status != "deleted"');

        if (isset($parameters['assesmentOfAuditDate']) && $parameters['assesmentOfAuditDate'] != '') {
            $sQuery = $sQuery->where("spiv3.assesmentofaudit='" . CommonService::isoDateFormat($parameters['assesmentOfAuditDate']) . "'");
            $tQuery = $tQuery->where("spiv3.assesmentofaudit='" . CommonService::isoDateFormat($parameters['assesmentOfAuditDate']) . "'");
        }
        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
            $tQuery = $tQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }
        $sQuery = self::applyV3LocationScope($sQuery);
        $tQuery = self::applyV3LocationScope($tQuery);
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

        $role = $loginContainer->roleCode;
        $downloadPdfAction = (bool) $acl->isAllowed($role, 'Application\Controller\SpiV3Controller', 'download-pdf');

        $approveStatusAction = (bool) $acl->isAllowed($role, 'Application\Controller\SpiV3Controller', 'approve-status');


        foreach ($rResult as $aRow) {
            $row = [];
            $downloadPdf = "";

            $level = isset($aRow['level_other']) && $aRow['level_other'] != "" ? " - " . $aRow['level_other'] : '';

            $row[] = CommonService::escapeHtml($aRow['facilityname']);
            $row[] = CommonService::escapeHtml($aRow['auditroundno']);
            $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
            $row[] = CommonService::escapeHtml(isset($aRow['testingpointname']) && $aRow['testingpointname'] != "" ? $aRow['testingpointname'] : $aRow['testingpointtype']);
            $row[] = CommonService::escapeHtml($aRow['testingpointtype']);
            $row[] = CommonService::escapeHtml($aRow['level'] . $level);
            $row[] = CommonService::escapeHtml($aRow['affiliation']);
            $row[] = round((float) ($aRow['AUDIT_SCORE_PERCANTAGE']), 2);
            $row[] = CommonService::escapeHtml(ucwords($aRow['status']));
            //$print = '<a href="/spi-v3/print/' . $aRow['id'] . '" target="_blank" style="white-space:nowrap;"><i class="fa fa-print"></i> Print</a>';

            if ($downloadPdfAction) {
                $downloadPdf = '<br><a href="javascript:void(0);" onclick="downloadPdf(' . (int) $aRow['id'] . ')" style="white-space:nowrap;"><i class="fa fa-download"></i> PDF</a>';
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
        return $this->db->fetchAllAssociative($tokenQueryStr);
    }

    public function fetchViewDataDetails($parameters)
    {
        $loginContainer = new Container('credo');
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */
        // An unrecognised source matches no columns rather than failing on them.
        $aColumns = [];
        $orderColumns = [];
        if ($parameters['source'] == 'hv') {
            $aColumns = array("DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", 'facilityname', 'testingpointname', 'avgMonthTesting', 'NumberofTester');
            $orderColumns = array('assesmentofaudit', 'facilityname', 'testingpointname', 'avgMonthTesting', 'NumberofTester', 'AUDIT_SCORE_PERCANTAGE');
        } elseif ($parameters['source'] == 'la') {
            $aColumns = array("DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", 'facilityname', 'testingpointname', 'avgMonthTesting', 'AUDIT_SCORE_PERCANTAGE');
            $orderColumns = array('assesmentofaudit', 'facilityname', 'testingpointname', 'avgMonthTesting', 'AUDIT_SCORE_PERCANTAGE');
        } elseif ($parameters['source'] == 'ad') {
            $aColumns = array("DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", "DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')");
            $orderColumns = array('assesmentofaudit', 'assesmentofaudit');
        } elseif ($parameters['source'] == 'apall' || $parameters['source'] == 'apl180' || $parameters['source'] == 'ap') {
            $aColumns = array('facilityid', 'facilityname', 'AUDIT_SCORE_PERCANTAGE', "DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", 'testingpointtype', 'testingpointname', 'testingpointtype_other', 'level', 'affiliation', 'AUDIT_SCORE_PERCANTAGE', 'AUDIT_SCORE_PERCANTAGE');
            $orderColumns = array('facilityid', 'facilityname', 'AUDIT_SCORE_PERCANTAGE', 'assesmentofaudit', 'testingpointtype', 'testingpointname', 'level', 'affiliation', 'AUDIT_SCORE_PERCANTAGE', 'AUDIT_SCORE_PERCANTAGE');
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
            $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                ->columns(array(new Expression('DISTINCT(assesmentofaudit) as assesmentofaudit'), 'totalDataPoints' => new Expression("COUNT(*)")))
                ->where(array('spiv3.status' => 'approved'))
                ->group('spiv3.assesmentofaudit');
            $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
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
            $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                ->columns(array('facilityid', 'facilityname', 'auditroundno', 'assesmentofaudit', 'testingpointtype', 'testingpointname', 'testingpointtype_other', 'level', 'affiliation', 'AUDIT_SCORE_PERCANTAGE'))
                ->where(array('spiv3.status' => 'approved'));
            $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                ->columns(array('facilityid', 'facilityname', 'auditroundno', 'assesmentofaudit', 'testingpointtype', 'testingpointname', 'testingpointtype_other', 'level', 'affiliation', 'AUDIT_SCORE_PERCANTAGE'))
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
                    $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE < 40");
                    $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE < 40");
                } elseif ($parameters['scoreLevel'] == 1) {
                    $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 40 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 59");
                    $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 40 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 59");
                } elseif ($parameters['scoreLevel'] == 2) {
                    $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 60 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 79");
                    $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 60 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 79");
                } elseif ($parameters['scoreLevel'] == 3) {
                    $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 80 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 89");
                    $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 80 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 89");
                } elseif ($parameters['scoreLevel'] == 4) {
                    $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 90");
                    $tQuery = $tQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 90");
                }
            }
            if (isset($parameters['level']) && $parameters['level'] != '') {
                $sQuery = $sQuery->where(['spiv3.level' => $parameters['level']]);
                $tQuery = $tQuery->where(['spiv3.level' => $parameters['level']]);
            }
            if (isset($parameters['province']) && $parameters['province'] != '') {
                $sQuery = $sQuery->where(['spiv3.level_name' => CommonService::inList($parameters['province'])]);
                $tQuery = $tQuery->where(['spiv3.level_name' => CommonService::inList($parameters['province'])]);
            }
            if (isset($parameters['affiliation']) && $parameters['affiliation'] != '') {
                $sQuery = $sQuery->where(['spiv3.affiliation' => $parameters['affiliation']]);
                $tQuery = $tQuery->where(['spiv3.affiliation' => $parameters['affiliation']]);
            }
        } elseif ($parameters['source'] == 'apspi') {
            //For Audit Performance
            $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                ->columns(array('assesmentofaudit', 'PERSONAL_SCORE' => new Expression('AVG(PERSONAL_SCORE)'), 'PHYSICAL_SCORE' => new Expression('AVG(PHYSICAL_SCORE)'), 'SAFETY_SCORE' => new Expression('AVG(SAFETY_SCORE)'), 'PRETEST_SCORE' => new Expression('AVG(PRETEST_SCORE)'), 'TEST_SCORE' => new Expression('AVG(TEST_SCORE)'), 'POST_SCORE' => new Expression('AVG(POST_SCORE)'), 'EQA_SCORE' => new Expression('AVG(EQA_SCORE)')))
                ->group('spiv3.assesmentofaudit');
            $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                ->columns(array('assesmentofaudit', 'PERSONAL_SCORE' => new Expression('AVG(PERSONAL_SCORE)'), 'PHYSICAL_SCORE' => new Expression('AVG(PHYSICAL_SCORE)'), 'SAFETY_SCORE' => new Expression('AVG(SAFETY_SCORE)'), 'PRETEST_SCORE' => new Expression('AVG(PRETEST_SCORE)'), 'TEST_SCORE' => new Expression('AVG(TEST_SCORE)'), 'POST_SCORE' => new Expression('AVG(POST_SCORE)'), 'EQA_SCORE' => new Expression('AVG(EQA_SCORE)')))
                ->group('spiv3.assesmentofaudit');
            if (isset($parameters['roundno']) && $parameters['roundno'] != '') {
                $sQuery = $sQuery->where(['spiv3.auditroundno' => CommonService::inList($parameters['roundno'])]);
                $tQuery = $tQuery->where(['spiv3.auditroundno' => CommonService::inList($parameters['roundno'])]);
            }
        } else {
            //For Others
            $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                ->columns(array('assesmentofaudit', 'facilityname', 'testingpointname', 'testingpointtype', 'avgMonthTesting', 'NumberofTester', 'AUDIT_SCORE_PERCANTAGE'));

            $tQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                ->columns(array('assesmentofaudit', 'facilityname', 'testingpointname', 'testingpointtype', 'avgMonthTesting', 'NumberofTester', 'AUDIT_SCORE_PERCANTAGE'));
        }

        if (!empty($loginContainer->token)) {
            $sQuery = $sQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
            $tQuery = $tQuery->where(['spiv3.token' => CommonService::inList($loginContainer->token)]);
        }
        $sQuery = self::applyV3LocationScope($sQuery);
        $tQuery = self::applyV3LocationScope($tQuery);

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
         * The score column is text: "+ 0" reads it as PHP did. A level counts
         * only a score that is set, and an unset score adds 0 to the average.
         */
        $isScored = in_array($parameters['source'], ['hv', 'la', 'apall', 'apl180', 'ap'], true);
        $summaryColumns = ['filtered' => new Expression('COUNT(*)')];
        if ($isScored) {
            $isSet = 't.AUDIT_SCORE_PERCANTAGE IS NOT NULL';
            $score = 'COALESCE(t.AUDIT_SCORE_PERCANTAGE + 0, 0)';
            $summaryColumns['scoreTotal'] = new Expression("SUM($score)");
            $summaryColumns['levelZero'] = new Expression("SUM($isSet AND $score < 40)");
            $summaryColumns['levelOne'] = new Expression("SUM($isSet AND $score >= 40 AND $score < 60)");
            $summaryColumns['levelTwo'] = new Expression("SUM($isSet AND $score >= 60 AND $score < 80)");
            $summaryColumns['levelThree'] = new Expression("SUM($isSet AND $score >= 80 AND $score < 90)");
            $summaryColumns['levelFour'] = new Expression("SUM($isSet AND $score >= 90)");
        }
        $summaryQuery = $sql->select()->from(['t' => $sQuery])->columns($summaryColumns);
        $summary = $this->db->fetchAssociative($sql->buildSqlString($summaryQuery));
        $iFilteredTotal = (int) $summary['filtered'];

        $totalQuery = $sql->select()->from(['t' => $tQuery])->columns(['total' => new Expression('COUNT(*)')]);
        $iTotal = (int) $this->db->fetchOne($sql->buildSqlString($totalQuery));

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

        if (isset($sLimit) && isset($sOffset)) {
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
        foreach ($rResult as $aRow) {
            $row = [];
            // Reset per row: a row without a score would otherwise show the previous row's level.
            $level = null;
            if ($isScored && isset($aRow['AUDIT_SCORE_PERCANTAGE'])) {
                $scorePer = $aRow['AUDIT_SCORE_PERCANTAGE'];
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
                $row[] = CommonService::escapeHtml($aRow['avgMonthTesting'] ?? 0);
                $row[] = CommonService::escapeHtml($aRow['NumberofTester'] ?? 0);
                $row[] = $level;
            } elseif ($parameters['source'] == 'la') {
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = CommonService::escapeHtml(ucwords($aRow['facilityname']));
                $row[] = CommonService::escapeHtml(isset($aRow['testingpointname']) && $aRow['testingpointname'] != "" ? $aRow['testingpointname'] : $aRow['testingpointtype']);
                $row[] = round((float) ($aRow['AUDIT_SCORE_PERCANTAGE']), 2);
                $row[] = CommonService::escapeHtml($aRow['avgMonthTesting'] ?? 0);
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
                $row[] = round((float) ($aRow['AUDIT_SCORE_PERCANTAGE']), 2);
            } elseif ($parameters['source'] == 'apspi') {
                $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                $row[] = round((float) ($aRow['PERSONAL_SCORE']), 2);
                $row[] = round((float) ($aRow['PHYSICAL_SCORE']), 2);
                $row[] = round((float) ($aRow['SAFETY_SCORE']), 2);
                $row[] = round((float) ($aRow['PRETEST_SCORE']), 2);
                $row[] = round((float) ($aRow['TEST_SCORE']), 2);
                $row[] = round((float) ($aRow['POST_SCORE']), 2);
                $row[] = round((float) ($aRow['EQA_SCORE']), 2);
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
        $output['avgAuditScore'] = ($iFilteredTotal > 0) ? round((float) ($summary['scoreTotal'] ?? 0) / $iFilteredTotal, 2) : 0;
        $output['levelZeroCount'] = (int) ($summary['levelZero'] ?? 0);
        $output['levelOneCount'] = (int) ($summary['levelOne'] ?? 0);
        $output['levelTwoCount'] = (int) ($summary['levelTwo'] ?? 0);
        $output['levelThreeCount'] = (int) ($summary['levelThree'] ?? 0);
        $output['levelFourCount'] = (int) ($summary['levelFour'] ?? 0);
        return $output;
    }

    public function fetchAllTestingPointType()
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $query = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->columns(array(new Expression('DISTINCT(testingPointType) as testingPointType')))
            ->group('testingPointType')
            ->order("testingPointType ASC");
        $queryStr = $sql->buildSqlString($query);

        return $this->db->fetchAllAssociative($queryStr);
    }

    public function fetchTestingPointTypeNamesByType($params)
    {
        $typeResult = [];
        if (isset($params['testingPointType']) && trim($params['testingPointType']) != '') {
            if ($params['testingPointType'] == 'other') {
                $column = 'DISTINCT(testingpointtype_other) as testingpointName';
            } else {
                $column = 'DISTINCT(testingpointname) as testingpointName';
            }
            $dbAdapter = $this->adapter;
            $sql = new Sql($this->adapter);
            $query = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                ->columns(array(new Expression($column)))
                ->where(array('testingpointtype' => $params['testingPointType']));
            $queryStr = $sql->buildSqlString($query);
            $typeResult = $this->db->fetchAllAssociative($queryStr);
        }
        return $typeResult;
    }

    public function fetchSpiV3FormUniqueLevelNames()
    {

        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $query = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
            ->columns(array('facilityname'))
            ->join(array('f' => 'spi_rt_3_facilities'), 'f.id=spiv3.facility', array('province', 'district'))
            ->where('f.province not like "" AND f.province is not null')
            ->group("province")
            ->order("province ASC");
        $queryStr = $sql->buildSqlString($query);
        //$query = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
        //                       ->columns(array(new Expression('DISTINCT(level_name) as level_name')))
        //                       ->order("level_name ASC");
        //$queryStr = $sql->buildSqlString($query);
        return $this->db->fetchAllAssociative($queryStr);
    }
    public function fetchDistrictData($params)
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $query = $sql->select()
            ->quantifier(\Laminas\Db\Sql\Select::QUANTIFIER_DISTINCT)
            ->from(array('f' => 'spi_rt_3_facilities'))
            ->columns(array('district'));
        if (!empty($params['province']) && is_array($params['province'])) {
            $query = $query->where(['f.province' => CommonService::inList($params['province'])])->where('f.district not like "" AND f.district is not null');
        } elseif (!empty($params['province']) && !is_array($params['province'])) {
            $query = $query->where(['f.province' => $params['province']])->where('f.district not like "" AND f.district is not null');
        } else {
            $query = $query->where('f.district not like "" AND f.district is not null');
        }
        $queryStr = $sql->buildSqlString($query);
        return $this->db->fetchAllAssociative($queryStr);
    }

    public function addValidateSpiv3Data($params)
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $vId = explode(",", $params['validateId']);
        // Only rows whose instance ID is new, the rows the page lists. Adding a
        // stored one would make a duplicate audit.
        $newQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3_temp'))
            ->where(['spiv3.id' => CommonService::inList($vId), 'spiv3.spi_data_status' => 0]);
        $newQueryStr = $sql->buildSqlString($newQuery); // Get the string of the Sql, instead of the Select-instance
        $totalResult = $this->db->fetchAllAssociative($newQueryStr);
        if ($totalResult) {
            foreach ($totalResult as $newResult) {
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
                    'AUDIT_SCORE_PERCANTAGE' => $newResult['AUDIT_SCORE_PERCANTAGE'],
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
                    'status' => $newResult['status'],
                );
                $dbAdapter = $this->adapter;
                $this->db->insert(self::TABLE, $this->quoteColumns($par));
                if ($this->db->lastInsertId() > 0) {
                    $spiv3Temp = new \Application\Model\SpiFormVer3TempTable($this->adapter);
                    $spiv3Temp->deleteById($newResult['id']);
                }
            }
        }
    }

    public function updateSpiv3FacilityInfo($id, $params)
    {
        if ($id > 0) {
            $data = array(
                'facilityid' => $params['facilityId'],
                'facilityname' => $params['facilityName'],
            );
            $this->updateAudits($data, array('facility' => $id));
        }
        return $id;
    }

    public function getLatestFormDate($projectId, $formId)
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $query = "SELECT MAX(today) as last_added_form_date FROM spi_form_v_3 WHERE central_project_id = ? AND central_form_id = ?";
        return $dbAdapter->query($query, [$projectId, $formId])->toArray();
    }

    public function fetchV3DetailsByMetaInstanceId($metaInstantId)
    {
        return Dbal::connection($this->adapter)->fetchAssociative(
            'SELECT * FROM spi_form_v_3 WHERE `meta-instance-id` = ?',
            [$metaInstantId],
        );
    }
}

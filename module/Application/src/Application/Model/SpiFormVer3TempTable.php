<?php

namespace Application\Model;

use App\Db\Dbal;
use Application\Session\Container;
use Application\Service\CommonService;
use Doctrine\DBAL\Connection;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;

class SpiFormVer3TempTable
{
    private readonly Connection $db;

    public function __construct(private readonly Adapter $adapter)
    {
        $this->db = Dbal::connection($adapter);
    }

    /** Stores one row of an uploaded sheet. The sheet's headers name the columns. */
    public function insert(array $data): int
    {
        // DBAL's insert() does not quote column names, and these hold hyphens.
        $quoted = [];
        foreach ($data as $column => $value) {
            $quoted[$this->db->quoteSingleIdentifier((string) $column)] = $value;
        }
        return $this->db->insert('spi_form_v_3_temp', $quoted);
    }

    public function deleteById($id): int
    {
        return $this->db->delete('spi_form_v_3_temp', ['id' => $id]);
    }

    /** Clears the previous upload. */
    public function deleteAll(): int
    {
        return $this->db->executeStatement('DELETE FROM spi_form_v_3_temp');
    }

    public function fetchAllValidateSpiv3Details($parameters)
    {
        $loginContainer = new Container('credo');
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
        $aColumns = ['id', 'facilityname', 'auditroundno', "DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", 'testingpointname', 'testingpointtype', 'level', 'affiliation', 'AUDIT_SCORE_PERCANTAGE', 'status'];
        $orderColumns = ['id', 'facilityname', 'auditroundno', 'assesmentofaudit', 'testingpointname', 'testingpointtype', 'level', 'affiliation', 'AUDIT_SCORE_PERCANTAGE', 'status'];

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
        // The builder still writes the search and the sort. DBAL runs the SQL.
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
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3_temp'))
            ->where('spiv3.status != "deleted" and spiv3.spi_data_status=0');
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

        $rResult = $this->db->fetchAllAssociative($sql->buildSqlString($sQuery));

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $iFilteredTotal = (int) $this->db->fetchOne('SELECT COUNT(*) FROM (' . $sql->buildSqlString($sQuery) . ') AS filtered');

        /* Total data set length */
        $iTotal = (int) $this->db->fetchOne("SELECT COUNT(*) FROM spi_form_v_3_temp WHERE status != 'deleted' AND spi_data_status = 0");
        $output = array(
            "sEcho" => (int) $parameters['sEcho'],
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );

        $role = $loginContainer->roleCode;
        foreach ($rResult as $aRow) {
            $row = [];
            $level = isset($aRow['level_other']) && $aRow['level_other'] != "" ? " - " . $aRow['level_other'] : '';
            $row[] = '<input type="checkbox" class="checkSpiv3Data" name="chk[]" id="chk' . (int) $aRow['id'] . '"  value="' . (int) $aRow['id'] . '" onclick="getValidateId(this);"  />';
            $row[] = CommonService::escapeHtml($aRow['facilityname']);
            $row[] = CommonService::escapeHtml($aRow['auditroundno']);
            $row[] = CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
            $row[] = CommonService::escapeHtml(isset($aRow['testingpointname']) && $aRow['testingpointname'] != "" ? $aRow['testingpointname'] : $aRow['testingpointtype']);
            $row[] = CommonService::escapeHtml($aRow['testingpointtype']);
            $row[] = CommonService::escapeHtml($aRow['level'] . $level);
            $row[] = CommonService::escapeHtml($aRow['affiliation']);
            $row[] = round((float) ($aRow['AUDIT_SCORE_PERCANTAGE'] ?? 0), 2);
            $row[] = CommonService::escapeHtml(ucwords($aRow['status']));
            $output['aaData'][] = $row;
        }
        //get count of exist data
        $output['totalData'] = $this->db->fetchOne("SELECT COUNT(*) FROM spi_form_v_3 WHERE status != 'deleted'");
        //get count of new data
        $output['newData'] = $this->db->fetchOne('SELECT COUNT(id) FROM spi_form_v_3_temp');
        return $output;
    }
}

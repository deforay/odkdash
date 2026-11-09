<?php

namespace Application\Model;

use App\Db\DataTables;
use Application\Session\Container;
use Application\Service\CommonService;
use Doctrine\DBAL\Connection;

class SpiFormVer3TempTable
{
    private readonly Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
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
        // The columns the list shows, in order: $aColumns to search, $orderColumns to sort.
        $aColumns = ['id', 'facilityname', 'auditroundno', "DATE_FORMAT(assesmentofaudit,'%d-%b-%Y')", 'testingpointname', 'testingpointtype', 'level', 'affiliation', 'AUDIT_SCORE_PERCANTAGE', 'status'];
        $orderColumns = ['id', 'facilityname', 'auditroundno', 'assesmentofaudit', 'testingpointname', 'testingpointtype', 'level', 'affiliation', 'AUDIT_SCORE_PERCANTAGE', 'status'];

        $where = "status != 'deleted' AND spi_data_status = 0";
        $values = [];
        [$search, $searchValues] = DataTables::search($aColumns, $parameters);
        if ($search !== '') {
            $where .= ' AND ' . $search;
            $values = $searchValues;
        }
        $sql = 'SELECT * FROM spi_form_v_3_temp WHERE ' . $where;
        $order = DataTables::order($orderColumns, $parameters);
        if ($order !== '') {
            $sql .= ' ORDER BY ' . $order;
        }

        $rResult = $this->db->fetchAllAssociative($sql . DataTables::limit($parameters), $values);
        $iFilteredTotal = (int) $this->db->fetchOne('SELECT COUNT(*) FROM spi_form_v_3_temp WHERE ' . $where, $values);
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

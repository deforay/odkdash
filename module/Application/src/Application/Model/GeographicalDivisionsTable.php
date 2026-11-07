<?php

namespace Application\Model;

use App\Db\Dbal;
use Application\Session\Container;
use Application\Service\CommonService;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;

/**
 * Provinces and districts. A province has geo_parent 0. A district has
 * the province's geo_id in geo_parent. geo_parent is a varchar, so each
 * query keeps its own comparison: `= '0'` and `= 0` differ for a
 * non-numeric value.
 */
class GeographicalDivisionsTable
{
    private readonly Connection $db;

    public function __construct(private readonly Adapter $adapter)
    {
        $this->db = Dbal::connection($adapter);
    }

    public function addProvinceDetails($params)
    {
        if (trim($params['provinceName']) != "") {
            return $this->insertDivision($params['provinceName'], $params['provinceCode'], 0, $params['status']);
        }
    }

    public function updateProvinceDetails($params)
    {
        if (trim($params['provinceName']) != "" && trim($params['provinceId']) != "") {
            $provinceId = base64_decode($params['provinceId']);
            $this->db->update('geographical_divisions', [
                'geo_name' => $params['provinceName'],
                'geo_code' => $params['provinceCode'],
                'geo_status' => $params['status'],
                'geo_parent' => 0,
                'updated_datetime' => CommonService::getDateTime(),
            ], ['geo_id' => $provinceId]);
            return $provinceId;
        }
    }

    public function fetchAllProvinceDetails($parameters, $acl)
    {
        $aColumns = ['geo_name', 'geo_code', 'geo_status'];

        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from('geographical_divisions')->where("(geo_parent = 0 OR geo_parent IS NULL)");
        $sWhere = CommonService::dataTablesSearch($aColumns, $parameters);
        if ($sWhere !== null) {
            $sQuery->where($sWhere);
        }
        $this->orderAndPage($sQuery, $aColumns, $parameters);

        $rResult = $this->db->fetchAllAssociative($sql->buildSqlString($sQuery));

        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $iFilteredTotal = (int) $this->db->fetchOne('SELECT COUNT(*) FROM (' . $sql->buildSqlString($sQuery) . ') AS filtered');
        $iTotal = (int) $this->db->fetchOne('SELECT COUNT(*) FROM geographical_divisions WHERE geo_parent = 0');

        $output = [
            "sEcho" => (int) $parameters['sEcho'],
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => []
        ];

        $loginContainer = new Container('credo');
        $update = (bool) $acl->isAllowed($loginContainer->roleCode, 'Application\Controller\ProvincesController', 'edit');
        foreach ($rResult as $aRow) {
            $row = [];
            $row[] = CommonService::escapeHtml(ucwords($aRow['geo_name']));
            $row[] = CommonService::escapeHtml($aRow['geo_code']);
            $row[] = CommonService::escapeHtml(ucwords($aRow['geo_status']));
            if ($update) {
                $row[] = '<a href="/provinces/edit/' . base64_encode($aRow['geo_id']) . '" style="margin-right: 2px;" title="Edit"><i class="fa fa-pencil"> Edit</i></a>';
            }
            $output['aaData'][] = $row;
        }
        return $output;
    }

    /** The province or district, as the edit pages read it. */
    public function getProvinceDetails($id): ?\ArrayObject
    {
        $row = $this->db->fetchAssociative('SELECT * FROM geographical_divisions WHERE geo_id = ?', [(int) $id]);
        return $row ? new \ArrayObject($row, \ArrayObject::ARRAY_AS_PROPS) : null;
    }

    public function fetchAllActiveProvinces()
    {
        return $this->activeDivisions('geo_parent = 0 OR geo_parent IS NULL', 'province');
    }

    public function fetchAllProvinces()
    {
        return $this->activeDivisions("geo_parent = '0'");
    }

    public function addDistrictDetails($params)
    {
        if (trim($params['districtName']) != "" && trim($params['province']) != "") {
            return $this->insertDivision($params['districtName'], $params['districtCode'], base64_decode($params['province']), $params['status']);
        }
    }

    public function updateDistrictDetails($params)
    {
        if (trim($params['districtName']) != "" && trim($params['districtId']) != "" && trim($params['province']) != "") {
            $districtId = base64_decode($params['districtId']);
            $this->db->update('geographical_divisions', [
                'geo_name' => $params['districtName'],
                'geo_code' => $params['districtCode'],
                'geo_parent' => base64_decode($params['province']),
                'geo_status' => $params['status'],
                'updated_datetime' => CommonService::getDateTime(),
            ], ['geo_id' => $districtId]);
            return $districtId;
        }
    }

    public function fetchAllDistrictDetails($parameters, $acl)
    {
        $aColumns = ['d.geo_name', 'd.geo_code', 'p.geo_name', 'd.geo_status'];

        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(['d' => 'geographical_divisions'])
            ->join(['p' => 'geographical_divisions'], 'p.geo_id=d.geo_parent', ['province' => 'geo_name'])
            ->where("d.geo_parent!='0'");
        $sWhere = CommonService::dataTablesSearch($aColumns, $parameters);
        if ($sWhere !== null) {
            $sQuery->where($sWhere);
        }
        $this->orderAndPage($sQuery, $aColumns, $parameters);

        $rResult = $this->db->fetchAllAssociative($sql->buildSqlString($sQuery));

        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $iFilteredTotal = (int) $this->db->fetchOne('SELECT COUNT(*) FROM (' . $sql->buildSqlString($sQuery) . ') AS filtered');
        $iTotal = (int) $this->db->fetchOne('SELECT COUNT(*) FROM geographical_divisions WHERE geo_parent > 0');

        $output = [
            "sEcho" => (int) $parameters['sEcho'],
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => []
        ];

        $loginContainer = new Container('credo');
        $update = (bool) $acl->isAllowed($loginContainer->roleCode, 'Application\Controller\ProvincesController', 'edit');
        foreach ($rResult as $aRow) {
            $row = [];
            $row[] = CommonService::escapeHtml(ucwords($aRow['geo_name']));
            $row[] = CommonService::escapeHtml($aRow['geo_code']);
            $row[] = CommonService::escapeHtml(ucwords($aRow['province']));
            $row[] = CommonService::escapeHtml(ucwords($aRow['geo_status']));
            if ($update) {
                $row[] = '<a href="/district/edit/' . base64_encode($aRow['geo_id']) . '" style="margin-right: 2px;" title="Edit"><i class="fa fa-pencil"> Edit</i></a>';
            }
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function fetchAllActiveDistricts()
    {
        return $this->activeDivisions('geo_parent != 0', 'district');
    }

    public function fetchAllDistricts()
    {
        return $this->activeDivisions('geo_parent != 0');
    }

    public function fetchAllMappedLocations()
    {
        $result['provinces'] = $this->activeDivisions("geo_parent = '0'", 'province');
        $result['districts'] = $this->activeDivisions('geo_parent != 0', 'district');

        $countriesTable = new CountriesTable($this->adapter);
        $result['countries'] = $countriesTable->fetchAllMapedCountries();

        return $result;
    }

    public function fetchAllDistrictByProvince($provinceId)
    {
        if ($provinceId != "") {
            return $this->db->fetchAllAssociative(
                "SELECT * FROM geographical_divisions WHERE geo_parent = ? AND geo_status = 'active'",
                [$provinceId]
            );
        }
    }

    /** Every district of the provinces picked in a multi-select. */
    public function fetchAllDistrictByProvinceAsMultiple($provinceId)
    {
        if ($provinceId != "") {
            return $this->db->fetchAllAssociative(
                'SELECT * FROM geographical_divisions WHERE geo_parent IN (?)',
                [array_map('strval', (array) $provinceId)],
                [ArrayParameterType::STRING]
            );
        }
    }

    /** The geo_id of the first province whose name contains $provinceName, created if there is none. */
    public function addProvinceByFacility($provinceName)
    {
        $geoId = $this->firstIdByNameLike($provinceName);
        return $geoId !== false ? $geoId : $this->insertDivision($provinceName, $provinceName, 0, 'active');
    }

    /** The geo_id of the first division whose name contains $districtName, created under $provinceId if there is none. */
    public function addDistrictByFacility($provinceId, $districtName)
    {
        $geoId = $this->firstIdByNameLike($districtName);
        return $geoId !== false ? $geoId : $this->insertDivision($districtName, $districtName, $provinceId, 'active');
    }

    public function getProvinceDistrict($params)
    {
        $found = $this->db->fetchOne('SELECT 1 FROM geographical_divisions WHERE geo_name = ? LIMIT 1', [$params['geo_name']]);
        return $found !== false ? 1 : 0;
    }

    /**
     * The district whose name contains $district and its province. If there
     * is none, it adds one with the name as its own geo_parent.
     */
    public function checkDistrict($district)
    {
        $row = $this->db->fetchAssociative(
            'SELECT geo_id, geo_parent FROM geographical_divisions WHERE geo_name LIKE ? LIMIT 1',
            ['%' . $district . '%']
        );
        if (!$row) {
            $insertId = $this->insertDivision($district, $district, $district, 'active');
            $row = $this->db->fetchAssociative('SELECT geo_id, geo_parent FROM geographical_divisions WHERE geo_id = ?', [$insertId]);
        }
        return ['district' => $row['geo_id'], 'province' => $row['geo_parent']];
    }

    /**
     * Active divisions that match $parentSql, by name. With a $mappingType,
     * a user mapped at that level sees only the divisions mapped to them.
     */
    private function activeDivisions(string $parentSql, ?string $mappingType = null): array
    {
        $sql = "SELECT * FROM geographical_divisions WHERE geo_status = 'active' AND ($parentSql)";
        $values = [];
        $types = [];

        $loginContainer = new Container('credo');
        if (
            $mappingType !== null
            && !empty($loginContainer->userMappedIds)
            && is_array($loginContainer->userMappedIds)
            && $loginContainer->userMappingType == $mappingType
        ) {
            $sql .= ' AND geo_id IN (?)';
            $values[] = CommonService::inList($loginContainer->userMappedIds);
            $types[] = ArrayParameterType::STRING;
        }

        return $this->db->fetchAllAssociative($sql . ' ORDER BY geo_name ASC', $values, $types);
    }

    private function firstIdByNameLike(string $name): mixed
    {
        return $this->db->fetchOne('SELECT geo_id FROM geographical_divisions WHERE geo_name LIKE ? LIMIT 1', ['%' . $name . '%']);
    }

    private function insertDivision($name, $code, $parent, $status): int
    {
        $currentDateTime = CommonService::getDateTime();
        $this->db->insert('geographical_divisions', [
            'geo_name' => $name,
            'geo_code' => $code,
            'geo_parent' => $parent,
            'geo_status' => $status,
            'created_by' => (new Container('credo'))->userId,
            'created_on' => $currentDateTime,
            'updated_datetime' => $currentDateTime,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** The DataTables sort and page, as the lists have always read them. */
    private function orderAndPage($sQuery, array $aColumns, $parameters): void
    {
        if (isset($parameters['iSortCol_0'])) {
            $sOrder = "";
            for ($i = 0; $i < (int) $parameters['iSortingCols']; $i++) {
                if ($parameters['bSortable_' . (int) $parameters['iSortCol_' . $i]] == "true") {
                    $sOrder .= $aColumns[(int) $parameters['iSortCol_' . $i]] . " " . ($parameters['sSortDir_' . $i]) . ",";
                }
            }
            $sOrder = substr_replace($sOrder, "", -1);
            if ($sOrder != "") {
                $sQuery->order($sOrder);
            }
        }
        if (isset($parameters['iDisplayStart']) && $parameters['iDisplayLength'] != '-1') {
            $sQuery->limit((int) $parameters['iDisplayLength']);
            $sQuery->offset((int) $parameters['iDisplayStart']);
        }
    }
}

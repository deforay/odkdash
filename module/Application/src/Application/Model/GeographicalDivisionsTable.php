<?php

namespace Application\Model;

use App\Db\DataTables;
use Application\Session\Container;
use Application\Service\CommonService;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

/**
 * Provinces and districts. A province has geo_parent 0. A district has
 * the province's geo_id in geo_parent. geo_parent is a varchar, so each
 * query keeps its own comparison: `= '0'` and `= 0` differ for a
 * non-numeric value.
 */
class GeographicalDivisionsTable
{
    private readonly Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
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

        [$search, $values] = DataTables::search($aColumns, $parameters);
        $where = '(geo_parent = 0 OR geo_parent IS NULL)' . ($search !== '' ? " AND $search" : '');
        $order = DataTables::order($aColumns, $parameters);

        $rResult = $this->db->fetchAllAssociative(
            "SELECT * FROM geographical_divisions WHERE $where" . ($order !== '' ? " ORDER BY $order" : '') . DataTables::limit($parameters),
            $values
        );
        $iFilteredTotal = (int) $this->db->fetchOne("SELECT COUNT(*) FROM geographical_divisions WHERE $where", $values);
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

        [$search, $values] = DataTables::search($aColumns, $parameters);
        $from = 'FROM geographical_divisions AS d INNER JOIN geographical_divisions AS p ON p.geo_id = d.geo_parent'
            . " WHERE d.geo_parent != '0'" . ($search !== '' ? " AND $search" : '');
        $order = DataTables::order($aColumns, $parameters);

        $rResult = $this->db->fetchAllAssociative(
            "SELECT d.*, p.geo_name AS province $from" . ($order !== '' ? " ORDER BY $order" : '') . DataTables::limit($parameters),
            $values
        );
        $iFilteredTotal = (int) $this->db->fetchOne("SELECT COUNT(*) $from", $values);
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

        $countriesTable = new CountriesTable($this->db);
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

    /** The geo_id of the province named $provinceName, created if there is none. */
    public function addProvinceByFacility($provinceName)
    {
        $geoId = $this->db->fetchOne(
            "SELECT geo_id FROM geographical_divisions WHERE geo_name = ? AND geo_parent = '0' ORDER BY geo_id LIMIT 1",
            [$provinceName]
        );
        return $geoId !== false ? $geoId : $this->insertDivision($provinceName, $provinceName, 0, 'active');
    }

    /** The geo_id of the district named $districtName under $provinceId, created there if there is none. */
    public function addDistrictByFacility($provinceId, $districtName)
    {
        $geoId = $this->db->fetchOne(
            'SELECT geo_id FROM geographical_divisions WHERE geo_name = ? AND geo_parent = ? ORDER BY geo_id LIMIT 1',
            [$districtName, (string) $provinceId]
        );
        return $geoId !== false ? $geoId : $this->insertDivision($districtName, $districtName, $provinceId, 'active');
    }

    public function getProvinceDistrict($params)
    {
        $found = $this->db->fetchOne('SELECT 1 FROM geographical_divisions WHERE geo_name = ? LIMIT 1', [$params['geo_name']]);
        return $found !== false ? 1 : 0;
    }

    /**
     * The district named $district and its province, or null if no district
     * has that name. An unknown district is not added, because its province
     * is unknown too.
     */
    public function checkDistrict($district)
    {
        $row = $this->db->fetchAssociative(
            "SELECT geo_id, geo_parent FROM geographical_divisions WHERE geo_name = ? AND geo_parent <> '0' ORDER BY geo_id LIMIT 1",
            [$district]
        );
        return $row ? ['district' => $row['geo_id'], 'province' => $row['geo_parent']] : null;
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
}

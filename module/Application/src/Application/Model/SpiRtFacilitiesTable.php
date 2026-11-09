<?php

namespace Application\Model;

use App\Db\DataTables;
use App\Db\Dbal;
use Application\Session\Container;
use Application\Service\CommonService;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Laminas\Db\Adapter\Adapter;

class SpiRtFacilitiesTable
{
    private readonly Connection $db;

    public function __construct(private readonly Adapter $adapter)
    {
        $this->db = Dbal::connection($adapter);
    }

    /**
     * The user's mapped locations as SQL on spi_rt_3_facilities, and its
     * values. A Super Admin has no mapping, so this is '' and every facility
     * passes. A mapping type this code does not know matches nothing.
     *
     * @return array{0: string, 1: list<string>}
     */
    public static function mappingCondition(string $alias): array
    {
        $loginContainer = new Container('credo');
        if (empty($loginContainer->userMappedIds) || !is_array($loginContainer->userMappedIds) || $loginContainer->userMappingType == '') {
            return ['', []];
        }
        $column = match ($loginContainer->userMappingType) {
            'country', 'province', 'district' => $alias . '.' . $loginContainer->userMappingType,
            default => null,
        };
        if ($column === null) {
            return ['1 = 0', []];
        }
        $ids = CommonService::inList($loginContainer->userMappedIds);
        return [$column . ' IN (' . implode(', ', array_fill(0, count($ids), '?')) . ')', $ids];
    }

    /** Adds a facility and returns its id. */
    public function insert(array $data): int
    {
        $this->db->insert('spi_rt_3_facilities', $data);
        return (int) $this->db->lastInsertId();
    }

    /** Updates the facilities that match every column in $where. */
    public function update(array $data, array $where): int
    {
        return (int) $this->db->update('spi_rt_3_facilities', $data, $where);
    }

    /**
     * Resolve the facility an audit belongs to and record it on the form row.
     *
     * Every location filter joins facilities on spi_form_v_6.facility, so an
     * audit whose facility column is empty is invisible to a mapped user. That
     * column is the only link — matching on facilityname was dropped because
     * names are not unique and facilityid is not a facility key at all.
     *
     * $createIfMissing is what separates ingest from approval. On ingest we
     * only link to a facility that already exists; a facility the auditor has
     * named for the first time is not promoted into the master list until the
     * audit is approved, which is the behaviour this has always had.
     *
     * Returns the facility id, or null when there is nothing to link to.
     */
    public function addFacilityBasedOnForm($formId, $formVersion = 3, $createIfMissing = true)
    {
        $table = match ((int) $formVersion) {
            3 => 'spi_form_v_3',
            5, 6 => 'spi_form_v_6',
            default => throw new \InvalidArgumentException("Unknown form version '$formVersion'"),
        };

        $result = $this->db->fetchAssociative(
            "SELECT formId, facilityname, facilityid, district, Latitude, Longitude FROM $table WHERE id = ?",
            [$formId]
        );
        if (!$result || trim((string) ($result['facilityname'] ?? '')) === '') {
            // Nothing to key a facility on. Left unlinked deliberately: one
            // blank-named row would silently collect every such audit.
            return null;
        }

        // Lowest id wins, so a duplicated facility name always resolves the
        // same way here as it does in the 1.2.4 backfill.
        $facilityId = $this->db->fetchOne(
            'SELECT id FROM spi_rt_3_facilities WHERE facility_name = ? ORDER BY id ASC LIMIT 1',
            [$result['facilityname']]
        );

        if ($facilityId === false) {
            if (!$createIfMissing) {
                return null;
            }
            // The columns hold division ids. A district name that matches no
            // district stays unresolved, so a name that reads as a number
            // cannot be taken for another district's id.
            $district = null;
            $province = 'Unknown';
            if (isset($result['district']) && $result['district'] != '') {
                $geoTable = new GeographicalDivisionsTable($this->adapter);
                $res = $geoTable->checkDistrict(trim($result['district']));
                if ($res) {
                    $district = $res['district'];
                    $province = $res['province'];
                }
            }
            $facilityId = $this->insert([
                'facility_id' => $result['facilityid'] ?? null,
                'facility_name' => $result['facilityname'] ?? null,
                'country' => $this->getInstanceCountryId(),
                'province' => $province,
                'district' => $district,
                'latitude' => $result['Latitude'] ?? null,
                'longitude' => $result['Longitude'] ?? null
            ]);
        }

        $this->db->update($table, ['facility' => $facilityId], ['id' => $formId]);

        return $facilityId;
    }

    /**
     * Country id this instance serves, resolved from the country-name global config.
     *
     * Facilities created from a form have no country of their own to go on, but a
     * deployment only ever covers one, so the instance name is the source. Returns
     * null when it does not name a country, which is no worse than the NULL that
     * used to be written unconditionally.
     */
    public function getInstanceCountryId()
    {
        $gTable = new GlobalTable($this->adapter);
        $countryName = trim((string) $gTable->getGlobalValue('country-name'));
        if ($countryName === '') {
            return null;
        }
        $countryId = $this->db->fetchOne('SELECT country_id FROM countries WHERE country_name = ?', [$countryName]);

        return $countryId !== false ? $countryId : null;
    }

    public function addFacilityDetails($params)
    {
        if (isset($params['facilityId']) && trim($params['facilityId']) != "") {
            $geoTable = new GeographicalDivisionsTable($this->adapter);
            $countryId = $provinceId = $districtId = null;
            //Add Country
            if (isset($params['country']) && trim($params['country']) != '') {
                $countryId = base64_decode($params['country']);
            }
            //Add Province
            if (isset($params['province']) && trim($params['province']) == 'other') {
                if (trim($params['provinceName']) != "") {
                    $provinceId = $geoTable->addProvinceByFacility(trim($params['provinceName']));
                }
            } else if (trim($params['province']) != '' && trim($params['province']) != 'other') {
                $provinceId = base64_decode($params['province']);
            }

            //Add District
            if (isset($params['district']) && trim($params['district']) == 'other') {
                if (trim($params['districtName']) != "") {
                    $districtId = $geoTable->addDistrictByFacility($provinceId, trim($params['districtName']));
                }
            } else if (trim($params['district']) != '' && trim($params['district']) != 'other') {
                $districtId = base64_decode($params['district']);
            }

            return $this->insert([
                'facility_id' => $params['facilityId'],
                'facility_name' => $params['facilityName'],
                'email' => $params['email'],
                'contact_person' => $params['contactPerson'],
                'district' => $districtId,
                'province' => $provinceId,
                'country' => $countryId,
                'latitude' => $params['latitude'],
                'longitude' => $params['longitude']
            ]);
        }
    }

    public function updateFacilityDetails($params)
    {
        if (isset($params['rowId']) && trim($params['rowId']) != "") {
            $spiv3Db = new SpiFormVer3Table($this->adapter);
            $spiv6Db = new SpiFormVer6Table($this->adapter);
            $rowId = base64_decode($params['rowId']);

            $geoTable = new GeographicalDivisionsTable($this->adapter);
            //Add Country
            if (isset($params['country']) && trim($params['country']) != '') {
                $params['country'] = base64_decode($params['country']);
            }
            //Add Province
            if (isset($params['province']) && trim($params['province']) == 'other') {
                if (trim($params['provinceName']) != "") {
                    $params['province'] = $geoTable->addProvinceByFacility(trim($params['provinceName']));
                }
            } else if (trim($params['province']) != '' && trim($params['province']) != 'other') {
                $params['province'] = base64_decode($params['province']);
            }

            //Add District
            if (isset($params['district']) && trim($params['district']) == 'other') {
                if (trim($params['districtName']) != "") {
                    $params['district'] = $geoTable->addDistrictByFacility($params['province'], trim($params['districtName']));
                }
            } else if (trim($params['district']) != '' && trim($params['district']) != 'other') {
                $params['district'] = base64_decode($params['district']);
            }
            $province = (isset($params['province']) && trim($params['province']) != '') ? $params['province'] : '';
            $district = (isset($params['district']) && trim($params['district']) != '') ? $params['district'] : '';
            $country = (isset($params['country']) && trim($params['country']) != '') ? $params['country'] : '';
            $this->update([
                'facility_id' => $params['facilityId'],
                'facility_name' => $params['facilityName'],
                'email' => $params['email'],
                'contact_person' => $params['contactPerson'],
                'district' => $district,
                'province' => $province,
                'country' => $country,
                'latitude' => $params['latitude'],
                'longitude' => $params['longitude']
            ], ['id' => $rowId]);
            $spiv3Db->updateSpiv3FacilityInfo($rowId, $params);
            $spiv6Db->updateSpiv6FacilityInfo($rowId, $params);
            return $rowId;
        }
    }

    public function fetchAllFacilities($parameters, $acl)
    {
        $queryContainer = new Container('query');
        $aColumns = ['facility_id', 'facility_name', 'email', 'contact_person'];

        // A mapped user sees the facilities in their locations. A user with
        // no mapping sees every facility.
        [$scope, $scopeValues] = self::mappingCondition('spirt3');
        $scopeWhere = "spirt3.status != 'deleted'" . ($scope !== '' ? " AND $scope" : '');

        [$search, $values] = DataTables::search($aColumns, $parameters);
        $values = array_merge($scopeValues, $values);
        $order = DataTables::order($aColumns, $parameters);
        $from = 'FROM spi_rt_3_facilities AS spirt3'
            . ' LEFT JOIN geographical_divisions AS g ON spirt3.province = g.geo_id'
            . ' LEFT JOIN geographical_divisions AS gd ON spirt3.district = gd.geo_id'
            . " WHERE $scopeWhere" . ($search !== '' ? " AND $search" : '');
        $select = 'SELECT spirt3.id, spirt3.facility_id, spirt3.facility_name, spirt3.email, spirt3.contact_person,'
            . ' spirt3.latitude, spirt3.longitude, spirt3.status, g.geo_name AS province, gd.geo_name AS district '
            . $from . ($order !== '' ? " ORDER BY $order" : '');

        $rResult = $this->db->fetchAllAssociative($select . DataTables::limit($parameters), $values);

        // The export runs this query again without the page, so it has every
        // facility the search matches.
        $queryContainer->exportAllFacilityQuery = ['sql' => $select, 'values' => $values];
        $iFilteredTotal = (int) $this->db->fetchOne("SELECT COUNT(*) $from", $values);
        $iTotal = (int) $this->db->fetchOne("SELECT COUNT(*) FROM spi_rt_3_facilities AS spirt3 WHERE $scopeWhere", $scopeValues);

        $output = [
            "sEcho" => (int) $parameters['sEcho'],
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => []
        ];

        $loginContainer = new Container('credo');
        $update = (bool) $acl->isAllowed($loginContainer->roleCode, 'Application\Controller\FacilityController', 'edit');

        foreach ($rResult as $aRow) {
            $row = [];

            /* Both of these go into a JS argument list inside an onclick, so hand
             * quoting them broke on any facility whose name carries an apostrophe.
             * json_encode makes the JS string literal, escapeHtml makes that safe
             * inside the attribute, and the browser undoes the second on the way in. */
            $facilityId = CommonService::escapeHtml(json_encode((string) $aRow['facility_id']));
            $facilityName = CommonService::escapeHtml(json_encode((string) $aRow['facility_name']));
            $row[] = '<a href="javascript:void(0)" onclick="getTestingPoint(' . $facilityId . ',\'facilityId\');getAuditData(' . $facilityId . ',\'facilityid\');">' . CommonService::escapeHtml($aRow['facility_id']) . '</a>';
            $row[] = '<a href="javascript:void(0)" onclick="getTestingPoint(' . $facilityName . ',\'facilityName\');getAuditData(' . $facilityName . ',\'facilityname\');">' . CommonService::escapeHtml($aRow['facility_name']) . '</a>';
            $row[] = CommonService::escapeHtml($aRow['email']);
            $row[] = CommonService::escapeHtml($aRow['contact_person']);
            if ($update) {
                $row[] = '<a href="/facility/edit/' . base64_encode($aRow['id']) . '" title="Edit"><i class="fa fa-pencil"></i> Edit</a>';
            }
            $output['aaData'][] = $row;
        }
        return $output;
    }

    /** The facility, as the edit page reads it. */
    public function fetchFacility($id): ?\ArrayObject
    {
        return $this->facilityById((int) $id);
    }

    public function fetchFacilityList($strSearch)
    {
        $rows = $this->db->fetchAllAssociative(
            'SELECT id, facility_name FROM spi_rt_3_facilities WHERE facility_name LIKE ?',
            ['%' . $strSearch . '%']
        );
        $echoResult = [];
        foreach ($rows as $row) {
            $echoResult[] = ["id" => $row['id'], "name" => ucwords($row['facility_name'])];
        }
        return ["result" => $echoResult];
    }

    public function updateFacilityInfo($id, $params)
    {
        if ($id > 0) {
            $this->update($this->facilityInfo($params), ['id' => $id]);
        }
    }

    /** The id of the facility with this id or name, added if there is none. */
    public function addFacilityInfo($params)
    {
        $id = $this->db->fetchOne(
            'SELECT id FROM spi_rt_3_facilities WHERE (facility_id = ? OR facility_name = ?) LIMIT 1',
            [$params['testingFacilityId'], $params['testingFacilityName']]
        );
        return $id !== false ? $id : $this->insert($this->facilityInfo($params));
    }

    public function updateFacilityEmailAddress($params)
    {
        $result = 0;
        if (isset($params['facility']) && trim($params['facility']) != '') {
            $result = 1;
            $this->update(['email' => $params['emailAddress']], ['facility_name' => $params['facility']]);
        }
        return $result;
    }

    public function fetchFacilityProfileByAudit($ids)
    {
        return $this->facilityProfile($ids, 'spi_form_v_3', 'id, testingpointname, assesmentofaudit');
    }

    public function fetchFacilityProfileByAuditV6($ids)
    {
        return $this->facilityProfile($ids, 'spi_form_v_6', 'id, assesmentofaudit');
    }

    public function fetchProvinceList()
    {
        return $this->db->fetchAllAssociative('SELECT DISTINCT province AS name FROM spi_rt_3_facilities');
    }

    public function fetchDistrictList()
    {
        return $this->db->fetchAllAssociative('SELECT DISTINCT district AS name FROM spi_rt_3_facilities');
    }

    public function getSpiV3FormUniqueDistrict()
    {
        return $this->fetchDistrictList();
    }

    public function mapProvince($params)
    {
        $result = 0;
        $loginContainer = new Container('credo');
        $userName = $loginContainer->login;
        $eventTable = new EventLogTable($this->adapter);

        $geographicalDivisionsTable = new GeographicalDivisionsTable($this->adapter);
        $provinceId = null;
        $districtId = null;
        if (isset($params['province']) && trim($params['province']) == 'other') {
            if (trim($params['provinceName']) != "") {
                $provinceId = $geographicalDivisionsTable->addProvinceByFacility(trim($params['provinceName']));
            }
        } else if (trim($params['province']) != '' && trim($params['province']) != 'other') {
            $provinceId = base64_decode($params['province']);
        }

        //Add District
        if (isset($params['district']) && trim($params['district']) == 'other') {
            if (trim($params['districtName']) != "") {
                $districtId = $geographicalDivisionsTable->addDistrictByFacility($provinceId, trim($params['districtName']));
            }
        } else if (trim($params['district']) != '' && trim($params['district']) != 'other') {
            $districtId = base64_decode($params['district']);
        }

        if (isset($params['facility']) && count($params['facility']) > 0) {
            $result = 1;
            // Only what was chosen is written: a province or district left blank
            // keeps the facility's current value instead of clearing it.
            $data = array_filter(['province' => $provinceId, 'district' => $districtId], fn($value) => $value !== null);
            if ($data !== []) {
                foreach ($params['facility'] as $facilityName) {
                    $this->update($data, ['facility_name' => $facilityName]);
                }
            }

            $subject = '';
            $eventType = 'Map-Province';
            $action = $userName . ' has mapped Province ' . $params['province'];
            $resourceName = 'Map-Province';
            $eventTable->addEventLog($subject, $eventType, $action, $resourceName);
        }
        return $result;
    }

    public function fecthProvinceData($searchStr)
    {
        if (trim($searchStr) != "") {
            return $this->matchingValues('province', $searchStr);
        }
    }

    public function fecthDistrictData($searchStr)
    {
        if (trim($searchStr) != "") {
            return $this->matchingValues('district', $searchStr);
        }
    }

    /** The facility the V3 and V6 edit forms pick, as get-facility-details reads it. */
    public function fetchFacilityDetails($params): ?\ArrayObject
    {
        return $this->facilityById((int) base64_decode($params['id']));
    }

    public function fetchAllProvince()
    {
        return $this->db->fetchAllAssociative(
            "SELECT DISTINCT province FROM spi_rt_3_facilities
            WHERE province NOT LIKE '' AND province IS NOT NULL
            ORDER BY province ASC"
        );
    }

    /** Every district the facilities of the picked provinces have. No province picked means every district. */
    public function fetchDistrictByProvince($params)
    {
        if (isset($params['province']) && is_array($params['province'])) {
            $sql = "SELECT DISTINCT district FROM spi_rt_3_facilities WHERE district NOT LIKE '' AND district IS NOT NULL";
            $values = [];
            $types = [];
            if (!empty($params['province'])) {
                $sql .= ' AND province IN (?)';
                $values[] = CommonService::inList($params['province']);
                $types[] = ArrayParameterType::STRING;
            }
            return $this->db->fetchAllAssociative($sql . ' ORDER BY district ASC', $values, $types);
        }
    }

    private function facilityById(int $id): ?\ArrayObject
    {
        $row = $this->db->fetchAssociative('SELECT * FROM spi_rt_3_facilities WHERE id = ?', [$id]);
        return $row ? new \ArrayObject($row, \ArrayObject::ARRAY_AS_PROPS) : null;
    }

    /** The columns an audit form sends for its facility. */
    private function facilityInfo($params): array
    {
        return [
            'facility_id' => $params['testingFacilityId'],
            'facility_name' => $params['testingFacilityName'],
            'email' => $params['email'],
            'contact_person' => $params['contactPerson'],
            'district' => (isset($params['district']) && trim($params['district']) != '') ? $params['district'] : '',
            'province' => (isset($params['province']) && trim($params['province']) != '') ? $params['province'] : '',
            'latitude' => $params['latitude'],
            'longitude' => $params['longitude']
        ];
    }

    /**
     * The facility named on the audit, and that facility's approved audits,
     * for the email pages. The facility is null when no facility has the name.
     */
    private function facilityProfile($ids, string $formTable, string $auditColumns): array
    {
        if (!isset($ids) || trim($ids) == '') {
            return [];
        }
        $fResult = [];
        $auditsResult = [];
        $facilityName = $this->db->fetchOne("SELECT facilityname FROM $formTable WHERE id = ?", [base64_decode($ids)]);
        if ($facilityName !== false) {
            $auditsResult = $this->db->fetchAllAssociative(
                "SELECT $auditColumns FROM $formTable WHERE facilityname = ? AND status = 'approved'",
                [$facilityName]
            );
            $row = $this->db->fetchAssociative(
                'SELECT facility_name, email FROM spi_rt_3_facilities WHERE facility_name = ? LIMIT 1',
                [$facilityName]
            );
            $fResult = $row ? new \ArrayObject($row, \ArrayObject::ARRAY_AS_PROPS) : null;
        }
        return ['fResult' => $fResult, 'auditsResult' => $auditsResult];
    }

    /**
     * The distinct values of $column that contain $searchStr, for a select2
     * box. With no match, the search itself is the one choice.
     */
    private function matchingValues(string $column, string $searchStr): array
    {
        $values = $this->db->fetchFirstColumn(
            "SELECT $column FROM spi_rt_3_facilities WHERE $column LIKE ? GROUP BY $column",
            ['%' . $searchStr . '%']
        );
        $echoResult = [];
        foreach ($values as $value) {
            $echoResult[] = ["id" => $value, "text" => ucwords($value)];
        }
        if (count($echoResult) == 0) {
            $echoResult[] = ["id" => $searchStr, "text" => $searchStr];
        }
        return ["result" => $echoResult];
    }
}

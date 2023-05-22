<?php

namespace Application\Model;

use Laminas\Session\Container;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Db\TableGateway\AbstractTableGateway;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Countries
 *
 * @author ilahir
 */
class SpiRtFacilitiesTable extends AbstractTableGateway
{

    protected $table = 'spi_rt_3_facilities';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function addFacilityBasedOnForm($formId, $formVersion = 3)
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        if ($formVersion == 3) {
            $table = 'spi_form_v_3';
        } elseif ($formVersion == 5) {
            $table = 'spi_form_v_5';
        } elseif ($formVersion == 6) {
            $table = 'spi_form_v_6';
        }
        
        $query = $sql->select()->from($table)
            ->columns(array('formId', 'facilityname', 'facilityid', 'Latitude', 'Longitude'))
            ->where(array('id' => $formId));

        $queryStr = $sql->buildSqlString($query);
        $result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
        if ($result != "") {

            $fQuery = $sql->select()->from('spi_rt_3_facilities')->where(array('facility_name' => $result['facilityname']));

            $fQueryStr = $sql->buildSqlString($fQuery);
            $fResult = $dbAdapter->query($fQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
            if ($fResult == "") {
                $data = array(
                    'facility_id' => $result['facilityid'],
                    'facility_name' => $result['facilityname'],
                    'latitude' => $result['Latitude'],
                    'longitude' => $result['Longitude']
                );
                return $this->insert($data);
            }
        }
    }

    public function addFacilityDetails($params)
    {
        if (isset($params['facilityId']) && trim($params['facilityId']) != "") {
            $province = (isset($params['province']) && trim($params['province']) != '') ? $params['province'] : '';
            $district = (isset($params['district']) && trim($params['district']) != '') ? $params['district'] : '';
            $data = array(
                'facility_id' => $params['facilityId'],
                'facility_name' => $params['facilityName'],
                'email' => $params['email'],
                'contact_person' => $params['contactPerson'],
                'district' => $district,
                'province' => $province,
                'latitude' => $params['latitude'],
                'longitude' => $params['longitude']
            );
            $this->insert($data);
            return $lastInsertedId = $this->lastInsertValue;
        }
    }

    public function updateFacilityDetails($params)
    {
        if (isset($params['rowId']) && trim($params['rowId']) != "") {
            $dbAdapter = $this->adapter;
            $spiv3Db = new SpiFormVer3Table($dbAdapter);
            $spiv5Db = new SpiFormVer5Table($dbAdapter);
            $spiv6Db = new SpiFormVer6Table($dbAdapter);
            $rowId = base64_decode($params['rowId']);
            $province = (isset($params['province']) && trim($params['province']) != '') ? $params['province'] : '';
            $district = (isset($params['district']) && trim($params['district']) != '') ? $params['district'] : '';
            $data = array(
                'facility_id' => $params['facilityId'],
                'facility_name' => $params['facilityName'],
                'email' => $params['email'],
                'contact_person' => $params['contactPerson'],
                'district' => $district,
                'province' => $province,
                'latitude' => $params['latitude'],
                'longitude' => $params['longitude']
            );
            $this->update($data, array('id' => $rowId));
            //update spiv3 & spiv5 table facility info
            $spiv3Db->updateSpiv3FacilityInfo($rowId, $params);
            $spiv5Db->updateSpiv5FacilityInfo($rowId, $params);
            $spiv6Db->updateSpiv6FacilityInfo($rowId, $params);
            return $rowId;
        }
    }

    public function fetchAllFacilities($parameters, $acl)
    {

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
        $queryContainer = new Container('query');
        $aColumns = array('facility_id', 'facility_name', 'email', 'contact_person');
        $orderColumns = array('facility_id', 'facility_name', 'email', 'contact_person');

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
                    $sOrder .= $orderColumns[intval($parameters['iSortCol_' . $i])] . " " . ($parameters['sSortDir_' . $i]) . ",";
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
        $sql = new Sql($this->adapter);
        $startDate = "";
        $endDate = "";
        $sQuery = $sql->select()->from(array('spirt3' => 'spi_rt_3_facilities'))
            ->where('spirt3.status != "deleted"');

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

        $queryContainer->exportAllFacilityQuery = $sQuery;
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
        $tQuery =  $sql->select()->from(array('spirt3' => 'spi_rt_3_facilities'))
            ->where('spirt3.status != "deleted"');
        $tQueryStr = $sql->buildSqlString($tQuery); // Get the string of the Sql, instead of the Select-instance
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
        if ($acl->isAllowed($role, 'Application\Controller\FacilityController', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }

        foreach ($rResult as $aRow) {
            $row = array();

            $row[] = '<a href="javascript:void(0)" onclick="getTestingPoint(\'' . $aRow['facility_id'] . '\',\'facilityId\');getAuditData(\'' . $aRow['facility_id'] . '\',\'facilityid\');">' . $aRow['facility_id'] . '</a>';
            $row[] = '<a href="javascript:void(0)" onclick="getTestingPoint(\'' . $aRow['facility_name'] . '\',\'facilityName\');getAuditData(\'' . $aRow['facility_name'] . '\',\'facilityname\');">' . $aRow['facility_name'] . '</a>';
            $row[] = $aRow['email'];
            $row[] = $aRow['contact_person'];
            if ($update) {
                $edit = '<a href="/facility/edit/' . base64_encode($aRow['id']) . '" title="Edit"><i class="fa fa-pencil"></i> Edit</a>';
                $row[] = $edit;
            }
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function fetchFacility($id)
    {
        $row = $this->select(array('id' => (int) $id))->current();
        return $row;
    }

    public function fetchFacilityList($strSearch)
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $query = $sql->select()->from('spi_rt_3_facilities')->where("facility_name like '%$strSearch%'");
        $queryStr = $sql->buildSqlString($query);
        $result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE);
        $echoResult = array();
        foreach ($result as $row) {
            $echoResult[] = array("id" => $row['id'], "name" => ucwords($row['facility_name']));
        }
        return array("result" => $echoResult);
    }

    public function updateFacilityInfo($id, $params)
    {
        if ($id > 0) {
            $province = (isset($params['province']) && trim($params['province']) != '') ? $params['province'] : '';
            $district = (isset($params['district']) && trim($params['district']) != '') ? $params['district'] : '';
            $data = array(
                'facility_id' => $params['testingFacilityId'],
                'facility_name' => $params['testingFacilityName'],
                'email' => $params['email'],
                'contact_person' => $params['contactPerson'],
                'district' => $district,
                'province' => $province,
                'latitude' => $params['latitude'],
                'longitude' => $params['longitude']
            );
            $this->update($data, array('id' => $id));
        }
    }

    public function addFacilityInfo($params)
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $fQuery = $sql->select()->from('spi_rt_3_facilities')->where('facility_id = "' . $params['testingFacilityId'] . '" OR facility_name = "' . $params['testingFacilityName'] . '"');
        $fQueryStr = $sql->buildSqlString($fQuery);
        $fResult = $dbAdapter->query($fQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
        if ($fResult) {
            return $fResult->id;
        } else {
            $province = (isset($params['province']) && trim($params['province']) != '') ? $params['province'] : '';
            $district = (isset($params['district']) && trim($params['district']) != '') ? $params['district'] : '';
            $data = array(
                'facility_id' => $params['testingFacilityId'],
                'facility_name' => $params['testingFacilityName'],
                'email' => $params['email'],
                'contact_person' => $params['contactPerson'],
                'district' => $district,
                'province' => $province,
                'latitude' => $params['latitude'],
                'longitude' => $params['longitude']
            );
            $this->insert($data);
            return $this->lastInsertValue;
        }
    }

    public function updateFacilityEmailAddress($params)
    {
        $result = 0;
        if (isset($params['facility']) && trim($params['facility']) != '') {
            $result = 1;
            $this->update(array('email' => $params['emailAddress']), array('facility_name' => $params['facility']));
        }
        return $result;
    }

    public function fetchFacilityProfileByAudit($ids)
    {
        $result = array();
        $fResult = array();
        $auditsResult = array();
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        if (isset($ids) && trim($ids) != '') {
            $auditId = base64_decode($ids);
            $auditQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                ->columns(array('facilityname'))
                ->where(array('spiv3.id' => $auditId));
            $auditQueryStr = $sql->buildSqlString($auditQuery);
            $auditResult = $dbAdapter->query($auditQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
            if ($auditResult) {
                $auditsQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                    ->columns(array('id', 'testingpointname', 'assesmentofaudit'))
                    ->where(array('spiv3.facilityname' => $auditResult->facilityname, 'spiv3.status' => 'approved'));
                $auditsQueryStr = $sql->buildSqlString($auditsQuery);
                $auditsResult = $dbAdapter->query($auditsQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();

                $fQuery = $sql->select()->from(array('spirt3' => 'spi_rt_3_facilities'))
                    ->columns(array('facility_name', 'email'))
                    ->where(array('spirt3.facility_name' => $auditResult->facilityname));
                $fQueryStr = $sql->buildSqlString($fQuery);
                $fResult = $dbAdapter->query($fQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
            }
            $result = array('fResult' => $fResult, 'auditsResult' => $auditsResult);
        }
        return $result;
    }

    public function fetchFacilityProfileByAuditV5($ids)
    {
        $result = array();
        $fResult = array();
        $auditsResult = array();
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        if (isset($ids) && trim($ids) != '') {
            $auditId = base64_decode($ids);
            $auditQuery = $sql->select()->from(array('spiv5' => 'spi_form_v_5'))
                ->columns(array('facilityname'))
                ->where(array('spiv5.id' => $auditId));
            $auditQueryStr = $sql->buildSqlString($auditQuery);
            $auditResult = $dbAdapter->query($auditQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
            if ($auditResult) {
                $auditsQuery = $sql->select()->from(array('spiv5' => 'spi_form_v_5'))
                    ->columns(array('id', 'assesmentofaudit'))
                    ->where(array('spiv5.facilityname' => $auditResult->facilityname, 'spiv5.status' => 'approved'));
                $auditsQueryStr = $sql->buildSqlString($auditsQuery);
                $auditsResult = $dbAdapter->query($auditsQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();

                $fQuery = $sql->select()->from(array('spirt3' => 'spi_rt_3_facilities'))
                    ->columns(array('facility_name', 'email'))
                    ->where(array('spirt3.facility_name' => $auditResult->facilityname));
                $fQueryStr = $sql->buildSqlString($fQuery);
                $fResult = $dbAdapter->query($fQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
            }
            $result = array('fResult' => $fResult, 'auditsResult' => $auditsResult);
        }
        return $result;
    }

    public function fetchFacilityProfileByAuditV6($ids)
    {
        $result = array();
        $fResult = array();
        $auditsResult = array();
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        if (isset($ids) && trim($ids) != '') {
            $auditId = base64_decode($ids);
            $auditQuery = $sql->select()->from(array('spiv5' => 'spi_form_v_6'))
                ->columns(array('facilityname'))
                ->where(array('spiv5.id' => $auditId));
            $auditQueryStr = $sql->buildSqlString($auditQuery);
            $auditResult = $dbAdapter->query($auditQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
            if ($auditResult) {
                $auditsQuery = $sql->select()->from(array('spiv5' => 'spi_form_v_6'))
                    ->columns(array('id', 'assesmentofaudit'))
                    ->where(array('spiv5.facilityname' => $auditResult->facilityname, 'spiv5.status' => 'approved'));
                $auditsQueryStr = $sql->buildSqlString($auditsQuery);
                $auditsResult = $dbAdapter->query($auditsQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();

                $fQuery = $sql->select()->from(array('spirt3' => 'spi_rt_3_facilities'))
                    ->columns(array('facility_name', 'email'))
                    ->where(array('spirt3.facility_name' => $auditResult->facilityname));
                $fQueryStr = $sql->buildSqlString($fQuery);
                $fResult = $dbAdapter->query($fQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
            }
            $result = array('fResult' => $fResult, 'auditsResult' => $auditsResult);
        }
        return $result;
    }

    public function fetchProvinceList()
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $provinceQuery = $sql->select()->from(array('spirt3' => 'spi_rt_3_facilities'))
            ->columns(array('name' => new Expression("DISTINCT province")));
        $provinceQueryStr = $sql->buildSqlString($provinceQuery);
        return $dbAdapter->query($provinceQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    }

    public function getSpiV3FormUniqueDistrict()
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $districtQuery = $sql->select()->from(array('spirt3' => 'spi_rt_3_facilities'))
            ->columns(array('name' => new Expression("DISTINCT district")));
        $districtQueryStr = $sql->buildSqlString($districtQuery);
        return $dbAdapter->query($districtQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    }

    public function mapProvince($params)
    {
        $result = 0;
        $loginContainer = new Container('credo');
        $userName = $loginContainer->login;
        $dbAdapter = $this->adapter;
        $eventTable = new EventLogTable($dbAdapter);
        if (isset($params['province']) && trim($params['province']) != '') {
            if (isset($params['facility']) && count($params['facility']) > 0) {
                $result = 1;
                for ($f = 0; $f < count($params['facility']); $f++) {
                    $this->update(array('province' => $params['province']), array('facility_name' => $params['facility'][$f]));
                }
            }
            $subject = '';
            $eventType = 'Map-Province';
            $action = $userName . ' has mapped Province '.$params['province'];
            $resourceName = 'Map-Province';
            $eventTable->addEventLog($subject, $eventType, $action, $resourceName);
        }
        return $result;
    }

    public function fecthProvinceData($searchStr)
    {
        if (trim($searchStr) != "") {
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $sQuery = $sql->select()->from(array('spirt3' => 'spi_rt_3_facilities'))
                ->where('spirt3.province like "%' . $searchStr . '%"')
                ->group('spirt3.province');
            $sQueryStr = $sql->buildSqlString($sQuery); // Get the string of the Sql, instead of the Select-instance
            $rResult = $adapter->query($sQueryStr, $adapter::QUERY_MODE_EXECUTE)->toArray();
            $echoResult = array();
            foreach ($rResult as $row) {
                $echoResult[] = array("id" => $row['province'], "text" => ucwords($row['province']));
            }
            if (count($echoResult) == 0) {
                $echoResult[] = array("id" => $searchStr, "text" => $searchStr);
            }
            return array("result" => $echoResult);
        }
    }

    public function fecthDistrictData($searchStr)
    {
        if (trim($searchStr) != "") {
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $sQuery = $sql->select()->from(array('spirt3' => 'spi_rt_3_facilities'))
                ->where('spirt3.district like "%' . $searchStr . '%"')
                ->group('spirt3.district');
            $sQueryStr = $sql->buildSqlString($sQuery); // Get the string of the Sql, instead of the Select-instance
            $rResult = $adapter->query($sQueryStr, $adapter::QUERY_MODE_EXECUTE)->toArray();
            $echoResult = array();
            foreach ($rResult as $row) {
                $echoResult[] = array("id" => $row['district'], "text" => ucwords($row['district']));
            }
            if (count($echoResult) == 0) {
                $echoResult[] = array("id" => $searchStr, "text" => $searchStr);
            }
            return array("result" => $echoResult);
        }
    }

    public function fetchFacilityDetails($params)
    {
        return $this->select(array('id' => (int) base64_decode($params['id'])))->current();
    }
}

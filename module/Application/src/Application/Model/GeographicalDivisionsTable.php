<?php

namespace Application\Model;

use Laminas\Session\Container;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Zend\Debug\Debug;
use Laminas\Config\Writer\PhpArray;
use Application\Service\CommonService;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


class GeographicalDivisionsTable extends AbstractTableGateway
{

    protected $table = 'geographical_divisions';
    protected $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function addProvinceDetails($params)
    {
        $loginContainer = new Container('credo');
        $currentDateTime = CommonService::getDateTime();
        if (trim($params['provinceName']) != "") {
            $data = array(
                'geo_name' => $params['provinceName'],
                'geo_code' => $params['provinceCode'],
                'geo_status' => $params['status'],
                'created_by' => $loginContainer->userId,
                'created_on'    => $currentDateTime,
                'updated_datetime'    => $currentDateTime
            );

            $this->insert($data);
            return $this->lastInsertValue;
        }
    }

    public function updateProvinceDetails($params)
    {
        if (trim($params['provinceName']) != "" && trim($params['provinceId']) != "") {
            $provinceId = base64_decode($params['provinceId']);
            $currentDateTime = CommonService::getDateTime();
            $data = array(
                'geo_name' => $params['provinceName'],
                'geo_code' => $params['provinceCode'],
                'geo_status' => $params['status'],
                'updated_datetime'    => $currentDateTime
            );
            $this->update($data, array('geo_id=' . $provinceId));
            return $provinceId;
        }
    }

    public function fetchAllProvinceDetails($parameters, $acl)
    {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */

        $aColumns = array('geo_name', 'geo_code', 'geo_status');

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
                    $sOrder .= $aColumns[(int) $parameters['iSortCol_' . $i]] . " " . ($parameters['sSortDir_' . $i]) . ",";
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
        $sQuery = $sql->select()->from('geographical_divisions')->where("geo_parent =0");
        //$sQuery=$this->select();
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
        //error_log($sQueryForm);
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->buildSqlString($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $iTotal = $this->select("geo_parent=0")->count();

        $output = array(
            "sEcho" => (int) $parameters['sEcho'],
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );

        $loginContainer = new Container('credo');
        $role = $loginContainer->roleCode;
        $update = (bool) $acl->isAllowed($role, 'Application\Controller\ProvincesController', 'edit');
        foreach ($rResult as $aRow) {
            $row = [];
            $row[] = ucwords($aRow['geo_name']);
            $row[] = $aRow['geo_code'];
            $row[] = ucwords($aRow['geo_status']);
            if ($update) {
                $row[] = '<a href="/provinces/edit/' . base64_encode($aRow['geo_id']) . '" style="margin-right: 2px;" title="Edit"><i class="fa fa-pencil"> Edit</i></a>';
            }
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function getProvinceDetails($id)
    {
        return $this->select(array('geo_id' => (int) $id))->current();
    }

    public function fetchAllActiveProvinces()
    {
        $loginContainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $query = $sql->select()->from('geographical_divisions')->order('geo_name')->where(array('geo_status' => 'active','geo_parent'=>'0'));
        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType == 'province') {
            $query = $query->where('geo_id IN (' . implode(',', $loginContainer->userMappedIds) . ')');
        }
        $roleQueryStr = $sql->buildSqlString($query);
        return $dbAdapter->query($roleQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    }

    public function fetchAllProvinces()
    {
        $loginContainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $query = $sql->select()->from('geographical_divisions')->order('geo_name')->where(array('geo_status' => 'active', 'geo_parent' => '0'));
        $roleQueryStr = $sql->buildSqlString($query);
        return $dbAdapter->query($roleQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    }

    public function addDistrictDetails($params)
    {
        $loginContainer = new Container('credo');
        $currentDateTime = CommonService::getDateTime();
        if (trim($params['districtName']) != "" && trim($params['province']) != "") {
            $provinceId = base64_decode($params['province']);
            $data = array(
                'geo_name' => $params['districtName'],
                'geo_code' => $params['districtCode'],
                'geo_parent' => $provinceId,
                'geo_status' => $params['status'],
                'created_by' => $loginContainer->userId,
                'created_on'    => $currentDateTime,
                'updated_datetime'    => $currentDateTime
            );
            $this->insert($data);
            return $this->lastInsertValue;
        }
    }

    public function updateDistrictDetails($params)
    {
        if (trim($params['districtName']) != "" && trim($params['districtId']) != "" && trim($params['province']) != "") {
            $districtId = base64_decode($params['districtId']);
            $provinceId = base64_decode($params['province']);
            $currentDateTime = CommonService::getDateTime();
            $data = array(
                'geo_name' => $params['districtName'],
                'geo_code' => $params['districtCode'],
                'geo_parent' => $provinceId,
                'geo_status' => $params['status'],
                'updated_datetime'    => $currentDateTime
            );
            $this->update($data, array('geo_id=' . $districtId));
            return $districtId;
        }
    }

    public function fetchAllDistrictDetails($parameters, $acl)
    {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */

        $aColumns = array('d.geo_name', 'd.geo_code', 'p.geo_name', 'd.geo_status');

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
                    $sOrder .= $aColumns[(int) $parameters['iSortCol_' . $i]] . " " . ($parameters['sSortDir_' . $i]) . ",";
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
        $sQuery = $sql->select()->from(array('d' => 'geographical_divisions'))
            ->join(array('p' => 'geographical_divisions'), 'p.geo_id=d.geo_parent', array('province' => 'geo_name'))
            ->where("d.geo_parent!='0'");
        //$sQuery=$this->select();
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
        //error_log($sQueryForm);
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->buildSqlString($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $iTotal = $this->select("geo_parent>0")->count();

        $output = array(
            "sEcho" => (int) $parameters['sEcho'],
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );

        $loginContainer = new Container('credo');
        $role = $loginContainer->roleCode;
        $update = (bool) $acl->isAllowed($role, 'Application\Controller\ProvincesController', 'edit');
        foreach ($rResult as $aRow) {
            $row = [];
            $row[] = ucwords($aRow['geo_name']);
            $row[] = $aRow['geo_code'];
            $row[] = ucwords($aRow['province']);
            $row[] = ucwords($aRow['geo_status']);
            if ($update) {
                $row[] = '<a href="/district/edit/' . base64_encode($aRow['geo_id']) . '" style="margin-right: 2px;" title="Edit"><i class="fa fa-pencil"> Edit</i></a>';
            }
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function fetchAllActiveDistricts()
    {
        $loginContainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $query = $sql->select()->from('geographical_divisions')->order('geo_name')->where(array('geo_status' => 'active'))->where("geo_parent!=0");
        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType == 'district') {
            $query = $query->where('geo_id IN (' . implode(',', $loginContainer->userMappedIds) . ')');
        }
        $queryStr = $sql->buildSqlString($query);
        return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    }

    public function fetchAllDistricts()
    {
        $loginContainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $query = $sql->select()->from('geographical_divisions')->order('geo_name')->where(array('geo_status' => 'active'))->where("geo_parent!=0");
        $queryStr = $sql->buildSqlString($query);
        return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    }

    public function fetchAllMappedLocations()
    {
        $loginContainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $query = $sql->select()->from('geographical_divisions')->order('geo_name')->where(array('geo_status' => 'active','geo_parent'=>'0'));
        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType == 'province') {
            $query = $query->where('geo_id IN (' . implode(',', $loginContainer->userMappedIds) . ')');
        }
        $queryStr = $sql->buildSqlString($query);
        $result['provinces'] = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();

        $query = $sql->select()->from('geographical_divisions')->order('geo_name')->where(array('geo_status' => 'active'))->where("geo_parent!=0");
        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType == 'district') {
            $query = $query->where('geo_id IN (' . implode(',', $loginContainer->userMappedIds) . ')');
        }
        $queryStr = $sql->buildSqlString($query);
        $result['districts'] = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();

        $countriesTable = new CountriesTable($this->adapter);
        $result['countries'] = $countriesTable->fetchAllMapedCountries();

        return $result;
    }

    public function fetchAllDistrictByProvince($provinceId)
    {
        if ($provinceId != "") {
            $dbAdapter = $this->adapter;
            $sql = new Sql($this->adapter);
            $query = $sql->select()->from('geographical_divisions')
                ->where(array('geo_parent' => $provinceId, 'geo_status' => 'active'));
            $queryStr = $sql->buildSqlString($query);
            return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        }
    }

    public function fetchAllDistrictByProvinceAsMultiple($provinceId)
    {
        if ($provinceId != "") {
            $dbAdapter = $this->adapter;
            $sql = new Sql($this->adapter);
            $query = $sql->select()->from('geographical_divisions')
                ->where('geo_parent IN ("' . implode('", "', $provinceId) . '")');
            $queryStr = $sql->buildSqlString($query);
            return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        }
    }

    public function addProvinceByFacility($provinceName)
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $query = $sql->select()->from('geographical_divisions')
            ->where("geo_name  like '%" . $provinceName . "%'");
        $queryStr = $sql->buildSqlString($query);
        $result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
        // print_r($result); die;
        if ($result) {
            return $result['geo_id'];
        } else {
            $loginContainer = new Container('credo');
            $currentDateTime = CommonService::getDateTime();
            $data = array(
                'geo_name' => $provinceName,
                'geo_code' => $provinceName,
                'geo_status' => 'active',
                'created_by' => $loginContainer->userId,
                'created_on'    => $currentDateTime,
                'updated_datetime'    => $currentDateTime
            );
            $this->insert($data);
            return $this->lastInsertValue;
        }
    }

    public function addDistrictByFacility($provinceId, $districtName)
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $query = $sql->select()->from('geographical_divisions')
            ->where("geo_name  like '%" . $districtName . "%'");
        $queryStr = $sql->buildSqlString($query);
        $result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
        if ($result) {
            return $result['geo_id'];
        } else {
            $loginContainer = new Container('credo');
            $currentDateTime = CommonService::getDateTime();
            $data = array(
                'geo_name' => $districtName,
                'geo_code' => $districtName,
                'geo_parent' => $provinceId,
                'geo_status' => 'active',
                'created_by' => $loginContainer->userId,
                'created_on'    => $currentDateTime,
                'updated_datetime'    => $currentDateTime
            );
            $this->insert($data);
            return $this->lastInsertValue;
        }
    }

    public function getProvinceDistrict($params)
    {
        $res = $this->select(array('geo_name' => $params['geo_name']))->current();
        if ($res) {
            return 1;
        }
        return 0;
    }

    public function checkDistrict($district)
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $query = $sql->select()->from('geographical_divisions')
            ->where("geo_name  like '%" . $district . "%'");
        $queryStr = $sql->buildSqlString($query);
        $result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
        if ($result) {
            $res['district'] = $result['geo_id'];
            $res['province'] = $result['geo_parent'];
            return $res;
        } else {
            $loginContainer = new Container('credo');
            $currentDateTime = CommonService::getDateTime();
            $data = array(
                'geo_name' => $district,
                'geo_code' => $district,
                'geo_parent' => $district,
                'geo_status' => 'active',
                'created_by' => $loginContainer->userId,
                'created_on'    => $currentDateTime,
                'updated_datetime'    => $currentDateTime
            );
            $this->insert($data);
            $insertId = $this->lastInsertValue;
            $query = $sql->select()->from('geographical_divisions')
                ->where("id =$insertId");
            $queryStr = $sql->buildSqlString($query);
            $result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
            $res['district'] = $result['geo_id'];
            $res['province'] = $result['geo_parent'];
            return $res;
        }
    }
}

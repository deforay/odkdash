<?php

namespace Application\Model;

use Laminas\Session\Container;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Zend\Debug\Debug;
use Laminas\Config\Writer\PhpArray;

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
class RolesTable extends AbstractTableGateway  {

    protected $table = 'roles';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function addRolesDetails($params) {
        
        if (trim($params['roleName']) != "") {
            $rolesdata = array(
                'category_id' => 1,
                'role_name' => $params['roleName'],
                'role_code' => $params['roleCode'],
                'description' => $params['description'],
                'status' => 'active'
            );
            
            $this->insert($rolesdata);
            $lastInsertId=$this->lastInsertValue;
            return $lastInsertId;
        }
    }
    
    public function updateRolesDetails($params) {
        if (trim($params['roleName']) != "" && trim($params['roleId']) != "") {
            $roleId=base64_decode($params['roleId']);
            $rolesdata = array(
                'role_name' => $params['roleName'],
                'role_code' => $params['roleCode'],
                'description' => $params['description'],
                'status' => $params['status']
            );
            $this->update($rolesdata,array('role_id='.$roleId));
            return $roleId;
        }
    }
    
    public function fetchAllRoleDetails($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */

        $aColumns = array('role_name','role_code','status');

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
                    $sOrder .= $aColumns[intval($parameters['iSortCol_' . $i])] . " " . ( $parameters['sSortDir_' . $i] ) . ",";
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
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search ) . "%' OR ";
                    } else {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search ) . "%' ";
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
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from('roles')->where("role_code !='daemon'");
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

        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery); // Get the string of the Sql, instead of the Select-instance 
        //error_log($sQueryForm);
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->getSqlStringForSqlObject($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $iTotal = $this->select("role_code !='daemon'")->count();

        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
        $loginContainer = new Container('credo');
        $role = $loginContainer->roleCode;
        if ($acl->isAllowed($role, 'Application\Controller\Roles', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
        foreach ($rResult as $aRow) {
            $row = array();
            $row[] = ucwords($aRow['role_name']);
            $row[] = $aRow['role_code'];
            $row[] = ucwords($aRow['status']);
            if($update){
            $row[] = '<a href="/roles/edit/' . base64_encode($aRow['role_id']) . '" style="margin-right: 2px;" title="Edit"><i class="fa fa-pencil"> Edit</i></a>';
            }
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function getRolesDetails($id) {
        $row = $this->select(array('role_id' => (int) $id))->current();
        return $row;
    }

    public function fecthAllActiveRoles() {
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $query = $sql->select()->from('roles')->order('role_name')->where(array('status'=>'active'));
        $roleQueryStr = $sql->getSqlStringForSqlObject($query); // Get the string of the Sql, instead of the Select-instance
        return $dbAdapter->query($roleQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    }
    
    public function fetchAllRoles() {
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $resourceQuery = $sql->select()->from('resources')->order('display_name');
        $resourceQueryStr = $sql->getSqlStringForSqlObject($resourceQuery); // Get the string of the Sql, instead of the Select-instance
        $resourceResult = $dbAdapter->query($resourceQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        $n = sizeof($resourceResult);
        for ($i = 0; $i < $n; $i++) {
            $privilageQuery = $sql->select()->from('privileges')->where(array('resource_id' => $resourceResult[$i]['resource_id']))->order('display_name');
            $privilageQueryStr = $sql->getSqlStringForSqlObject($privilageQuery); // Get the string of the Sql, instead of the Select-instance
            $resourceResult[$i]['privileges'] = $dbAdapter->query($privilageQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        }
        return $resourceResult;
    }
    
    public function mapRolesPrivileges($params) {    
        try {
                $roleCode=$params['roleCode'];
                $configFile = CONFIG_PATH . DIRECTORY_SEPARATOR . "acl.config.php";
                $config = new \Laminas\Config\Config(include($configFile), true);
                $config->$roleCode = array();
                
                foreach ($params['resource'] as $resourceName => $privilege) {
                    $config->$roleCode->$resourceName = $privilege;
                }
        
                $writer = new PhpArray();
                $writer->toFile($configFile, $config);
                
            } catch (Exception $exc) {
        
                error_log($exc->getMessage());
                error_log($exc->getTraceAsString());
           }
    }
}

<?php

namespace Application\Model;

use Zend\Session\Container;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Zend\Db\TableGateway\AbstractTableGateway;

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
class SpiRtFacilitiesTable extends AbstractTableGateway {

    protected $table = 'spi_rt_3_facilities';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    public function addFacilityBasedOnForm($formId){
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $query = $sql->select()->from('spi_form_v_3')
                    ->columns(array('formId','facilityname','facilityid','Latitude','Longitude'))
                    ->where(array('id'=>$formId));
        $queryStr = $sql->getSqlStringForSqlObject($query);
        $result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
        if($result!=""){
            $data = array(
                'facility_id' => $result['facilityid'],
                'facility_name' => $result['facilityname'],
                'latitude' => $result['Latitude'],
                'longitude' => $result['Longitude']
            );
            return $this->insert($data);
        }
    }
    
	public function addFacilityDetails($params){
        if(isset($params['facilityId']) && trim($params['facilityId'])!=""){
            $data = array(
                'facility_id' => $params['facilityId'],
                'facility_name' => $params['facilityName'],
                'email' => $params['email'],
                'contact_person' => $params['contactPerson'],
                'district' => $params['district'],
                'province' => $params['province'],
                'latitude' => $params['latitude'],
                'longitude' => $params['longitude']
            );
			$this->insert($data);
            return $lastInsertedId = $this->lastInsertValue;
        }
    }
	
    public function updateFacilityDetails($params){
        if(isset($params['rowId']) && trim($params['rowId'])!=""){
            $rowId=base64_decode($params['rowId']);
            $data = array(
                'facility_id' => $params['facilityId'],
                'facility_name' => $params['facilityName'],
                'email' => $params['email'],
                'contact_person' => $params['contactPerson'],
                'district' => $params['district'],
                'province' => $params['province'],
                'latitude' => $params['latitude'],
                'longitude' => $params['longitude']
            );
			$this->update($data,array('id'=>$rowId));
            return $rowId;
        }
    }
    
    public function fetchAllFacilities($parameters)
    {
        
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
	
        $aColumns = array('facility_id','facility_name','email','contact_person');
        $orderColumns = array('facility_id','facility_name','email','contact_person');

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
                    $sOrder .= $orderColumns[intval($parameters['iSortCol_' . $i])] . " " . ( $parameters['sSortDir_' . $i] ) . ",";
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
        $start_date = "";
        $end_date = "";
        $sQuery = $sql->select()->from('spi_rt_3_facilities');
       
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
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->getSqlStringForSqlObject($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $tQuery =  $sql->select()->from('spi_rt_3_facilities');
        $tQueryStr = $sql->getSqlStringForSqlObject($tQuery); // Get the string of the Sql, instead of the Select-instance
        $tResult = $dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);
        $iTotal = count($tResult);
        $output = array(
           "sEcho" => intval($parameters['sEcho']),
           "iTotalRecords" => $iTotal,
           "iTotalDisplayRecords" => $iFilteredTotal,
           "aaData" => array()
        );
	
       foreach ($rResult as $aRow) {
        $row = array();
        
        $row[] = $aRow['facility_id'];
        $row[] = $aRow['facility_name'];
        $row[] = $aRow['email'];
        $row[] = $aRow['contact_person'];
        $edit = '<a href="/facility/edit/'.base64_encode($aRow['id']).'" title="Edit"><i class="fa fa-pencil"></i> Edit</a>';
        $row[] =$edit;
        $output['aaData'][] = $row;
       }
       return $output;
    }
    
    public function fetchFacility($id){
        $row = $this->select(array('id' => (int) $id))->current();
        return $row;
    }
}

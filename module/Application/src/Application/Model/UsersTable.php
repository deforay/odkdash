<?php

namespace Application\Model;

use Laminas\Session\Container;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Application\Service\CommonService;
use Application\Model\UserRoleMapTable;
use Application\Model\UserTokenMapTable;
use Application\Model\EventLogTable;
use Application\Model\UserLoginHistoryTable;
use \Application\Service\ImageResizeService;



/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Countries
 *
 * @author amit
 */
class UsersTable extends AbstractTableGateway
{

    protected $table = 'users';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }


    public function login($params)
    {
        $common = new CommonService();
        $container = new Container('alert');
        $logincontainer = new Container('credo');
        $username = $params['username'];
        $config = new \Laminas\Config\Reader\Ini();
        $configResult = $config->fromFile(CONFIG_PATH . '/custom.config.ini');
        $password = sha1($params['password'] . $configResult["password"]["salt"]);

        $dbAdapter = $this->adapter;
        $gTable = new GlobalTable($dbAdapter);
        $trackTable = new EventLogTable($dbAdapter);
        $userHistoryTable = new UserLoginHistoryTable($dbAdapter);
        $userCountryMap = new UserCountryMapTable($dbAdapter);
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from(array('u' => 'users'))
            ->join(array('urm' => 'user_role_map'), 'urm.user_id=u.id', array('role_id'))
            ->join(array('r' => 'roles'), 'r.role_id=urm.role_id', array('role_name', 'role_code'))
            ->where(array('login' => $username, 'u.status' => 'active'));
        $sQueryStr = $sql->buildSqlString($sQuery);
        // die($sQueryStr);
        $sResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
        $data = array(
            'last_login_datetime' => $common->getDateTime()
        );
        if ($sResult !== false && !empty($sResult)) {
            $this->update($data, array('id' => $sResult->id));
        }

        if ($sResult) {
            $userCountryMapArray = array();
            $userCountryQuery = $sql->select()->from(array('u_c_map' => 'user_country_map'))
                ->join(array('c' => 'countries'), "c.country_id=u_c_map.country_id", array('country_id', 'country_name', 'iso2'))
                ->where(array('u_c_map.user_id' => $sResult->id))
                ->order("country_name ASC");
            $userCountryQueryStr = $sql->buildSqlString($userCountryQuery);
            $userCountryMapResult = $dbAdapter->query($userCountryQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
            foreach ($userCountryMapResult as $ucMap) {
                $userCountryMapArray[] = $ucMap['country_id'];
            }

            $token = array();
            $userTokenQuery = $sql->select()->from(array('u_t_map' => 'user_token_map'))
                ->columns(array('token'))
                ->where(array('user_id' => $sResult->id))
                ->order("token ASC");
            $userTokenQueryStr = $sql->buildSqlString($userTokenQuery);
            $userTokenResult = $dbAdapter->query($userTokenQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
            foreach ($userTokenResult as $userToken) {
                $token[] = $userToken['token'];
            }
            $logincontainer->userId = $sResult->id;
            $logincontainer->login = $sResult->login;
            $logincontainer->roleCode = $sResult->role_code;
            $logincontainer->token = $token;
            $logincontainer->userCountryMap = $userCountryMapArray;
            $logincontainer->userImage = $sResult->user_image;
            $subject = '';
            $eventType = 'login in';
            $action = $username . ' logged in';
            $resourceName = 'login in';
            $trackTable->addEventLog($subject, $eventType, $action, $resourceName);
            $userHistoryTable->userHistoryLog($sResult->login, $loginStatus = 'successful');

            $redirect = $gTable->getGlobalValue('web_version');
            $redirectRoute = 'dashboard';
            if (isset($redirect) && !empty($redirect) && !is_array($redirect)) {
                if (!empty($redirect) && $redirect == 'v6') {
                    $redirectRoute = 'dashboard-v6';
                }
                if (!empty($redirect) && $redirect == 'v5') {
                    $redirectRoute = 'dashboard-v5';
                }
                if (!empty($redirect) && $redirect == 'v3') {
                    $redirectRoute = 'dashboard';
                }
            }
            return $redirectRoute;
        } else {
            $container->alertMsg = 'Please check your login credentials';
            return 'login';
        }
    }

    public function addUserDetails($params)
    {
        $common = new CommonService();
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $userRoleMap = new UserRoleMapTable($dbAdapter);
        $userTokenMap = new UserTokenMapTable($dbAdapter);
        $userCountryMap = new UserCountryMapTable($dbAdapter);
        $config = new \Laminas\Config\Reader\Ini();
        $configResult = $config->fromFile(CONFIG_PATH . '/custom.config.ini');
        $password = sha1($params['password'] . $configResult["password"]["salt"]);
        $lastInsertId = 0;
        if (isset($params['userName']) && trim($params['userName']) != "") {
            $data = array(
                'first_name' => $params['firstName'],
                'last_name' => $params['lastName'],
                'login' => $params['userName'],
                'password' => $password,
                'email' => $params['email'],
                'status' => $params['status'],
                'contact_no' => $params['mobile_no'],
                'created_on' => $common->getDateTime()
            );
            $this->insert($data);
            $lastInsertId = $this->lastInsertValue;
            if ($lastInsertId > 0) {

                $userRoleMap->insert(array('user_id' => $lastInsertId, 'role_id' => $params['roleId']));
                if(count($params['country']) > 0){
                    foreach($params['country'] as $country){
                        $userCountryMap->insert(array('user_id' => $lastInsertId, 'country_id' => $country));
                    }
                }
                //Add User-Token
                if (isset($params['token']) && trim($params['token']) != '') {
                    $splitToken = explode(",", $params['token']);
                    for ($t = 0; $t < count($splitToken); $t++) {
                        $userTokenMap->insert(array('user_id' => $lastInsertId, 'token' => trim($splitToken[$t])));
                    }
                }
            }
            if (isset($_FILES['user_image']['name']) && $_FILES['user_image']['name'] != '') {
                if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users") && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users")) {
                    mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users");
                }
                $extension = strtolower(pathinfo(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_FILES['user_image']['name'], PATHINFO_EXTENSION));
                $imageName = time() . '_' . $lastInsertId . "." . $extension;
                if (move_uploaded_file($_FILES["user_image"]["tmp_name"], UPLOAD_PATH . DIRECTORY_SEPARATOR . "users" . DIRECTORY_SEPARATOR . $imageName)) {

                    $resizeObj = new ImageResizeService(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users" . DIRECTORY_SEPARATOR . $imageName);
                    $resizeObj->resizeImage(320, 214, 'auto');
                    $resizeObj->saveImage(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users" . DIRECTORY_SEPARATOR . $imageName, 100);
                    $imageUpName = '/uploads' . DIRECTORY_SEPARATOR . "users" . DIRECTORY_SEPARATOR . $imageName;
                    $imageData = array('user_image' => $imageUpName);
                    $this->update($imageData, array("id" => $lastInsertId));
                }
            }
            return $lastInsertId;
        }
    }

    public function updateUserDetails($params)
    {
        $common = new CommonService();
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $userRoleMap = new UserRoleMapTable($dbAdapter);
        $userTokenMap = new UserTokenMapTable($dbAdapter);
        $userCountryMap = new UserCountryMapTable($dbAdapter);
        $userId = base64_decode($params['userId']);
        if (isset($params['password']) && $params['password'] != '') {
            $config = new \Laminas\Config\Reader\Ini();
            $configResult = $config->fromFile(CONFIG_PATH . '/custom.config.ini');
            $password = sha1($params['password'] . $configResult["password"]["salt"]);
            $data = array('password' => $password);
            $this->update($data, array('id' => $userId));
        }
        if (isset($params['userName']) && trim($params['userName']) != "") {
            $data = array(
                'first_name' => $params['firstName'],
                'last_name' => $params['lastName'],
                'login' => $params['userName'],
                'email' => $params['email'],
                'contact_no' => $params['mobile_no'],
                'status' => $params['status']
            );
            $this->update($data, array('id' => $userId));
            if ($userId > 0) {
                $userCountryMap->delete(array('user_id' => $userId));
                $userRoleMap->update(array('role_id' => $params['roleId']), array('user_id' => $userId));
                if(count($params['country']) > 0){
                    foreach($params['country'] as $country){
                        $userCountryMap->insert(array('user_id' => $userId, 'country_id' => $country));
                    }
                }
                //Update User-Token
                $userTokenMap->delete(array('user_id' => $userId));
                if (isset($params['token']) && trim($params['token']) != '') {
                    $splitToken = explode(",", $params['token']);
                    for ($t = 0; $t < count($splitToken); $t++) {
                        $userTokenMap->insert(array('user_id' => $userId, 'token' => trim($splitToken[$t])));
                    }
                }
            }
            if (isset($params['existImage']) && $params['existImage'] == '') {
                if (file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users")) {
                    unlink(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users" . DIRECTORY_SEPARATOR . $params['removedImage']);
                    $imageData = array('user_image' => '');
                    $result = $this->update($imageData, array("id" => $userId));
                }
            }
            if (isset($_FILES['user_image']['name']) && $_FILES['user_image']['name'] != '') {
                if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users") && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users")) {
                    mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users");
                }
                $extension = strtolower(pathinfo(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_FILES['user_image']['name'], PATHINFO_EXTENSION));
                $imageName = time() . '_' . $userId . "." . $extension;
                if (move_uploaded_file($_FILES["user_image"]["tmp_name"], UPLOAD_PATH . DIRECTORY_SEPARATOR . "users" . DIRECTORY_SEPARATOR . $imageName)) {

                    $resizeObj = new ImageResizeService(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users" . DIRECTORY_SEPARATOR . $imageName);
                    $resizeObj->resizeImage(320, 214, 'auto');
                    $resizeObj->saveImage(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users" . DIRECTORY_SEPARATOR . $imageName, 100);
                    $imageUpName = '/uploads' . DIRECTORY_SEPARATOR . "users" . DIRECTORY_SEPARATOR . $imageName;
                    $imageData = array('user_image' => $imageUpName);
                    $this->update($imageData, array("id" => $userId));
                }
            }
            return $userId;
        }
    }

    public function fetchAllUsers($parameters, $acl)
    {

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */

        $aColumns = array('first_name', 'last_name', 'email', 'status');
        $orderColumns = array('first_name', 'email', 'status');

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
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from('users');


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
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->buildSqlString($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $tQuery =  $sql->select()->from('users');
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
        if ($acl->isAllowed($role, 'Application\Controller\Users', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }

        foreach ($rResult as $aRow) {
            $row = array();

            $row[] = ucwords($aRow['first_name'] . " " . $aRow['last_name']);
            $row[] = $aRow['email'];
            $row[] = ucwords($aRow['status']);
            if ($update) {
                $edit = '<a href="/users/edit/' . base64_encode($aRow['id']) . '" title="Edit"><i class="fa fa-pencil"></i> Edit</a>';
                $row[] = $edit;
            }
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function fetchUser($id)
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $query = $sql->select()->from(array('u' => 'users'))
            ->join(array('urm' => 'user_role_map'), "urm.user_id=u.id", array('role_id'), 'left')
            ->where(array('id' => $id));
        $queryStr = $sql->buildSqlString($query);
        $queryResult = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
        if ($queryResult) {
            $userTokenQuery = $sql->select()->from(array('u_t_map' => 'user_token_map'))
                ->columns(array('token'))
                ->where(array('user_id' => $id))
                ->order("token ASC");
            $userTokenQueryStr = $sql->buildSqlString($userTokenQuery);
            $queryResult['userToken'] = $dbAdapter->query($userTokenQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        }
        return $queryResult;
    }

    function logout($username)
    {
        $dbAdapter = $this->adapter;
        $trackTable = new EventLogTable($dbAdapter);
        $subject = '';
        $eventType = 'log-out';
        $action = $username . ' logged out';
        $resourceName = 'log-out';
        $trackTable->addEventLog($subject, $eventType, $action, $resourceName);
    }

    public function updateUserProfileDetails($params)
    {
        $common = new CommonService();
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $userId = base64_decode($params['userId']);
        if (!empty($userId)) {
            $data = array(
                'first_name' => $params['firstName'],
                'last_name' => $params['lastName'],
                'email' => $params['email'],
                'contact_no' => $params['mobile_no']
            );
            $this->update($data, array('id' => $userId));
            if (isset($params['existImage']) && $params['existImage'] == '') {
                if (file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users")) {
                    unlink(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users" . DIRECTORY_SEPARATOR . $params['removedImage']);
                    $imageData = array('user_image' => '');
                    $result = $this->update($imageData, array("id" => $userId));
                }
            }
            if (isset($_FILES['user_image']['name']) && $_FILES['user_image']['name'] != '') {
                if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users") && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users")) {
                    mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users");
                }
                $extension = strtolower(pathinfo(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_FILES['user_image']['name'], PATHINFO_EXTENSION));
                $imageName = time() . '_' . $userId . "." . $extension;
                if (move_uploaded_file($_FILES["user_image"]["tmp_name"], UPLOAD_PATH . DIRECTORY_SEPARATOR . "users" . DIRECTORY_SEPARATOR . $imageName)) {

                    $resizeObj = new ImageResizeService(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users" . DIRECTORY_SEPARATOR . $imageName);
                    $resizeObj->resizeImage(320, 214, 'auto');
                    $resizeObj->saveImage(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users" . DIRECTORY_SEPARATOR . $imageName, 100);
                    $imageUpName = '/uploads' . DIRECTORY_SEPARATOR . "users" . DIRECTORY_SEPARATOR . $imageName;
                    $imageData = array('user_image' => $imageUpName);
                    $this->update($imageData, array("id" => $userId));
                }
            }
            return $userId;
        }
    }

    public function updatePassword($params)
    {
        $logincontainer = new Container('credo');
        $common = new CommonService();
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $userId = $logincontainer->userId;
        $config = new \Laminas\Config\Reader\Ini();
        $configResult = $config->fromFile(CONFIG_PATH . '/custom.config.ini');
        $password = sha1($params['newpassword'] . $configResult["password"]["salt"]);
        $data = array('password' => $password);
        return $this->update($data, array('id' => $userId));
    }
}

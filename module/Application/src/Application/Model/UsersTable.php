<?php

namespace Application\Model;

use Laminas\Db\Sql\Sql;
use Laminas\Session\Container;
use Laminas\Db\Adapter\Adapter;
use Application\Model\GlobalTable;
use Application\Model\EventLogTable;
use Application\Service\CommonService;
use Application\Model\UserRoleMapTable;
use Application\Model\UserTokenMapTable;
use \Application\Service\ImageResizeService;
use Application\Model\UserLoginHistoryTable;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Application\Model\UserLocationMapTable;
use Twilio\Rest\Client;




/**
 * Description of Countries
 *
 * @author amit
 */
class UsersTable extends AbstractTableGateway
{

    protected $table = 'users';
    protected $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }


    public function login($params, $configResult)
    {
        $container = new Container('alert');
        $loginContainer = new Container('credo');
        $username = $params['username'];

        $dbAdapter = $this->adapter;
        $gTable = new GlobalTable($dbAdapter);
        $trackTable = new EventLogTable($dbAdapter);
        $userHistoryTable = new UserLoginHistoryTable($dbAdapter);
        $userLocationMapTable = new UserLocationMapTable($dbAdapter);
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from(['u' => 'users'])
            ->join(['urm' => 'user_role_map'], 'urm.user_id=u.id', ['role_id'])
            ->join(['r' => 'roles'], 'r.role_id=urm.role_id', ['role_name', 'role_code'])
            ->where(['login' => $username, 'u.status' => 'active']);
        $sQueryStr = $sql->buildSqlString($sQuery);

        /** @var \Laminas\Db\Adapter\Driver\ResultInterface $sResult */
        $sResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();

        $passwordValidation = false;
        if ($sResult) {
            $passwordValidation = password_verify($params['password'], $sResult->password);
        }

        $data = [
            'last_login_datetime' => CommonService::getDateTime()
        ];
        if ($sResult !== false && !empty($sResult)) {
            $this->update($data, ['id' => $sResult->id]);
        }

        if ($sResult && $passwordValidation) {
            $userMappedIdsArray = [];
            $userMappingType = '';
            $sendLoginOtp = $gTable->getGlobalValue('login_otp');
            if ($sendLoginOtp == "yes") {
                if (trim($sResult->contact_no) != "") {
                    $this->updateUserOtp($sResult->id, trim($sResult->contact_no));
                    $loginContainer->otpId = $sResult->id;
                    return 'validate-otp';
                } else {
                    $container->alertMsg = 'Please contact your admin,Unable to sent OTP your number';
                    return 'login';
                }
            } else {
                return $this->userEventLog($sResult);
            }
        } else {
            $container->alertMsg = 'Please check your login credentials';
            return 'login';
        }
    }

    public function passwordHash($password)
    {
        if (empty($password)) {
            return null;
        }

        $options = [
            'cost' => 14
        ];

        return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    public function addUserDetails($params, $configResult)
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $userRoleMap = new UserRoleMapTable($dbAdapter);
        $userTokenMap = new UserTokenMapTable($dbAdapter);
        $userLocationMap = new UserLocationMapTable($dbAdapter);
        $password = $this->passwordHash($params['password']);
        $lastInsertId = 0;
        if (isset($params['userName']) && trim($params['userName']) != "") {
            $data = array(
                'first_name' => $params['firstName'],
                'last_name' => $params['lastName'],
                'login' => $params['userName'],
                'password' => $password,
                'email' => $params['email'],
                'status' => $params['status'],
                'language' => $params['language'],
                'contact_no' => $params['mobile_no'],
                'created_on' => CommonService::getDateTime()
            );
            $this->insert($data);
            $lastInsertId = $this->lastInsertValue;
            if ($lastInsertId > 0) {

                $userRoleMap->insert(array('user_id' => $lastInsertId, 'role_id' => $params['roleId']));

                //Add User-Location
                if (!empty($params['mappingType']) && in_array($params['mappingType'], ['country', 'province', 'district'])) {
                    $locationKey = $params['mappingType'];
                    if (!empty($params[$locationKey])) {
                        $data = array_map(function ($location) use ($lastInsertId, $params) {
                            return [
                                'user_id' => $lastInsertId,
                                'location_id' => $location,
                                'mapping_type' => $params['mappingType']
                            ];
                        }, $params[$locationKey]);
                        if (!empty($data)) {
                            foreach ($data as $row) {
                                $userLocationMap->insert($row); // Single row insert
                            }
                        }
                    }
                }

                //Add User-Token
                if (isset($params['token']) && trim($params['token']) != '') {
                    $splitToken = explode(",", $params['token']);
                    $counter = count($splitToken);
                    for ($t = 0; $t < $counter; $t++) {
                        $userTokenMap->insert(array('user_id' => $lastInsertId, 'token' => trim($splitToken[$t])));
                    }
                }
            }
            if (isset($_FILES['user_image']['name']) && $_FILES['user_image']['name'] != '') {
                // Sanitize the base directory path
                $baseDirectory = UPLOAD_PATH . DIRECTORY_SEPARATOR . "users";
                $safeDirectoryPath = CommonService::buildSafePath(UPLOAD_PATH, ['users']); // Use buildSafePath to sanitize path

                if ($safeDirectoryPath !== false) {
                    // Extract the file extension
                    $extension = strtolower(pathinfo($_FILES['user_image']['name'], PATHINFO_EXTENSION));

                    // Construct a preliminary filename and clean it using preg_replace
                    $prelimFileName = time() . '_' . $lastInsertId; // Filename before sanitization
                    $prelimFileName = preg_replace('/[^a-zA-Z0-9_-]/', '', $prelimFileName); // Strip out any invalid characters

                    // Now clean the file name further using cleanFileName()
                    $safeFileName = CommonService::cleanFileName($prelimFileName) . "." . $extension;

                    // Construct the full safe file path
                    $imageFullPath = $safeDirectoryPath . DIRECTORY_SEPARATOR . $safeFileName;

                    // Move the uploaded file to the safe directory
                    if (move_uploaded_file($_FILES["user_image"]["tmp_name"], $imageFullPath)) {
                        // Resize the image if upload was successful
                        $resizeObj = new ImageResizeService($imageFullPath);
                        $resizeObj->resizeImage(320, 214, 'auto');
                        $resizeObj->saveImage($imageFullPath, 100);

                        // Save the file path relative to the uploads folder
                        $imageUpName = '/uploads/users/' . $safeFileName;
                        $imageData = array('user_image' => $imageUpName);
                        $this->update($imageData, array("id" => $lastInsertId));
                    }
                } else {
                    // Handle error if directory creation fails
                    echo "Failed to create directory.";
                }
            }

            return $lastInsertId;
        }
    }

    public function updateUserDetails($params, $configResult)
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $userRoleMap = new UserRoleMapTable($dbAdapter);
        $userTokenMap = new UserTokenMapTable($dbAdapter);
        $userLocationMap = new UserLocationMapTable($dbAdapter);
        $userId = base64_decode($params['userId']);
        if (isset($params['password']) && $params['password'] != '') {
            $password = $this->passwordHash($params['password']);
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
                'status' => $params['status'],
                'language' => $params['language']
            );
            $this->update($data, array('id' => $userId));
            if ($userId > 0) {
                $userRoleMap->update(array('role_id' => $params['roleId']), array('user_id' => $userId));

                //Update User-Location
                $userLocationMap->delete(array('user_id' => $userId));
                if (!empty($params['mappingType']) && in_array($params['mappingType'], ['country', 'province', 'district'])) {
                    $locationKey = $params['mappingType'];
                    if (!empty($params[$locationKey])) {
                        $data = array_map(function ($location) use ($userId, $params) {
                            return [
                                'user_id' => $userId,
                                'location_id' => $location,
                                'mapping_type' => $params['mappingType']
                            ];
                        }, $params[$locationKey]);
                        if (!empty($data)) {
                            foreach ($data as $row) {
                                $userLocationMap->insert($row); // Single row insert
                            }
                        }
                    }
                }

                //Update User-Token
                $userTokenMap->delete(array('user_id' => $userId));
                if (isset($params['token']) && trim($params['token']) != '') {
                    $splitToken = explode(",", $params['token']);
                    $counter = count($splitToken);
                    for ($t = 0; $t < $counter; $t++) {
                        $userTokenMap->insert(array('user_id' => $userId, 'token' => trim($splitToken[$t])));
                    }
                }
            }
            if (isset($params['existImage']) && $params['existImage'] == '' && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users")) {
                $fileName = preg_replace('/[^a-zA-Z0-9_-]/', '', $params['existImage']);
                $cleanedFileName = CommonService::cleanFileName($fileName);
                $cleanedFilePath = CommonService::buildSafePath(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users" . DIRECTORY_SEPARATOR, []);
                unlink($cleanedFilePath . $cleanedFileName);
                $imageData = array('user_image' => '');
                $result = $this->update($imageData, array("id" => $userId));
            }

            if (isset($_FILES['user_image']['name']) && $_FILES['user_image']['name'] != '') {
                // Sanitize the base directory path
                $baseDirectory = UPLOAD_PATH . DIRECTORY_SEPARATOR . "users";
                $safeDirectoryPath = CommonService::buildSafePath(UPLOAD_PATH, ['users']); // Use buildSafePath to sanitize path

                if ($safeDirectoryPath !== false) {
                    // Extract the file extension
                    $extension = strtolower(pathinfo($_FILES['user_image']['name'], PATHINFO_EXTENSION));

                    // Construct a preliminary filename and clean it using preg_replace
                    $prelimFileName = time() . '_' . $userId; // Filename before sanitization
                    $prelimFileName = preg_replace('/[^a-zA-Z0-9_-]/', '', $prelimFileName); // Strip out any invalid characters

                    // Now clean the file name further using cleanFileName()
                    $safeFileName = CommonService::cleanFileName($prelimFileName) . "." . $extension;

                    // Construct the full safe file path
                    $imageFullPath = $safeDirectoryPath . DIRECTORY_SEPARATOR . $safeFileName;

                    // Move the uploaded file to the safe directory
                    if (move_uploaded_file($_FILES["user_image"]["tmp_name"], $imageFullPath)) {
                        // Resize the image if upload was successful
                        $resizeObj = new ImageResizeService($imageFullPath);
                        $resizeObj->resizeImage(320, 214, 'auto');
                        $resizeObj->saveImage($imageFullPath, 100);

                        // Save the file path relative to the uploads folder
                        $imageUpName = '/uploads/users/' . $safeFileName;
                        $imageData = array('user_image' => $imageUpName);
                        $this->update($imageData, array("id" => $userId));
                    }
                } else {
                    // Handle error if directory creation fails
                    echo "Failed to create directory.";
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
            for ($i = 0; $i < (int) $parameters['iSortingCols']; $i++) {
                if ($parameters['bSortable_' . (int) $parameters['iSortCol_' . $i]] == "true") {
                    $sOrder .= $orderColumns[(int) $parameters['iSortCol_' . $i]] . " " . ($parameters['sSortDir_' . $i]) . ",";
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
        $tQuery = $sql->select()->from('users');
        $tQueryStr = $sql->buildSqlString($tQuery); // Get the string of the Sql, instead of the Select-instance
        $tResult = $dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);
        $iTotal = count($tResult);
        $output = array(
            "sEcho" => (int) $parameters['sEcho'],
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );

        $loginContainer = new Container('credo');
        $role = $loginContainer->roleCode;
        $update = (bool) $acl->isAllowed($role, 'Application\Controller\UsersController', 'edit');
        $updatePassword = (bool) $acl->isAllowed($role, 'Application\Controller\UsersController', 'reset-password');

        foreach ($rResult as $aRow) {
            $row = [];

            $row[] = ucwords($aRow['first_name'] . " " . $aRow['last_name']);
            $row[] = $aRow['email'];
            $row[] = ucwords($aRow['status']);
            $actionStr = '';
            if ($update) {
                $editStr = '<a href="/users/edit/' . base64_encode($aRow['id']) . '" class="btn btn-info btn-xs" title="Edit"><i class="fa fa-pencil"></i> Edit</a>';
                $actionStr .= $editStr;
            }
            if ($updatePassword) {
                $resetPasswordStr = ' <a href="javascript:void(0);" onclick="showModal(\'/users/reset-password/' . base64_encode($aRow['id']) . '\',\'800\',\'450\');" class="btn btn-warning btn-xs" title="Reset Password">Reset Password</a>';
                $actionStr .= $resetPasswordStr;
            }
            if ($update || $updatePassword) {
                $row[] = $actionStr;
            }
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function fetchUser($id)
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
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

    public function logout($username)
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
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $userId = base64_decode($params['userId']);
        if (!empty($userId)) {
            $data = array(
                'first_name' => $params['firstName'],
                'last_name' => $params['lastName'],
                'email' => $params['email'],
                'contact_no' => $params['mobile_no']
            );
            $this->update($data, array('id' => $userId));
            if (isset($params['existImage']) && $params['existImage'] == '' && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users")) {
                $fileName = preg_replace('/[^a-zA-Z0-9_-]/', '', $params['removedImage']);
                $cleanedFileName = CommonService::cleanFileName($fileName);
                $cleanedFilePath = CommonService::buildSafePath(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users" . DIRECTORY_SEPARATOR, []);

                unlink($cleanedFilePath . $cleanedFileName);
                $imageData = array('user_image' => '');
                $result = $this->update($imageData, array("id" => $userId));
            }
            if (isset($_FILES['user_image']['name']) && $_FILES['user_image']['name'] != '') {
                // Sanitize the base directory path
                $baseDirectory = UPLOAD_PATH . DIRECTORY_SEPARATOR . "users";
                $safeDirectoryPath = CommonService::buildSafePath(UPLOAD_PATH, ['users']); // Use buildSafePath to sanitize path

                if ($safeDirectoryPath !== false) {
                    // Extract the file extension
                    $extension = strtolower(pathinfo($_FILES['user_image']['name'], PATHINFO_EXTENSION));

                    // Construct a preliminary filename and clean it using preg_replace
                    $prelimFileName = time() . '_' . $userId; // Filename before sanitization
                    $prelimFileName = preg_replace('/[^a-zA-Z0-9_-]/', '', $prelimFileName); // Strip out any invalid characters

                    // Now clean the file name further using cleanFileName()
                    $safeFileName = CommonService::cleanFileName($prelimFileName) . "." . $extension;

                    // Construct the full safe file path
                    $imageFullPath = $safeDirectoryPath . DIRECTORY_SEPARATOR . $safeFileName;

                    // Move the uploaded file to the safe directory
                    if (move_uploaded_file($_FILES["user_image"]["tmp_name"], $imageFullPath)) {
                        // Resize the image if upload was successful
                        $resizeObj = new ImageResizeService($imageFullPath);
                        $resizeObj->resizeImage(320, 214, 'auto');
                        $resizeObj->saveImage($imageFullPath, 100);

                        // Save the file path relative to the uploads folder
                        $imageUpName = '/uploads/users/' . $safeFileName;
                        $imageData = array('user_image' => $imageUpName);
                        $this->update($imageData, array("id" => $userId));
                    }
                } else {
                    // Handle error if directory creation fails
                    echo "Failed to create directory.";
                }
            }

            return $userId;
        }
    }

    public function updatePassword($params)
    {
        $loginContainer = new Container('credo');
        $userId = $loginContainer->userId;
        $password = $this->passwordHash($params['newpassword']);
        $data = array('password' => $password);
        return $this->update($data, array('id' => $userId));
    }

    public function checkPassword($params)
    {
        $sql = new Sql($this->adapter);
        $loginContainer = new Container('credo');
        $sQuery = $sql->select()->from(array('u' => 'users'))
            ->where(array('id' => $loginContainer->userId));
        $sQueryStr = $sql->buildSqlString($sQuery);

        /** @var \Laminas\Db\Adapter\Driver\ResultInterface $sResult */
        $sResult = $this->adapter->query($sQueryStr, $this->adapter::QUERY_MODE_EXECUTE)->current();

        $passwordValidation = false;

        if ($sResult) {
            $passwordValidation = password_verify($params['password'], $sResult->password);
        }
        return $passwordValidation;
    }

    public function resetPassword($params)
    {
        $userId = base64_decode($params->userId);
        $password = $this->passwordHash($params['newPassword']);
        $data = array('password' => $password);
        return $this->update($data, array('id' => $userId));
    }

    public function updateUserOtp($userId, $mobileNo)
    {
        $dbAdapter = $this->adapter;
        $userOtp = CommonService::generateOTP();
        $gTable = new GlobalTable($dbAdapter);
        $sid = $gTable->getGlobalValue('whatsapp_sid');
        $token = $gTable->getGlobalValue('whatsapp_token');

        //$twilio = new Client($sid, $token);

        $otpData = array('otp' => $userOtp, 'otp_generated_datetime' => CommonService::getDateTime());
        $this->update($otpData, array("id" => $userId));
    }

    public function validateUserOtp($otp, $expiry = 30)
    {
        if (trim($otp) != "") {
            $sql = new Sql($this->adapter);
            $loginContainer = new Container('credo');
            $container = new Container('alert');
            $dbAdapter = $this->adapter;
            $query = $sql->select()->from(['u' => 'users'])
                ->join(['urm' => 'user_role_map'], 'urm.user_id=u.id', ['role_id'])
                ->join(['r' => 'roles'], 'r.role_id=urm.role_id', ['role_name', 'role_code'])
                ->where(['u.id' => $loginContainer->otpId, 'otp' => $otp, 'u.status' => 'active']);
            $queryStr = $sql->buildSqlString($query);
            $sResult = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
            if ($sResult) {
                $userMappedIdsArray = [];
                $userMappingType = '';
                if (!$this->select(array('TIMESTAMPDIFF(MINUTE, otp_generated_datetime, now()) <= ' . $expiry, 'id' => $loginContainer->otpId))) {
                    $container->alertMsg = 'The sms code has expired. Please re-send the OTP';
                    return 'login';
                }

                return $this->userEventLog($sResult);
            } else {
                $container->alertMsg = 'Please enter valid otp';
                return 'validate-otp';
            }
        }
    }

    public function userEventLog($sResult)
    {
        $dbAdapter = $this->adapter;
        $userLocationMapTable = new UserLocationMapTable($dbAdapter);
        $sql = new Sql($this->adapter);
        $gTable = new GlobalTable($dbAdapter);
        $trackTable = new EventLogTable($dbAdapter);
        $userHistoryTable = new UserLoginHistoryTable($dbAdapter);
        $loginContainer = new Container('credo');
        $userLocationMapResult = $userLocationMapTable->fetchSelectedLocation($sResult->id);

        if (!empty($userLocationMapResult)) {
            // Convert object to array if needed
            $userLocationMapResult = (array) $userLocationMapResult;

            // Check if it's a single associative array (not indexed), wrap it in an array
            if (!isset($userLocationMapResult[0])) {
                $userLocationMapResult = [$userLocationMapResult];
            }
        } else {
            $userLocationMapResult = [];
        }
        foreach ($userLocationMapResult as $ulMap) {
            $userMappedIdsArray[] = $ulMap['location_id'];
            $userMappingType = $ulMap['mapping_type'];
        }

        $token = [];
        $userTokenQuery = $sql->select()->from(['u_t_map' => 'user_token_map'])
            ->columns(['token'])
            ->where(['user_id' => $sResult->id])
            ->order("token ASC");
        $userTokenQueryStr = $sql->buildSqlString($userTokenQuery);
        $userTokenResult = $dbAdapter->query($userTokenQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        foreach ($userTokenResult as $userToken) {
            $token[] = $userToken['token'];
        }

        $language = $gTable->getGlobalValue('language');
        if (isset($sResult->language) && !empty($sResult->language)) {
            $loginContainer->language = $sResult->language;
        } elseif (!empty($language)) {
            $loginContainer->language = $language;
        } else {
            $loginContainer->language = 'en_US';
        }
        $loginContainer->userId = $sResult->id;
        $loginContainer->login = $sResult->login;
        $loginContainer->roleCode = $sResult->role_code;
        $loginContainer->roleId = $sResult->role_id;
        $loginContainer->token = $token;
        $loginContainer->userMappedIds = $userMappedIdsArray;
        $loginContainer->userMappingType = $userMappingType;
        $loginContainer->userImage = $sResult->user_image;
        $subject = '';
        $eventType = 'login in';
        $action = $sResult->login . ' logged in';
        $resourceName = 'login in';
        $trackTable->addEventLog($subject, $eventType, $action, $resourceName);
        $userHistoryTable->userHistoryLog($sResult->login, $loginStatus = 'successful');

        $redirect = $gTable->getGlobalValue('web_version');
        $redirectRoute = 'dashboard';
        if (isset($redirect) && !empty($redirect) && !is_array($redirect)) {
            if (!empty($redirect) && $redirect == 'v6') {
                $redirectRoute = 'dashboard-v6';
            }
            if (!empty($redirect) && $redirect == 'v3') {
                $redirectRoute = 'dashboard';
            }
        }
        return $redirectRoute;
    }
}

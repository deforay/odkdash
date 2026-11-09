<?php

namespace Application\Model;

use App\Db\DataTables;
use Application\Session\Container;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Application\Model\GlobalTable;
use Application\Model\EventLogTable;
use Application\Service\CommonService;
use \Application\Service\ImageResizeService;
use Application\Model\UserLocationMapTable;

class UsersTable
{
    private readonly Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }


    public function login($params, $configResult)
    {
        $container = new Container('alert');
        $username = $params['username'];

        $trackTable = new EventLogTable($this->db);
        $sResult = $this->db->fetchAssociative(
            "SELECT u.*, urm.role_id, r.role_name, r.role_code
            FROM users u
            JOIN user_role_map urm ON urm.user_id = u.id
            JOIN roles r ON r.role_id = urm.role_id
            WHERE u.login = ? AND u.status = 'active'",
            [$username]
        );

        $passwordValidation = false;
        if ($sResult) {
            $passwordValidation = password_verify($params['password'], $sResult['password']);
        }

        if ($sResult) {
            $this->db->update('users', ['last_login_datetime' => CommonService::getDateTime()], ['id' => $sResult['id']]);
        }

        if ($sResult && $passwordValidation) {
            return $this->userEventLog($sResult);
        } else {
            $reason = $sResult ? 'invalid password' : 'unknown user';
            $trackTable->addEventLog('', 'login-failed', "Failed login for '{$username}' ({$reason})", 'login');
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

    /**
     * Only a Super Admin may change a Super Admin account or give anyone the
     * Super Admin role. Without this, anyone allowed to edit users could
     * promote themselves, demote a Super Admin, or reset a Super Admin's
     * password and sign in as them.
     *
     * @param int $targetUserId the account being changed, 0 for a new one
     * @param mixed $newRoleId the role being assigned, null when unchanged
     */
    private function mayManage(int $targetUserId, mixed $newRoleId): bool
    {
        if ((new Container('credo'))->roleCode === 'SA') {
            return true;
        }
        $saRoleIds = array_map('intval', $this->db->fetchFirstColumn("SELECT role_id FROM roles WHERE role_code = 'SA'"));
        if ($newRoleId !== null && in_array((int) $newRoleId, $saRoleIds, true)) {
            return false;
        }
        if ($targetUserId > 0 && $saRoleIds !== []) {
            $isSuperAdmin = $this->db->fetchOne(
                'SELECT 1 FROM user_role_map WHERE user_id = ? AND role_id IN (?)',
                [$targetUserId, $saRoleIds],
                [ParameterType::INTEGER, ArrayParameterType::INTEGER]
            );
            if ($isSuperAdmin !== false) {
                return false;
            }
        }
        return true;
    }

    public function addUserDetails($params, $configResult)
    {
        if (!$this->mayManage(0, $params['roleId'] ?? null)) {
            return 0;
        }
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
            $this->db->insert('users', $data);
            $lastInsertId = (int) $this->db->lastInsertId();
            if ($lastInsertId > 0) {
                $this->db->insert('user_role_map', ['user_id' => $lastInsertId, 'role_id' => $params['roleId']]);
                $this->saveLocationsAndTokens($lastInsertId, $params);
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
                        $this->db->update('users', $imageData, ['id' => $lastInsertId]);
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
        $userId = base64_decode($params['userId']);
        if (!$this->mayManage((int) $userId, $params['roleId'] ?? null)) {
            return 0;
        }
        if (isset($params['password']) && $params['password'] != '') {
            $password = $this->passwordHash($params['password']);
            $this->db->update('users', ['password' => $password], ['id' => $userId]);
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
            $this->db->update('users', $data, ['id' => $userId]);
            if ($userId > 0) {
                $this->db->update('user_role_map', ['role_id' => $params['roleId']], ['user_id' => $userId]);
                $this->db->delete('user_location_map', ['user_id' => $userId]);
                $this->db->delete('user_token_map', ['user_id' => $userId]);
                $this->saveLocationsAndTokens((int) $userId, $params);
            }
            if (isset($params['existImage']) && $params['existImage'] == '' && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users")) {
                $fileName = preg_replace('/[^a-zA-Z0-9_-]/', '', $params['existImage']);
                $cleanedFileName = CommonService::cleanFileName($fileName);
                $cleanedFilePath = CommonService::buildSafePath(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users" . DIRECTORY_SEPARATOR, []);
                unlink($cleanedFilePath . $cleanedFileName);
                $imageData = array('user_image' => '');
                $result = $this->db->update('users', $imageData, ['id' => $userId]);
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
                        $this->db->update('users', $imageData, ['id' => $userId]);
                    }
                } else {
                    // Handle error if directory creation fails
                    echo "Failed to create directory.";
                }
            }

            return $userId;
        }
    }

    /** Saves the locations and tokens chosen on the add and edit screens. */
    private function saveLocationsAndTokens(int $userId, $params): void
    {
        $mappingType = $params['mappingType'] ?? '';
        if (in_array($mappingType, ['country', 'province', 'district'], true) && !empty($params[$mappingType])) {
            foreach ($params[$mappingType] as $location) {
                $this->db->insert('user_location_map', ['user_id' => $userId, 'location_id' => $location, 'mapping_type' => $mappingType]);
            }
        }

        if (isset($params['token']) && trim($params['token']) != '') {
            foreach (explode(',', $params['token']) as $token) {
                $this->db->insert('user_token_map', ['user_id' => $userId, 'token' => trim($token)]);
            }
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
         * SQL queries
         * Get data to display
         */
        // The search reads $aColumns and the sort $orderColumns, as the table shows them.
        [$search, $values] = DataTables::search($aColumns, $parameters);
        $where = $search !== '' ? " WHERE $search" : '';
        $order = DataTables::order($orderColumns, $parameters);

        $rResult = $this->db->fetchAllAssociative(
            "SELECT * FROM users$where" . ($order !== '' ? " ORDER BY $order" : '') . DataTables::limit($parameters),
            $values
        );
        $iFilteredTotal = (int) $this->db->fetchOne("SELECT COUNT(*) FROM users$where", $values);

        /* Total data set length */
        $iTotal = (int) $this->db->fetchOne('SELECT COUNT(*) FROM users');
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

            $row[] = CommonService::escapeHtml(ucwords($aRow['first_name'] . " " . $aRow['last_name']));
            $row[] = CommonService::escapeHtml($aRow['email']);
            $row[] = CommonService::escapeHtml(ucwords($aRow['status']));
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
        $user = $this->db->fetchAssociative(
            'SELECT u.*, urm.role_id FROM users u LEFT JOIN user_role_map urm ON urm.user_id = u.id WHERE u.id = ?',
            [$id]
        );
        if ($user === false) {
            return null;
        }
        $user['userToken'] = $this->db->fetchAllAssociative('SELECT token FROM user_token_map WHERE user_id = ? ORDER BY token ASC', [$id]);

        // The templates read the user as $result->name and as $result['name'].
        return new \ArrayObject($user, \ArrayObject::ARRAY_AS_PROPS);
    }

    public function logout($username)
    {
        $dbConnection = $this->db;
        $trackTable = new EventLogTable($dbConnection);
        $subject = '';
        $eventType = 'log-out';
        $action = $username . ' logged out';
        $resourceName = 'log-out';
        $trackTable->addEventLog($subject, $eventType, $action, $resourceName);
    }

    public function updateUserProfileDetails($params)
    {
        // The profile is the signed-in user's own, whatever id the form sends.
        $userId = (new Container('credo'))->userId;
        if (!empty($userId)) {
            $data = array(
                'first_name' => $params['firstName'],
                'last_name' => $params['lastName'],
                'email' => $params['email'],
                'contact_no' => $params['mobile_no']
            );
            $this->db->update('users', $data, ['id' => $userId]);
            if (isset($params['existImage']) && $params['existImage'] == '' && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users")) {
                $fileName = preg_replace('/[^a-zA-Z0-9_-]/', '', $params['removedImage']);
                $cleanedFileName = CommonService::cleanFileName($fileName);
                $cleanedFilePath = CommonService::buildSafePath(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users" . DIRECTORY_SEPARATOR, []);

                unlink($cleanedFilePath . $cleanedFileName);
                $imageData = array('user_image' => '');
                $result = $this->db->update('users', $imageData, ['id' => $userId]);
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
                        $this->db->update('users', $imageData, ['id' => $userId]);
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
        return $this->db->update('users', $data, ['id' => $userId]);
    }

    public function checkPassword($params)
    {
        $hash = $this->db->fetchOne('SELECT password FROM users WHERE id = ?', [(new Container('credo'))->userId]);

        return is_string($hash) && password_verify($params['password'], $hash);
    }

    public function resetPassword($params)
    {
        $userId = base64_decode($params->userId);
        if (!$this->mayManage((int) $userId, null)) {
            return 0;
        }
        $password = $this->passwordHash($params['newPassword']);
        $data = array('password' => $password);
        return $this->db->update('users', $data, ['id' => $userId]);
    }

    /** The users whose login or email is $user. Used by bin/console reset-password. */
    public function findByLoginOrEmail(string $user): array
    {
        return $this->db->fetchAllAssociative('SELECT id, login, email FROM users WHERE login = ? OR email = ?', [$user, $user]);
    }

    public function setPasswordHash(int $userId, string $hash): int
    {
        return $this->db->update('users', ['password' => $hash], ['id' => $userId]);
    }

    /**
     * The scope of an active user who is not signed in, such as the
     * requester of a queued bulk download. Null when the user is missing or
     * inactive, so the caller can refuse rather than fall back to all access.
     *
     * @return array{roleCode: string, token: list<string>, userMappedIds: ?list<string>, userMappingType: ?string}|null
     */
    public function scopeOf(int $userId): ?array
    {
        $roleCode = $this->db->fetchOne(
            "SELECT r.role_code
            FROM users u
            JOIN user_role_map urm ON urm.user_id = u.id
            JOIN roles r ON r.role_id = urm.role_id
            WHERE u.id = ? AND u.status = 'active'",
            [$userId]
        );
        if ($roleCode === false) {
            return null;
        }
        return ['roleCode' => (string) $roleCode] + $this->scopeFor($userId, (string) $roleCode);
    }

    /**
     * The token and location limits the login puts in the session.
     *
     * @return array{token: list<string>, userMappedIds: ?list<string>, userMappingType: ?string}
     */
    private function scopeFor(int $userId, string $roleCode): array
    {
        // A Super Admin has all access: no location mapping and no token
        // restriction, whatever is assigned on the user screen. That is also
        // who sees the audits whose location cannot be resolved, to fix them.
        if ($roleCode === 'SA') {
            return ['token' => [], 'userMappedIds' => null, 'userMappingType' => null];
        }
        $userMappedIdsArray = null;
        $userMappingType = null;
        foreach ((new UserLocationMapTable($this->db))->fetchSelectedLocation($userId) as $ulMap) {
            $userMappedIdsArray[] = $ulMap['location_id'];
            $userMappingType = $ulMap['mapping_type'];
        }

        $token = $this->db->fetchFirstColumn('SELECT token FROM user_token_map WHERE user_id = ? ORDER BY token ASC', [$userId]);

        return ['token' => $token, 'userMappedIds' => $userMappedIdsArray, 'userMappingType' => $userMappingType];
    }

    public function userEventLog($sResult)
    {
        $dbConnection = $this->db;
        $gTable = new GlobalTable($dbConnection);
        $trackTable = new EventLogTable($dbConnection);
        $loginContainer = new Container('credo');
        $scope = $this->scopeFor((int) $sResult['id'], (string) $sResult['role_code']);

        $language = $gTable->getGlobalValue('language');
        if (isset($sResult['language']) && !empty($sResult['language'])) {
            $loginContainer->language = $sResult['language'];
        } elseif (!empty($language)) {
            $loginContainer->language = $language;
        } else {
            $loginContainer->language = 'en_US';
        }
        $loginContainer->userId = $sResult['id'];
        $loginContainer->login = $sResult['login'];
        $loginContainer->roleCode = $sResult['role_code'];
        $loginContainer->roleId = $sResult['role_id'];
        $loginContainer->token = $scope['token'];
        $loginContainer->userMappedIds = $scope['userMappedIds'];
        $loginContainer->userMappingType = $scope['userMappingType'];
        $loginContainer->userImage = $sResult['user_image'];
        $subject = '';
        $eventType = 'login in';
        $action = $sResult['login'] . ' logged in';
        $resourceName = 'login in';
        $trackTable->addEventLog($subject, $eventType, $action, $resourceName);

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

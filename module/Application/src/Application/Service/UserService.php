<?php

namespace Application\Service;

use Laminas\Session\Container;

class UserService
{

    public $sm = null;
    public \Application\Model\UsersTable $usersTable;

    public function __construct($sm, $usersTable)
    {
        $this->sm = $sm;
        $this->usersTable = $usersTable;
    }

    public function getServiceManager()
    {
        return $this->sm;
    }

    public function login($params)
    {
        $configResult = $this->sm->get('Config');
        return $this->usersTable->login($params, $configResult);
    }

    public function addUser($params)
    {
        $adapter = $this->sm->get('Laminas\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $configResult = $this->sm->get('Config');
            $result = $this->usersTable->addUserDetails($params, $configResult);
            if ($result > 0) {
                $adapter->commit();
                //<-- Event log
                $subject = $result;
                $eventType = 'user-add';
                $action = 'added a new user ' . $params['userName'];
                $resourceName = 'users';
                $eventLogDb = $this->sm->get('EventLogTable');
                $eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
                //-------->
                $container = new Container('alert');
                $container->alertMsg = 'User added successfully';
            }
        } catch (\Exception $exc) {
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    public function updateUser($params)
    {
        $adapter = $this->sm->get('Laminas\Db\Adapter\Adapter')->getDriver()->getConnection();
        $configResult = $this->sm->get('Config');
        $adapter->beginTransaction();
        try {
            $result = $this->usersTable->updateUserDetails($params, $configResult);
            if ($result > 0) {
                $adapter->commit();
                //<-- Event log
                $subject = $result;
                $eventType = 'user-update';
                $action = 'updates a user ' . $params['userName'];
                $resourceName = 'users';
                $eventLogDb = $this->sm->get('EventLogTable');
                $eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
                //-------->
                $container = new Container('alert');
                $container->alertMsg = 'User details updated successfully';
            }
        } catch (\Exception $exc) {
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    public function getAllUsers($parameters)
    {

        $acl = $this->sm->get('AppAcl');
        return $this->usersTable->fetchAllUsers($parameters, $acl);
    }

    public function getUser($id)
    {
        return $this->usersTable->fetchUser($id);
    }

    public function logout($params)
    {
        return $this->usersTable->logout($params);
    }

    public function updateUserProfile($params)
    {
        $adapter = $this->sm->get('Laminas\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $result = $this->usersTable->updateUserProfileDetails($params);
            if ($result > 0) {
                $adapter->commit();
                //<-- Event log
                $subject = $result;
                $eventType = 'user-profile-update';
                $action = 'updates a user profile' . $params['userName'];
                $resourceName = 'users';
                $eventLogDb = $this->sm->get('EventLogTable');
                $eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
                //-------->
                $container = new Container('alert');
                $container->alertMsg = 'User Profile details updated successfully';
            }
        } catch (\Exception $exc) {
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    public function checkPassword($params)
    {
        try {
            $result = $this->usersTable->checkPassword($params);
            if ($result === true) {
                return 1;
            } else {
                return 0;
            }
        } catch (\Exception $exc) {
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    public function updatePassword($params)
    {
        $adapter = $this->sm->get('Laminas\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $result = $this->usersTable->updatePassword($params);
            if ($result > 0) {
                $adapter->commit();
                //<-- Event log
                $subject = $result;
                $eventType = 'user-password-update';
                $action = 'updates a user password' . $params['userName'];
                $resourceName = 'users';
                $eventLogDb = $this->sm->get('EventLogTable');
                $eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
                //-------->
                $container = new Container('alert');
                $container->alertMsg = 'Password changed successfully';
            }
        } catch (\Exception $exc) {
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    public function validateUserOtp($otp)
    {
        return $this->usersTable->validateUserOtp($otp);
    }
}

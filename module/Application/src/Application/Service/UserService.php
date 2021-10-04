<?php

namespace Application\Service;

use Laminas\Session\Container;

class UserService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }

    public function login($params) {
        $db = $this->sm->get('UsersTable');
        return $db->login($params);
    }
    
    public function addUser($params) {
        $adapter = $this->sm->get('Laminas\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $userDb = $this->sm->get('UsersTable');
            $result = $userDb->addUserDetails($params);
            if ($result > 0) {
                $adapter->commit();
                //<-- Event log
                $subject = $result;
                $eventType = 'user-add';
                $action = 'added a new user '.$params['userName'];
                $resourceName = 'users';
                $eventLogDb = $this->sm->get('EventLogTable');
                $eventLogDb->addEventLog($subject,$eventType,$action,$resourceName);
                //-------->
                $container = new Container('alert');
                $container->alertMsg = 'User added successfully';
            }
        } catch (Exception $exc) {
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function updateUser($params) {
        $adapter = $this->sm->get('Laminas\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $userDb = $this->sm->get('UsersTable');
            $result = $userDb->updateUserDetails($params);
            if ($result > 0) {
                $adapter->commit();
                //<-- Event log
                $subject = $result;
                $eventType = 'user-update';
                $action = 'updates a user '.$params['userName'];
                $resourceName = 'users';
                $eventLogDb = $this->sm->get('EventLogTable');
                $eventLogDb->addEventLog($subject,$eventType,$action,$resourceName);
                //-------->
                $container = new Container('alert');
                $container->alertMsg = 'User details updated successfully';
            }
        } catch (Exception $exc) {
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getAllUsers($parameters){
        $userDb = $this->sm->get('UsersTable');
        $acl = $this->sm->get('AppAcl');
        return $userDb->fetchAllUsers($parameters,$acl);
    }
    
    public function getUser($id){
        $userDb = $this->sm->get('UsersTable');
        return $userDb->fetchUser($id);
    }

    public function logout($params) {
        $db = $this->sm->get('UsersTable');
        return $db->logout($params);
    }

    public function updateUserProfile($params) {
        $adapter = $this->sm->get('Laminas\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $userDb = $this->sm->get('UsersTable');
            $result = $userDb->updateUserProfileDetails($params);
            if ($result > 0) {
                $adapter->commit();
                //<-- Event log
                $subject = $result;
                $eventType = 'user-profile-update';
                $action = 'updates a user profile'.$params['userName'];
                $resourceName = 'users';
                $eventLogDb = $this->sm->get('EventLogTable');
                $eventLogDb->addEventLog($subject,$eventType,$action,$resourceName);
                //-------->
                $container = new Container('alert');
                $container->alertMsg = 'User Profile details updated successfully';
            }
        } catch (Exception $exc) {
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    public function updatePassword($params) {
        $adapter = $this->sm->get('Laminas\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $userDb = $this->sm->get('UsersTable');
            $result = $userDb->updatePassword($params);
            if ($result > 0) {
                $adapter->commit();
                //<-- Event log
                $subject = $result;
                $eventType = 'user-password-update';
                $action = 'updates a user password'.$params['userName'];
                $resourceName = 'users';
                $eventLogDb = $this->sm->get('EventLogTable');
                $eventLogDb->addEventLog($subject,$eventType,$action,$resourceName);
                //-------->
                $container = new Container('alert');
                $container->alertMsg = 'Password changed successfully';
            }
        } catch (Exception $exc) {
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
}
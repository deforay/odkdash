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
 * @author amit
 */
class UsersTable extends AbstractTableGateway {

    protected $table = 'users';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    
    public function login($params) {
        
        $username = $params['username'];
        $password = $params['password'];

        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from(array('u' => 'users'))
                ->where(array('login' => $username, 'password' => $password));
        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
        
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        //\Zend\Debug\Debug::dump($rResult);die;
        $container = new Container('alert');
        $logincontainer = new Container('credo');
        if (count($rResult) > 0) {
            $logincontainer->userId = $rResult[0]["id"];
            $logincontainer->login = $rResult[0]["login"];
            return 'home';
        } else {
            $container->alertMsg = 'Please check your login credentials';
            return 'login';
        }
    }
    
    
    
}

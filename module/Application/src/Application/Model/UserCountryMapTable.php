<?php

namespace Application\Model;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Sql\Expression;
use Laminas\Session\Container;
use Application\Service\CommonService;
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
class UserCountryMapTable extends AbstractTableGateway {

    protected $table = 'user_country_map';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function fetchSelectedCountry($id){
        return $this->select(array('user_id' => $id))->toArray();
    }
    
}
?>
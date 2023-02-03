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
class CountriesTable extends AbstractTableGateway {

    protected $table = 'countries';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function fetchAllCountries(){
        return $this->select()->toArray();
    }
    
    public function fetchMapedCountries(){
        $logincontainer = new Container('credo');
        if(isset($logincontainer->userCountryMap) && !empty($logincontainer->userCountryMap))
            $result = $this->select(array('country_id IN('.implode(",", $logincontainer->userCountryMap).')'))->toArray();
                else
            $result = $this->select()->toArray();
        $response = array();
        foreach($result as $row){
            $response[$row['country_id']] = $row;
        }
        return $response;
    }


    
}
?>
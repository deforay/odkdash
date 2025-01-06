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
class CountriesTable extends AbstractTableGateway
{

    protected $table = 'countries';
    protected $adapter;
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function fetchAllCountries()
    {
        return $this->select(['status' => 'active'])->toArray();
    }

    public function fetchMapedCountries()
    {
        $loginContainer = new Container('credo');
        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType == 'country') {
            $result = $this->select(array('status' => 'active', 'country_id IN(' . implode(",", $loginContainer->userMappedIds) . ')'))->toArray();
        } else {
            $result = $this->select(array('status' => 'active'))->toArray();
        }

        $response = [];
        foreach ($result as $row) {
            $response[$row['country_id']] = $row;
        }
        return $response;
    }

    public function fetchAllMapedCountries()
    {
        $loginContainer = new Container('credo');
        if (!empty($loginContainer->userMappedIds) && is_array($loginContainer->userMappedIds) && $loginContainer->userMappingType == 'country') {
            $result = $this->select(array('country_id IN(' . implode(",", $loginContainer->userMappedIds) . ')'))->toArray();
        } else {
            $result = $this->select()->toArray();
        }
        return $result;
    }
}

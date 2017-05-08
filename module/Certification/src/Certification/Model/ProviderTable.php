<?php

namespace Certification\Model;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class ProviderTable extends AbstractTableGateway {

    private $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll($paginated = false) {
        if ($paginated) {
            // create a new Select object for the table provider
            $sqlSelect = $this->tableGateway->getSql()->select();
            $sqlSelect->columns(array('id', 'certification_id', 'provider_id', 'last_name', 'first_name', 'middle_name', 'region', 'district', 'type_vih_test', 'phone', 'email', 'prefered_contact_method', 'current_jod', 'facility_id', 'test_site_in_charge'));
            $sqlSelect->join('spi_rt_3_facilities', ' spi_rt_3_facilities.id = provider.facility_id ', array('facility_name'), 'left');
            $sqlSelect->order('last_name asc');
            // create a new result set based on the provider entity
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Provider());
            // create a new pagination adapter object
            $paginatorAdapter = new DbSelect(
                    // our configured select object
                    $sqlSelect,
                    // the adapter to run it against
                    $this->tableGateway->getAdapter(),
                    // the result set to hydrate
                    $resultSetPrototype
            );
            $paginator = new Paginator($paginatorAdapter);
            return $paginator;
        }
        $resultSet = $this->tableGateway->selectWith($sqlSelect);
        return $resultSet;
    }

    public function getProvider($id) {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveProvider(\Certification\Model\Provider $provider) {
        $alphabet = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $last_name = strtoupper($provider->last_name);
        $first_name = ucfirst($provider->first_name);
        $middle_name = ucfirst($provider->middle_name);
        $test_site_in_charge = ucfirst($provider->test_site_in_charge);
        $region = ucfirst($provider->region);
        $district = ucfirst($provider->district);

        $data = array(
            'certification_id' => 'P' . $alphabet[rand(0, 51)] . '-' . rand(0, 999) . $alphabet[rand(0, 51)] . '-' . $alphabet[rand(0, 51)] . rand(0, 999),
            'provider_id' => $provider->provider_id,
            'last_name' => $last_name,
            'first_name' => $first_name,
            'middle_name' => $middle_name,
            'region' =>$region,
            'district' => $district,
            'type_vih_test' => $provider->type_vih_test,
            'phone' => $provider->phone,
            'email' => $provider->email,
            'prefered_contact_method' => $provider->prefered_contact_method,
            'current_jod' => $provider->current_jod,
            'facility_id' => $provider->facility_id,
            'test_site_in_charge' => $test_site_in_charge,
        );

//        print_r($data);
        $id = (int) $provider->id;
        $certification_id = $provider->certification_id;

        if ($id == 0 && !$certification_id) {

            $this->tableGateway->insert($data);
        } else {
            if ($this->getProvider($id)) {
                $data['certification_id'] = $provider->certification_id;
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Provider id does not exist');
            }
        }
    }

    public function search($motCle) {
        $sqlSelect = $this->tableGateway->getSql()->select();
        $sqlSelect->columns(array('id', 'certification_id', 'provider_id', 'last_name', 'first_name', 'middle_name', 'region', 'district', 'type_vih_test', 'phone', 'email', 'prefered_contact_method', 'current_jod', 'facility_id', 'test_site_in_charge'));
        $sqlSelect->join('spi_rt_3_facilities', ' spi_rt_3_facilities.id = provider.facility_id ', array('facility_name'), 'left');
        ;
        $sqlSelect->where->like('last_name', '%' . $motCle . '%');
        $sqlSelect->where->OR->like('first_name', '%' . $motCle . '%');
        $sqlSelect->where->OR->like('middle_name', '%' . $motCle . '%');
        $sqlSelect->where->OR->like('certification_id', '%' . $motCle . '%');
        $sqlSelect->order('last_name ASC');
        ?> 
        <pre><?php // print_r($sqlSelect) ;    ?></pre> <?php
        $resultSet = $this->tableGateway->selectWith($sqlSelect);
        return $resultSet;
    }

}

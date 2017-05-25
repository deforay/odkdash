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
            $sqlSelect->columns(array('id', 'certification_reg_no', 'certification_id', 'professional_reg_no', 'last_name', 'first_name', 'middle_name', 'region', 'district', 'type_vih_test', 'phone', 'email', 'prefered_contact_method', 'current_jod', 'time_worked', 'test_site_in_charge_name', 'test_site_in_charge_phone', 'test_site_in_charge_email', 'facility_in_charge_name', 'facility_in_charge_phone', 'facility_in_charge_email', 'facility_id'));
            $sqlSelect->join('certification_facilities', ' certification_facilities.id = provider.facility_id ', array('facility_name', 'facility_address'), 'left')
                    ->join('certification_regions', 'certification_regions.id =certification_facilities.region', array('region_name'), 'left')
                    ->join('certification_districts', 'certification_districts.id =certification_regions.district', array('district_name'), 'left');
            $sqlSelect->order('id desc');
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

        $last_name = strtoupper($provider->last_name);
        $first_name = ucfirst($provider->first_name);
        $middle_name = ucfirst($provider->middle_name);
        $region = ucfirst($provider->region);
        $district = ucfirst($provider->district);

        $db = $this->tableGateway->getAdapter();
        $sql = 'SELECT MAX(certification_reg_no) as max FROM provider';
        $statement = $db->query($sql);
        $result = $statement->execute();
        foreach ($result as $res) {
            $max = $res['max'];
        }
        $array = explode("R", $max);
        $array2 = explode("-", $max);

        if (date('Y') > $array2[0]) {

            $certification_reg_no = date('Y') . '-R' . substr_replace("0000", 1, -strlen(1));
        } else {

            $certification_reg_no = $array2[0] . '-R' . substr_replace("0000", ($array[1] + 1), -strlen(($array[1] + 1)));
        }


        $data = array(
            'certification_reg_no' => $certification_reg_no,
            'professional_reg_no' => $provider->professional_reg_no,
            'last_name' => $last_name,
            'first_name' => $first_name,
            'middle_name' => $middle_name,
            'region' => $region,
            'district' => $district,
            'type_vih_test' => $provider->type_vih_test,
            'phone' => $provider->phone,
            'email' => $provider->email,
            'prefered_contact_method' => $provider->prefered_contact_method,
            'current_jod' => $provider->current_jod,
            'time_worked' => $provider->time_worked,
            'test_site_in_charge_name' => $provider->test_site_in_charge_name,
            'test_site_in_charge_phone' => $provider->test_site_in_charge_phone,
            'test_site_in_charge_email' => $provider->test_site_in_charge_email,
            'facility_in_charge_name' => $provider->facility_in_charge_name,
            'facility_in_charge_phone' => $provider->facility_in_charge_phone,
            'facility_in_charge_email' => $provider->facility_in_charge_email,
            'facility_id' => $provider->facility_id,
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

    public function search($motCle, $paginated = false) {
       if ($paginated) {
            // create a new Select object for the table provider
            $sqlSelect = $this->tableGateway->getSql()->select();
            $sqlSelect->columns(array('id', 'certification_reg_no', 'certification_id', 'professional_reg_no', 'last_name', 'first_name', 'middle_name', 'region', 'district', 'type_vih_test', 'phone', 'email', 'prefered_contact_method', 'current_jod', 'time_worked', 'test_site_in_charge_name', 'test_site_in_charge_phone', 'test_site_in_charge_email', 'facility_in_charge_name', 'facility_in_charge_phone', 'facility_in_charge_email', 'facility_id'));
            $sqlSelect->join('certification_facilities', ' certification_facilities.id = provider.facility_id ', array('facility_name', 'facility_address'), 'left')
                    ->join('certification_regions', 'certification_regions.id =certification_facilities.region', array('region_name'), 'left')
                    ->join('certification_districts', 'certification_districts.id =certification_regions.district', array('district_name'), 'left');

            $sqlSelect->where->like('last_name', '%' . $motCle . '%');
            $sqlSelect->where->OR->like('first_name', '%' . $motCle . '%');
            $sqlSelect->where->OR->like('middle_name', '%' . $motCle . '%');
            $sqlSelect->where->OR->like('certification_reg_no', '%' . $motCle . '%');
            $sqlSelect->order('last_name ASC');
            
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
            return  $paginator;
        }
        
        $resultSet = $this->tableGateway->selectWith($sqlSelect);
        return  $resultSet;
    }

}

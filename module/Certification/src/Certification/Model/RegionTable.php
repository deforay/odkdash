<?php
namespace Certification\Model;

 use Zend\Db\TableGateway\TableGateway;

 class RegionTable
 {
     protected $tableGateway;

     public function __construct(TableGateway $tableGateway)
     {
         $this->tableGateway = $tableGateway;
     }

     public function fetchAll()
     {
         $resultSet = $this->tableGateway->select();
         return $resultSet;
     }

     public function getRegion($id)
     {
         $id  = (int) $id;
         $rowset = $this->tableGateway->select(array('id' => $id));
         $row = $rowset->current();
         if (!$row) {
             throw new \Exception("Could not find row $id");
         }
         return $row;
     }

     public function saveRegion(Region $region)
     {
         $data = array(
              'region_name'  =>strtoupper($region->region_name),
         );

         $id = (int) $region->id;
         if ($id == 0) {
             $this->tableGateway->insert($data);
         } else {
             if ($this->getRegion($id)) {
                 $this->tableGateway->update($data, array('id' => $id));
             } else {
                 throw new \Exception('Region id does not exist');
             }
         }
     }

    
 }


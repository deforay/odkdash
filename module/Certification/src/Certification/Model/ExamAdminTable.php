<?php
namespace Certification\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;


class ExamAdminTable extends AbstractTableGateway {

    private $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }
    
     public function fetchAll()
     {
         $resultSet = $this->tableGateway->select();
         return $resultSet;
     }
     
     public function getExamAdmin($exam_admin_by_id)
     {
         $exam_admin_by_id  = (int) $exam_admin_by_id;
         $rowset = $this->tableGateway->select(array('exam_admin_by_id' => $exam_admin_by_id));
         $row = $rowset->current();
         if (!$row) {
             throw new \Exception("Could not find row $exam_admin_by_id");
         }
         return $row;
     }

     public function saveExamAdmin(ExamAdmin $examAdmin)
     {
         $data = array(
              
             'admin_last_name'=>$examAdmin->admin_last_name,
             'admin_first_name'=>$examAdmin->admin_first_name,
            'admin_middle_name'=>$examAdmin->admin_middle_name,
             'district'=>$examAdmin->district,
             'region' =>$examAdmin->region,
             'email'=>$examAdmin->email,
             'phone'=>$examAdmin->phone,
             'prefered_contact_method'=>$examAdmin->prefered_contact_method,
             'current_job'=>$examAdmin->current_job,
              'job_address'=>$examAdmin->job_address,
         );
//         var_dump($examAdmin);
         $exam_admin_by_id = (int) $examAdmin->exam_admin_by_id;
         if ($exam_admin_by_id == 0) {
             $this->tableGateway->insert($data);
         } else {
             if ($this->getExamAdmin($exam_admin_by_id)) {
                 $this->tableGateway->update($data, array('exam_admin_by_id' => $exam_admin_by_id));
             } else {
                 throw new \Exception('Exam Administrator id does not exist');
             }
         }
     }

}

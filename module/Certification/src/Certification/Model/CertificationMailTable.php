<?php

 namespace Certification\Model;

 use Zend\Db\TableGateway\TableGateway;

 class CertificationMailTable
 {
     protected $tableGateway;

     public function __construct(TableGateway $tableGateway)
     {
         $this->tableGateway = $tableGateway;
     }

    
     public function getCertificationMail($mail_id)
     {
         $mail_id  = (int) $mail_id;
         $rowset = $this->tableGateway->select(array('mail_id' => $mail_id));
         $row = $rowset->current();
         if (!$row) {
             throw new \Exception("Could not find row $mail_id");
         }
         return $row;
     }

     public function saveCertificationMail(CertificationMail $CertificationMail)
     {
         $data = array(
             
             'from_full_name'=> $CertificationMail->from_full_name,
             'from_mail'=> $CertificationMail->from_mail,
             'to_email'=> $CertificationMail->to_email,
             'cc'=> $CertificationMail->cc,
             'bcc'=> $CertificationMail->bcc, 
             'subject'=> $CertificationMail->subject,
             'message'=> $CertificationMail->message,
             'status' => $CertificationMail->status,
                      );

         $mail_id = (int) $CertificationMail->mail_id;
         if ($mail_id == 0) {
             $this->tableGateway->insert($data);
         } else {
             if ($this->getCertificationMail($mail_id)) {
                 $this->tableGateway->update($data, array('mail_id' => $mail_id));
             } else {
                 throw new \Exception('Mail id does not exist');
             }
         }
     }

     
 }


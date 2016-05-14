<?php

namespace Application\Service;

use Zend\Session\Container;

class FacilityService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }
    
    public function addFacility($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
            $result = $facilityDb->addFacilityDetails($params);
            if ($result > 0) {
                $adapter->commit();
                //<-- Event log
                $subject = $result;
                $eventType = 'facility-add';
                $action = 'added a new facility '.$params['facilityName'];
                $resourceName = 'Facility';
                $eventLogDb = $this->sm->get('EventLogTable');
                $eventLogDb->addEventLog($subject,$eventType,$action,$resourceName);
                //-------->
                $container = new Container('alert');
                $container->alertMsg = 'Facility details added successfully';
                return $result;
            }
        } catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function updateFacility($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
            $result = $facilityDb->updateFacilityDetails($params);
            if ($result > 0) {
                $adapter->commit();
                //<-- Event log
                $subject = $result;
                $eventType = 'facility-update';
                $action = 'updated a facility '.$params['facilityName'];
                $resourceName = 'Facility';
                $eventLogDb = $this->sm->get('EventLogTable');
                $eventLogDb->addEventLog($subject,$eventType,$action,$resourceName);
                //-------->
                $container = new Container('alert');
                $container->alertMsg = 'Facility details updated successfully';
                return $result;
            }
        } catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getAllFacilities($parameters){
        $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
        $acl = $this->sm->get('AppAcl');
        return $facilityDb->fetchAllFacilities($parameters,$acl);
    }
    
    public function getFacility($id){
        $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
        return $facilityDb->fetchFacility($id);
    }
    
    public function getFacilityList($val){
        $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
        return $facilityDb->fetchFacilityList($val);
    }
    
    public function addEmail($params){
        $result = 0;
        $container = new Container('alert');
        $tempMailDb = $this->sm->get('TempMailTable');
        $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
        $db = $this->sm->get('SpiFormVer3Table');
        $commonService = new \Application\Service\CommonService();
        $config = new \Zend\Config\Reader\Ini();
        $configResult = $config->fromFile(CONFIG_PATH . '/custom.config.ini');
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $fromName = $configResult['admin']['name'];
            $fromEmailAddress = $configResult['admin']['emailAddress'];
            $toName = ucwords($params['facilityName']);
            $toEmailAddress = trim($params['emailAddress']);
            $cc = $configResult['admin']['emailAddress'];
            $subject = 'SPI-RT-CHECKLIST';
            $message = '';
            $message.= '<table border="0" cellspacing="0" cellpadding="0" style="width:100%;background-color:#DFDFDF;">';
              $message.= '<tr><td align="center">';
                $message.= '<table cellpadding="3" style="width:55%;font-family:Helvetica,Arial,sans-serif;margin:30px 0px 30px 0px;padding:2% 0% 0% 2%;background-color:#ffffff;">';
                  $message.= '<tr><td>Hi <strong>'.ucwords($params['facilityName']).'</strong>,</td></tr>';
                  $message.= '<tr><td>Message details is showing below..</td></tr>';
                  $message.= '<tr><td><p>'.ucfirst(trim($params['message'])).'</p></td></tr>';
                $message.= '</table>';
              $message.= '</tr></td>';
            $message.= '</table>';
            $tempId = $tempMailDb->insertTempMailDetails($toEmailAddress,$cc,$subject,$message,$fromName,$fromEmailAddress);
            if($tempId> 0){
                $result = $facilityDb->updateFacilityEmailAddress($params);
                if($result> 0){
                    if (!file_exists(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "email") && !is_dir(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "email")) {
                        mkdir(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "email");
                    }
                    
                    if (!file_exists(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "email" . DIRECTORY_SEPARATOR . $tempId) && !is_dir(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "email" . DIRECTORY_SEPARATOR . $tempId)) {
                        mkdir(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "email" . DIRECTORY_SEPARATOR . $tempId);
                    }
                    
                    //Move Attachement File(s)
                    $errorAttachement = 0;
                    if(isset($_FILES['attchement']['name']) && count($_FILES['attchement']['name']) > 0) {
                        for($attch=0;$attch<count($_FILES['attchement']['name']);$attch++){
                            if(trim($_FILES['attchement']['name'][$attch])!= ''){
                                $extension = strtolower(pathinfo(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $_FILES['attchement']['name'][$attch], PATHINFO_EXTENSION));
                                $fileName = $commonService->generateRandomString(5, 'alphanum') . "." . $extension;
                                if (move_uploaded_file($_FILES["attchement"]["tmp_name"][$attch], TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "email" . DIRECTORY_SEPARATOR . $tempId. DIRECTORY_SEPARATOR . $fileName)) {
                                }else{
                                    $errorAttachement+=1;
                                }
                            }
                        }
                    }
                    $adapter->commit();
                    if($errorAttachement > 0){
                        if($errorAttachement > 1){
                          $container->alertMsg = $errorAttachement. 'attachements were failed to upload!';
                        }else{
                          $container->alertMsg = $errorAttachement. 'attachement was failed to upload!';
                        }
                    }else{
                       $container->alertMsg = 'Mail queue added successfully.';
                    }
                }else{
                    $tempId = 0;
                    $container->alertMsg = 'We have experienced the problem..Please try again!';
                }
            }else{
                $container->alertMsg = 'We have experienced the problem..Please try again!';
            }
          return $tempId;
        }catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        } 
    }
    
    public function getAllTestingPoints($parameters){
        $sbiFormDb = $this->sm->get('SpiFormVer3Table');
        $acl = $this->sm->get('AppAcl');
        return $sbiFormDb->fetchAllTestingPointsBasedOnFacility($parameters,$acl);
    }
}
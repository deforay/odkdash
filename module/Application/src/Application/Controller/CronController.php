<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class CronController extends AbstractActionController {

    public function indexAction() {
        
    }
    
    public function sendMailAction(){
       $commonService = $this->getServiceLocator()->get('CommonService');
       $commonService->sendTempMail();
    }
}
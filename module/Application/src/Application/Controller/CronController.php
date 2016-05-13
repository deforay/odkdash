<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class CronController extends AbstractActionController {

    public function indexAction() {
        return new ViewModel();
    }
    
    public function dbBackupAction(){
        $commonService = $this->getServiceLocator()->get('CommonService');
        $commonService->dbBackup();
    }
    
    
}


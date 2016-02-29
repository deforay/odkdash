<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ReceiverController extends AbstractActionController
{

    public function indexAction()
    {        
        $viewModel = new ViewModel();
        
        $jsonData = file_get_contents('php://input');
        
        $params = json_decode($jsonData,true);
        
        $odkFormService = $this->getServiceLocator()->get('OdkFormService');
        $result = $odkFormService->saveSpiFormVer3($params);
        //$result = $odkFormService->getPerformance($params);
        
        
        $viewModel->setTerminal(true);
        return $viewModel;        
    }

    
    public function var_error_log( $object=null ){
        ob_start();
        var_dump( $object );
        $contents = ob_get_contents();
        ob_end_clean();
        error_log( $contents );
    }
 
}


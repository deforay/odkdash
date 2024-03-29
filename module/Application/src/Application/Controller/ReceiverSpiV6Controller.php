<?php

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class ReceiverSpiV6Controller extends AbstractActionController
{


    private $odkFormService = null;

    public function __construct($odkFormService)
    {
        $this->odkFormService = $odkFormService;
    }
    
    public function indexAction()
    {
        
        $viewModel = new ViewModel();        

        $jsonData = utf8_encode(file_get_contents('php://input'));

        $this->var_error_log($jsonData);

        $params = json_decode($jsonData, true);

        //$this->var_error_log($params);die;
        $result = $this->odkFormService->saveSpiFormVer6($params);
        

        $viewModel->setTerminal(true);
        return $viewModel;
    }


    public function var_error_log($object = null)
    {
        ob_start();
        var_dump($object);
        $contents = ob_get_contents();
        ob_end_clean();
        error_log($contents);
    }
}

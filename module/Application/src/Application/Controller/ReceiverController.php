<?php

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
class ReceiverController extends AbstractActionController
{

    private $odkFormService = null;

    public function __construct($odkFormService)
    {
        $this->odkFormService = $odkFormService;
    }

    public function indexAction()
    {
        
        $jsonData = utf8_encode(file_get_contents('php://input'));
        $params = json_decode($jsonData, true);
        $result = $this->odkFormService->saveSpiFormVer3($params);
        // $this->var_error_log($params);die;
        //Tested for ODK Central
        //$result = $this->odkFormService->getOdkCentralSubmissions();
        
        $viewModel = new ViewModel();
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

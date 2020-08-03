<?php

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\Json\Json;

class ConfigController extends AbstractActionController
{


    private $commonService = null;
    public function __construct($commonService)
    {
        $this->commonService = $commonService;
    }
    public function indexAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->commonService->getAllConfig($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function editGlobalAction()
    {

        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $this->commonService->updateConfig($params);
            return $this->redirect()->toRoute('config');
        } else {
            $configResult = $this->commonService->getGlobalConfigDetails();
            return new ViewModel(array(
                'config' => $configResult,
            ));
        }
    }
}

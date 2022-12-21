<?php

namespace Application\Controller;

use Laminas\Config\Config;
use Laminas\Json\Json;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class UserLoginHistoryController extends AbstractActionController
{


    private $userLoginHistoryService = null;

    public function __construct($userLoginHistoryService)
    {
        $this->userLoginHistoryService = $userLoginHistoryService;
    }


    public function indexAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();

            $result = $this->userLoginHistoryService->getAllDetails($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
}

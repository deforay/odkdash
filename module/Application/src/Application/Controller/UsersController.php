<?php

namespace Application\Controller;

use Laminas\Config\Config;
use Laminas\Json\Json;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class UsersController extends AbstractActionController
{

    private $odkFormService = null;
    private $userService = null;
    private $roleService = null;

    public function __construct($userService, $roleService, $odkFormService)
    {
        $this->userService = $userService;
        $this->roleService = $roleService;
        $this->odkFormService = $odkFormService;
    }    

    public function indexAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->userService->getAllUsers($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function addAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            
            $this->userService->addUser($params);
            return $this->redirect()->toRoute("users");
        }
        
        
        $roleResult = $this->roleService->getAllActiveRoles();
        $tokenResult = $this->odkFormService->getSpiV3FormUniqueTokens();
        return new ViewModel(array('roleResults' => $roleResult, 'tokenResults' => $tokenResult));
    }

    public function editAction()
    {
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->userService->updateUser($params);
            return $this->redirect()->toRoute("users");
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $this->userService->getUser($id);
            
            
            $roleResult = $this->roleService->getAllActiveRoles();
            $tokenResult = $this->odkFormService->getSpiV3FormUniqueTokens();
            return new ViewModel(array(
                'result' => $result,
                'roleResults' => $roleResult,
                'tokenResults' => $tokenResult
            ));
        }
    }

    public function profileAction()
    {
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->userService->updateUserProfile($params);
            return $this->redirect()->toRoute("dashboard");
        } else {
        $id = base64_decode($this->params()->fromRoute('id'));
        $result = $this->userService->getUser($id);
        return new ViewModel(array(
                'result' => $result
            ));
        }
    }
}

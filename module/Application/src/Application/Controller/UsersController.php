<?php

namespace Application\Controller;

use Laminas\Config\Config;
use Laminas\Json\Json;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class UsersController extends AbstractActionController
{

    public \Application\Service\OdkFormService $odkFormService;
    public \Application\Service\UserService $userService;
    public \Application\Service\RoleService $roleService;
    public \Application\Service\CommonService $commonService;

    public function __construct($userService, $roleService, $odkFormService, $commonService)
    {
        $this->userService = $userService;
        $this->roleService = $roleService;
        $this->odkFormService = $odkFormService;
        $this->commonService = $commonService;
    }

    public function indexAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->userService->getAllUsers($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function addAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();

            $this->userService->addUser($params);
            return $this->redirect()->toRoute("users");
        }
        $roleResult = $this->roleService->getAllActiveRoles();
        $tokenResult = $this->odkFormService->getSpiV3FormUniqueTokens();
        $countries = $this->commonService->getAllCountries();
        return new ViewModel(array('roleResults' => $roleResult, 'tokenResults' => $tokenResult, 'countries' => $countries));
    }

    public function editAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->userService->updateUser($params);
            return $this->redirect()->toRoute("users");
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $this->userService->getUser($id);
            $userCountryMapResult = $this->commonService->getSelectedCountry($id);

            $countries = $this->commonService->getAllCountries();
            $roleResult = $this->roleService->getAllActiveRoles();
            $tokenResult = $this->odkFormService->getSpiV3FormUniqueTokens();
            return new ViewModel(array(
                'result' => $result,
                'roleResults' => $roleResult,
                'tokenResults' => $tokenResult,
                'userCountryMapResult' => $userCountryMapResult,
                'countries' => $countries
            ));
        }
    }

    public function profileAction()
    {
        /** @var \Laminas\Http\Request $request */
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

    public function checkPasswordAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $params = $request->getPost();
            echo $this->userService->checkPassword($params);
            $viewModel = new ViewModel();
            $viewModel
                ->setTerminal(true);
            return $viewModel;
        }
    }

    public function changePasswordAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->userService->updatePassword($params);
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

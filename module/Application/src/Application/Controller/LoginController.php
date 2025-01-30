<?php

namespace Application\Controller;

use Application\Service\UserService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\Session\Container;


class LoginController extends AbstractActionController
{


    public UserService $userService;

    public function __construct($userService)
    {
        $this->userService = $userService;
    }

    public function indexAction()
    {
        $loginContainer = new Container('credo');
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $route = $this->userService->login($params);
            return $this->redirect()->toRoute($route);
        }
        if (!empty($loginContainer->userId)) {
            return $this->redirect()->toRoute("dashboard");
        } else {
            $vm = new ViewModel();
            $vm->setTerminal(true);
            return $vm;
        }
    }

    public function validateOtpAction(){
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params=$request->getPost();
            $route=$this->userService->validateUserOtp(trim($params['otp']));
            return $this->redirect()->toRoute($route);
        }
        $vm = new ViewModel();
        $vm->setTerminal(true);
        return $vm;
    }

    public function logoutAction()
    {
        $sessionLogin = new Container('credo');
        $user_name = $sessionLogin->login;
        $route = $this->userService->logout($user_name);
        $sessionLogin->getManager()->getStorage()->clear();
        return $this->redirect()->toRoute("login");
    }
}

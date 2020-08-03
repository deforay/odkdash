<?php

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\Session\Container;

class LoginController extends AbstractActionController
{


    private $userService = null;

    public function __construct($userService)
    {
        $this->userService = $userService;
    }    

    public function indexAction()
    {
        $logincontainer = new Container('credo');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $route = $this->userService->login($params);
            return $this->redirect()->toRoute($route);
        }
        if (isset($logincontainer->userId) && $logincontainer->userId != "") {
            return $this->redirect()->toRoute("dashboard");
        } else {
            $vm = new ViewModel();
            $vm->setTerminal(true);
            return $vm;
        }
    }

    public function logoutAction() {
        $sessionLogin = new Container('credo');
        $sessionLogin->getManager()->getStorage()->clear();
        return $this->redirect()->toRoute("login");
    }    


}


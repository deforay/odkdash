<?php

namespace Application\Controller;

use Zend\Config\Config;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class RolesController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $roleSerive = $this->getServiceLocator()->get('RoleService');
            $result = $roleSerive->getAllRoles($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function addAction() {
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $params = $request->getPost();
            $roleSerive = $this->getServiceLocator()->get('RoleService');
            $roleSerive->addRoles($params);
            return $this->redirect()->toRoute("roles");
        }
    }

    public function editAction() {
        $request = $this->getRequest();
        $roleSerive = $this->getServiceLocator()->get('RoleService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $roleSerive->updateRoles($params);
            return $this->redirect()->toRoute("roles");
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $roleSerive->getRole($id);
            return new ViewModel(array(
                'result' => $result,
            ));
        }
    }

}

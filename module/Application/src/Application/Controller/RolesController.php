<?php

namespace Application\Controller;

use Laminas\Config\Config;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class RolesController extends AbstractActionController
{


    private $roleService = null;

    public function __construct($roleService)
    {
        $this->roleService = $roleService;
    }


    public function indexAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();

            $result = $this->roleService->getAllRolesDetails($params);
            return $this->getResponse()->setContent(CommonService::jsonEncode($result));
        }
    }

    public function addAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $params = $request->getPost();
            $this->roleService->addRoles($params);
            return $this->redirect()->toRoute("roles");
        } else {
            $rolesResult = $this->roleService->getAllRoles();
            return new ViewModel(array(
                'rolesresult' => $rolesResult,
            ));
        }
    }

    public function editAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->roleService->updateRoles($params);
            return $this->redirect()->toRoute("roles");
        } else {
            // $configFile = CONFIG_PATH . DIRECTORY_SEPARATOR . "acl.config.php";

            // $config = \Laminas\Config\Factory::fromFile($configFile, true);

            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $this->roleService->getRole($id);
            $rolesResult = $this->roleService->getAllRoles(); //privileges
            $config = $this->roleService->getPrivilegesMap($id);
            return new ViewModel(array(
                'result' => $result,
                'rolesresult' => $rolesResult,
                'resourceResult' => $config,
            ));
        }
    }
}

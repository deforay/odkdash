<?php

namespace Application\Controller;

use Laminas\View\Model\ViewModel;
use Application\Service\CommonService;
use Laminas\Mvc\Controller\AbstractActionController;

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
        $rolesList = $this->redirect()->toRoute('roles');

        if ($request->isPost()) {
            $params = $request->getPost();
            // Super Admin is locked — UI hides Edit, but block direct POSTs too.
            $isSa = isset($params['roleCode']) && $params['roleCode'] === 'SA';
            if (!$isSa) {
                $this->roleService->updateRoles($params);
            }
            return $rolesList;
        }

        $id = base64_decode($this->params()->fromRoute('id'));
        $result = $this->roleService->getRole($id);

        // Block GET access to SA's edit form — keep the lock honest
        // if someone hand-builds the /roles/edit/<id> URL.
        if (isset($result->role_code) && $result->role_code === 'SA') {
            return $rolesList;
        }

        $rolesResult = $this->roleService->getAllRoles();
        $config = $this->roleService->getPrivilegesMap($id);
        return new ViewModel(array(
            'result' => $result,
            'rolesresult' => $rolesResult,
            'resourceResult' => $config,
        ));
    }
}

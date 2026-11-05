<?php

declare(strict_types=1);

namespace App\Http\Handlers;

use App\Http\Responder;
use Application\Service\CommonService;
use Application\Service\RoleService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RolesHandler extends ActionHandler
{
    public function __construct(
        private readonly Responder $respond,
        private readonly RoleService $roleService,
    ) {
    }

    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $result = $this->roleService->getAllRolesDetails($this->post($request));
            return $this->respond->html(CommonService::jsonEncode($result));
        }
        return $this->respond->page('application/roles/index');
    }

    public function addAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $this->roleService->addRoles($this->post($request));
            return $this->respond->redirect('roles');
        }
        return $this->respond->page('application/roles/add', [
            'rolesresult' => $this->roleService->getAllRoles(),
        ]);
    }

    public function editAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $params = $this->post($request);
            // Super Admin is locked — UI hides Edit, but block direct POSTs too.
            $isSa = isset($params['roleCode']) && $params['roleCode'] === 'SA';
            if (!$isSa) {
                $this->roleService->updateRoles($params);
            }
            return $this->respond->redirect('roles');
        }

        $id = base64_decode((string) $this->routeParam($request, 'id'));
        $result = $this->roleService->getRole($id);

        // Block GET access to SA's edit form — keep the lock honest
        // if someone hand-builds the /roles/edit/<id> URL.
        if (isset($result->role_code) && $result->role_code === 'SA') {
            return $this->respond->redirect('roles');
        }

        return $this->respond->page('application/roles/edit', [
            'result' => $result,
            'rolesresult' => $this->roleService->getAllRoles(),
            'resourceResult' => $this->roleService->getPrivilegesMap($id),
        ]);
    }
}

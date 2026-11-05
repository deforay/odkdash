<?php

declare(strict_types=1);

namespace App\Http\Handlers;

use App\Http\Responder;
use Application\Service\CommonService;
use Application\Service\OdkFormService;
use Application\Service\ProvinceService;
use Application\Service\RoleService;
use Application\Service\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class UsersHandler extends ActionHandler
{
    public function __construct(
        private readonly Responder $respond,
        private readonly UserService $userService,
        private readonly RoleService $roleService,
        private readonly OdkFormService $odkFormService,
        private readonly CommonService $commonService,
        private readonly ProvinceService $provinceService,
    ) {
    }

    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $result = $this->userService->getAllUsers($this->post($request));
            return $this->respond->html(CommonService::jsonEncode($result));
        }
        return $this->respond->page('application/users/index');
    }

    public function addAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $this->userService->addUser($this->post($request));
            return $this->respond->redirect('users');
        }
        return $this->respond->page('application/users/add', [
            'roleResults' => $this->roleService->getAllActiveRoles(),
            'tokenResults' => $this->odkFormService->getSpiV3FormUniqueTokens(),
            'countries' => $this->commonService->getAllCountries(),
            'provinces' => $this->provinceService->getAllProvinces(),
            'districts' => $this->provinceService->getAllDistricts(),
        ]);
    }

    public function editAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $this->userService->updateUser($this->post($request));
            return $this->respond->redirect('users');
        }

        $id = base64_decode((string) $this->routeParam($request, 'id'));
        return $this->respond->page('application/users/edit', [
            'result' => $this->userService->getUser($id),
            'roleResults' => $this->roleService->getAllActiveRoles(),
            'tokenResults' => $this->odkFormService->getSpiV3FormUniqueTokens(),
            'userLocationMapResult' => $this->commonService->getSelectedLocation($id),
            'countries' => $this->commonService->getAllCountries(),
            'provinces' => $this->provinceService->getAllActiveProvinces(),
            'districts' => $this->provinceService->getAllActiveDistricts(),
        ]);
    }

    public function profileAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $this->userService->updateUserProfile($this->post($request));
            return $this->respond->redirect('dashboard');
        }

        $id = base64_decode((string) $this->routeParam($request, 'id'));
        return $this->respond->page('application/users/profile', [
            'result' => $this->userService->getUser($id),
        ]);
    }

    /**
     * Called by the change-password page's JavaScript. Prints the service's
     * answer and nothing else. A GET printed nothing under Laminas.
     */
    public function checkPasswordAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->respond->page('application/users/check-password');
        }
        return $this->respond->html((string) $this->userService->checkPassword($this->post($request)));
    }

    public function changePasswordAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $this->userService->updatePassword($this->post($request));
            return $this->respond->redirect('dashboard');
        }

        $id = base64_decode((string) $this->routeParam($request, 'id'));
        return $this->respond->page('application/users/change-password', [
            'result' => $this->userService->getUser($id),
        ]);
    }

    public function resetPasswordAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            // Rendered without a layout, as under Laminas.
            return $this->respond->fragment('application/users/reset-password', [
                'result' => $this->userService->resetPassword($this->post($request)),
            ]);
        }

        $id = base64_decode((string) $this->routeParam($request, 'id'));
        return $this->respond->page('application/users/reset-password', [
            'result' => $this->userService->getUser($id),
        ], 'layout/modal');
    }
}

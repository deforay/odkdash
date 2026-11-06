<?php

declare(strict_types=1);

namespace App\Http\Handlers;

use App\Http\Responder;
use Application\Service\UserService;
use Application\Session\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Login, the one-time password step and logout. No login is needed. After
 * each step UserService names the route to go to next.
 */
final class LoginHandler extends ActionHandler
{
    public function __construct(
        private readonly Responder $respond,
        private readonly UserService $userService,
    ) {
    }

    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            return $this->respond->redirect($this->userService->login($this->post($request)));
        }
        if (!empty((new Container('credo'))->userId)) {
            return $this->respond->redirect('dashboard');
        }
        return $this->respond->fragment('application/login/index');
    }

    public function validateOtpAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $otp = trim((string) $this->post($request)['otp']);
            return $this->respond->redirect($this->userService->validateUserOtp($otp));
        }
        return $this->respond->fragment('application/login/validate-otp');
    }

    public function logoutAction(ServerRequestInterface $request): ResponseInterface
    {
        $session = new Container('credo');
        $this->userService->logout($session->login);
        $session->getManager()->getStorage()->clear();
        return $this->respond->redirect('login');
    }
}

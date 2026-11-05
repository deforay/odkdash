<?php

declare(strict_types=1);

namespace App\Http\Handlers;

use App\Http\Responder;
use Application\Service\CommonService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ConfigHandler extends ActionHandler
{
    public function __construct(
        private readonly Responder $respond,
        private readonly CommonService $commonService,
    ) {
    }

    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $result = $this->commonService->getAllConfig($this->post($request));
            return $this->respond->html(CommonService::jsonEncode($result));
        }
        return $this->respond->page('application/config/index');
    }

    public function editGlobalAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $this->commonService->updateConfig($this->post($request));
            return $this->respond->redirect('config');
        }
        return $this->respond->page('application/config/edit-global', [
            'config' => $this->commonService->getGlobalConfigDetails(),
        ]);
    }
}

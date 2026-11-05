<?php

declare(strict_types=1);

namespace App\Http\Handlers;

use App\Http\Responder;
use Application\Service\CommonService;
use Application\Service\ProvinceService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ProvincesHandler extends ActionHandler
{
    public function __construct(
        private readonly Responder $respond,
        private readonly ProvinceService $provinceService,
    ) {
    }

    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $result = $this->provinceService->getAllProvinceDetails($this->post($request));
            return $this->respond->html(CommonService::jsonEncode($result));
        }
        return $this->respond->page('application/provinces/index');
    }

    public function addAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $this->provinceService->addProvince($this->post($request));
            return $this->respond->redirect('provinces');
        }
        return $this->respond->page('application/provinces/add');
    }

    public function editAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $this->provinceService->updateProvince($this->post($request));
            return $this->respond->redirect('provinces');
        }

        $id = base64_decode((string) $this->routeParam($request, 'id'));
        return $this->respond->page('application/provinces/edit', [
            'result' => $this->provinceService->getProvince($id),
        ]);
    }

    /**
     * Called by the facility add and edit pages' JavaScript. Prints the
     * district options for a province.
     */
    public function getDistrictByProvinceAction(ServerRequestInterface $request): ResponseInterface
    {
        $result = '';
        if ($this->isPost($request)) {
            $provinceId = base64_decode((string) $this->post($request)['provinceId']);
            $result = $this->provinceService->getAllDistrictByProvince($provinceId);
        }
        return $this->respond->fragment('application/provinces/get-district-by-province', [
            'result' => $result,
        ]);
    }
}

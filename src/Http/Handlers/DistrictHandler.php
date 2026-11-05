<?php

declare(strict_types=1);

namespace App\Http\Handlers;

use App\Http\Responder;
use Application\Service\CommonService;
use Application\Service\ProvinceService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class DistrictHandler extends ActionHandler
{
    public function __construct(
        private readonly Responder $respond,
        private readonly ProvinceService $provinceService,
    ) {
    }

    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $result = $this->provinceService->getAllDistrictDetails($this->post($request));
            return $this->respond->html(CommonService::jsonEncode($result));
        }
        return $this->respond->page('application/district/index');
    }

    public function addAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $this->provinceService->addDistrict($this->post($request));
            return $this->respond->redirect('district');
        }
        return $this->respond->page('application/district/add', [
            'provinceResult' => $this->provinceService->getAllActiveProvinces(),
        ]);
    }

    public function editAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $this->provinceService->updateDistrict($this->post($request));
            return $this->respond->redirect('district');
        }

        // The edit page reads the district from the provinces lookup, as
        // the Laminas controller did.
        $id = base64_decode((string) $this->routeParam($request, 'id'));
        return $this->respond->page('application/district/edit', [
            'result' => $this->provinceService->getProvince($id),
            'provinceResult' => $this->provinceService->getAllActiveProvinces(),
        ]);
    }
}

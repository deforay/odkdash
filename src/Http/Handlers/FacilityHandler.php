<?php

declare(strict_types=1);

namespace App\Http\Handlers;

use App\Http\Responder;
use Application\Service\CommonService;
use Application\Service\FacilityService;
use Application\Service\OdkFormService;
use Application\Service\ProvinceService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The facility pages. Actions that only answer POST (or only GET) render
 * their template inside the layout for the other method, as the Laminas
 * actions did by returning null.
 */
final class FacilityHandler extends ActionHandler
{
    public function __construct(
        private readonly Responder $respond,
        private readonly FacilityService $facilityService,
        private readonly OdkFormService $odkFormService,
        private readonly ProvinceService $provinceService,
    ) {
    }

    /** The facility list, and the table rows it posts back for. */
    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('index');
        }
        $result = $this->facilityService->getAllFacilities($this->post($request));
        return $this->respond->html(CommonService::jsonEncode($result));
    }

    public function addAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $this->facilityService->addFacility($this->post($request));
            return $this->respond->redirect('spi-facility');
        }
        return $this->respond->page('application/facility/add', [
            'provinceResult' => $this->provinceService->getAllActiveProvinces(),
            'countries' => $this->provinceService->getAllMapedCountries(),
        ]);
    }

    public function editAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $this->facilityService->updateFacility($this->post($request));
            return $this->respond->redirect('spi-facility');
        }
        $id = base64_decode((string) $this->routeParam($request, 'id'));
        return $this->respond->page('application/facility/edit', [
            'result' => $this->facilityService->getFacility($id),
            'provinceResult' => $this->provinceService->getAllActiveProvinces(),
            'countries' => $this->provinceService->getAllMapedCountries(),
        ]);
    }

    public function facilityListAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() !== 'GET') {
            return $this->fallback('facility-list');
        }
        $result = $this->facilityService->getFacilityList($this->query($request, 'search'));
        return $this->respond->html(CommonService::jsonEncode($result));
    }

    /** Opened in a modal from the facility list. */
    public function getFacilityNameAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->respond->page('application/facility/get-facility-name', [
            'facilityName' => $this->odkFormService->getAllFacilityNames(),
        ], 'layout/modal');
    }

    public function getTestingPointAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('get-testing-point');
        }
        $result = $this->facilityService->getAllTestingPoints($this->post($request));
        return $this->respond->html(CommonService::jsonEncode($result));
    }

    public function getFacilityAuditRoundAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('get-facility-audit-round');
        }
        return $this->respond->fragment('application/facility/get-facility-audit-round', [
            'spiV3auditRoundNo' => $this->odkFormService->getSpiV3FormFacilityAuditNo($this->post($request)),
        ]);
    }

    /** Opened in a modal from the facility list. */
    public function getProvinceListAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->respond->page('application/facility/get-province-list', [
            'facilityName' => $this->odkFormService->getAllFacilityNames(),
            'provinces' => $this->provinceService->getAllActiveProvinces(),
        ], 'layout/modal');
    }

    public function mapProvinceAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('map-province');
        }
        return $this->respond->fragment('application/facility/map-province', [
            'result' => $this->facilityService->mapProvince($this->post($request)),
        ]);
    }

    /** Called by the SPI-RT V3 and V6 edit forms. */
    public function getFacilityDetailsAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('get-facility-details');
        }
        return $this->respond->fragment('application/facility/get-facility-details', [
            'result' => $this->facilityService->getFacilityDetails($this->post($request)),
        ]);
    }

    public function exportFacilityAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('export-facility');
        }
        return $this->respond->fragment('application/facility/export-facility', [
            'result' => $this->facilityService->exportFacility(),
        ]);
    }

    public function searchProvinceListAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() !== 'GET') {
            return $this->fallback('search-province-list');
        }
        $result = $this->facilityService->getProvinceData($this->query($request, 'q'));
        return $this->respond->html(CommonService::jsonEncode($result));
    }

    /** Has no template, so anything but GET is the error page, as before. */
    public function searchDistrictListAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() !== 'GET') {
            return $this->fallback('search-district-list');
        }
        $result = $this->facilityService->getDistrictData($this->query($request, 'q'));
        return $this->respond->html(CommonService::jsonEncode($result));
    }

    /**
     * The upload form, and the upload itself. The template reads the counts
     * from $_GET. The redirect after an upload is a literal path, as before.
     */
    public function uploadFacilityAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('upload-facility');
        }
        $result = $this->facilityService->uploadFacility($this->post($request));
        if (empty($result)) {
            return $this->respond->redirect('upload-facility');
        }
        return $this->respond->redirectToUrl('/facility/upload-facility?' . http_build_query([
            'total' => $result['total'],
            'notAdded' => $result['notAdded'],
            'option' => $result['option'],
        ]));
    }

    /** Has no template, so anything but POST is the error page, as before. */
    public function checkProvinceDistrictAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('check-province-district');
        }
        $result = $this->provinceService->checkProvinceDistrict($this->post($request));
        return $this->respond->html(CommonService::jsonEncode($result));
    }

    /** What a Laminas action that returned null rendered. */
    private function fallback(string $action): ResponseInterface
    {
        return $this->respond->page('application/facility/' . $action);
    }

    private function query(ServerRequestInterface $request, string $name): mixed
    {
        return $request->getQueryParams()[$name] ?? null;
    }
}

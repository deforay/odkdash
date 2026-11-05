<?php

declare(strict_types=1);

namespace App\Http\Handlers;

use App\Http\Responder;
use Application\Service\CommonService;
use Application\Service\OdkFormService;
use Application\Service\ProvinceService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class SpiV6ReportsHandler extends ActionHandler
{
    public function __construct(
        private readonly Responder $respond,
        private readonly OdkFormService $odkFormService,
        private readonly ProvinceService $provinceService,
    ) {
    }

    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->respond->page('application/spi-v6-reports/index');
    }

    /** The report page, and the table rows it posts back for. */
    public function facilityReportAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $result = $this->odkFormService->getAllApprovedV6FormSubmissionsTable($this->post($request));
            return $this->respond->html(CommonService::jsonEncode($result));
        }
        return $this->respond->page('application/spi-v6-reports/facility-report', [
            'testingPointResult' => $this->odkFormService->getAllSpiV6TestingPointType(),
            'pendingCount' => $this->odkFormService->getSpiV6PendingCount(),
            // The template reads the V6 audit rounds under the V3 name.
            'spiV3auditRoundNo' => $this->odkFormService->getSpiV6FormAuditNo(),
            'locationResult' => $this->provinceService->getAllMappedLocations(),
        ]);
    }

    /** Called by the report page's export button. */
    public function exportFacilityReportAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->respond->page('application/spi-v6-reports/export-facility-report');
        }
        return $this->respond->fragment('application/spi-v6-reports/export-facility-report', [
            'result' => $this->odkFormService->exportV6FacilityReport($this->post($request)),
        ]);
    }
}

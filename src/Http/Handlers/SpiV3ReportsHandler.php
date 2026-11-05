<?php

declare(strict_types=1);

namespace App\Http\Handlers;

use App\Http\Responder;
use Application\Service\CommonService;
use Application\Service\OdkFormService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class SpiV3ReportsHandler extends ActionHandler
{
    public function __construct(
        private readonly Responder $respond,
        private readonly OdkFormService $odkFormService,
    ) {
    }

    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->respond->page('application/spi-v3-reports/index');
    }

    /** The report page, and the table rows it posts back for. */
    public function facilityReportAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $result = $this->odkFormService->getAllApprovedSubmissionsTable($this->post($request));
            return $this->respond->html(CommonService::jsonEncode($result));
        }
        return $this->respond->page('application/spi-v3-reports/facility-report', [
            'pendingCount' => $this->odkFormService->getSpiV3PendingCount(),
            'spiV3auditRoundNo' => $this->odkFormService->getSpiV3FormAuditNo(),
            'levelNamesResult' => $this->odkFormService->getSpiV3FormUniqueLevelNames(),
        ]);
    }

    /** Called by the report page's export button. */
    public function exportFacilityReportAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->respond->page('application/spi-v3-reports/export-facility-report');
        }
        return $this->respond->fragment('application/spi-v3-reports/export-facility-report', [
            'result' => $this->odkFormService->exportFacilityReport($this->post($request)),
        ]);
    }
}

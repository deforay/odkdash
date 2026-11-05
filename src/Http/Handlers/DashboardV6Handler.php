<?php

declare(strict_types=1);

namespace App\Http\Handlers;

use App\Http\Responder;
use Application\Service\OdkFormService;
use Application\Service\ProvinceService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/** The SPI-RRT V6 dashboard. */
final class DashboardV6Handler extends ActionHandler
{
    public function __construct(
        private readonly Responder $respond,
        private readonly OdkFormService $odkFormService,
        private readonly ProvinceService $provinceService,
    ) {
    }

    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        $forms = $this->odkFormService;

        return $this->respond->page('application/dashboard-v6/index', [
            'perf1' => $forms->getPerformanceV6([]),
            'perflast30' => $forms->getPerformanceLast30DaysV6(''),
            'perflast180' => $forms->getPerformanceLast180DaysV6(),
            'allSubmissions' => $forms->getAllApprovedSubmissionsV6(),
            'testingVolume' => $forms->getAllApprovedTestingVolumeV6(''),
            'rawSubmissions' => $forms->getAllSubmissionsV6(),
            'highVolumeSites' => $forms->getHighVolumeSites(),
            'spiV6auditRoundNo' => $forms->getSpiV6FormAuditNo(),
            'testingPointResult' => $forms->getAllTestingPointTypeV6(),
            'locationResult' => $this->provinceService->getAllMappedLocations(),
        ]);
    }
}

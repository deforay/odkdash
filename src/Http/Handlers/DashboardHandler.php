<?php

declare(strict_types=1);

namespace App\Http\Handlers;

use App\Http\Responder;
use Application\Service\CommonService;
use Application\Service\OdkFormService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/** The SPI-RT V3 dashboard. */
final class DashboardHandler extends ActionHandler
{
    public function __construct(
        private readonly Responder $respond,
        private readonly OdkFormService $odkFormService,
    ) {
    }

    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        $forms = $this->odkFormService;

        return $this->respond->page('application/dashboard/index', [
            'perf1' => $forms->getPerformance([]),
            'perflast30' => $forms->getPerformanceLast30Days(''),
            'perflast180' => $forms->getPerformanceLast180Days(),
            'allSubmissions' => $forms->getAllApprovedSubmissions(),
            'testingVolume' => $forms->getAllApprovedTestingVolume(''),
            'rawSubmissions' => $forms->getAllSubmissions(),
            'spiV3auditRoundNo' => $forms->getSpiV3FormAuditNo(),
            'testingPointResult' => $forms->getAllTestingPointType(),
            'levelNamesResult' => $forms->getSpiV3FormUniqueLevelNames(),
        ]);
    }

    /** The audits of one assessment date. The id is the base64 date. */
    public function auditDetailsAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $result = $this->odkFormService->getAllApprovedSubmissionsDetailsBasedOnAuditDate($this->post($request));
            return $this->respond->html(CommonService::jsonEncode($result));
        }
        return $this->respond->page('application/dashboard/audit-details', [
            'assesmentOfAuditDate' => base64_decode((string) $this->routeParam($request, 'id')),
        ]);
    }
}

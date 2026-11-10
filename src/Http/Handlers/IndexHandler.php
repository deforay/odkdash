<?php

declare(strict_types=1);

namespace App\Http\Handlers;

use App\Audit\AuditScore;
use App\Http\Responder;
use Application\Service\CommonService;
use Application\Service\OdkFormService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The public landing page and the two charts it posts back for. No login
 * is needed.
 */
final class IndexHandler extends ActionHandler
{
    public function __construct(
        private readonly Responder $respond,
        private readonly OdkFormService $odkFormService,
        private readonly CommonService $commonService,
    ) {
    }

    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->respond->page('application/index/index', [
            'allSubmissions' => $this->odkFormService->getAllApprovedSubmissionsV6(),
            'testingVolume' => $this->odkFormService->getAllApprovedTestingVolumeV6(''),
        ], 'layout/home');
    }

    public function auditLocationsAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->respond->page('application/index/audit-locations');
        }
        return $this->respond->fragment('application/index/audit-locations', [
            'points' => self::mapPoints($this->odkFormService->getAllApprovedSubmissionLocationV6($this->post($request))),
            'configData' => $this->commonService->getGlobalConfigDetails(),
        ]);
    }

    /**
     * The public map's points: the score and a location rounded to one
     * decimal place, about 11 km. The page is public, so it shows where
     * audits scored what, not which lab scored it.
     *
     * @param list<array<string, mixed>> $audits
     * @return list<array{latitude: float, longitude: float, score: float}>
     */
    private static function mapPoints(array $audits): array
    {
        $points = [];
        foreach ($audits as $audit) {
            if (!is_numeric($audit['Latitude'] ?? null) || !is_numeric($audit['Longitude'] ?? null)) {
                continue;
            }
            $points[] = [
                'latitude' => round((float) $audit['Latitude'], 1),
                'longitude' => round((float) $audit['Longitude'], 1),
                'score' => AuditScore::percent($audit['AUDIT_SCORE_PERCENTAGE'] ?? null),
            ];
        }

        return $points;
    }

    public function auditPerformanceAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->respond->page('application/index/audit-performance');
        }
        $params = $this->post($request);
        return $this->respond->fragment('application/index/audit-performance', [
            'auditRoundWiseData' => $this->odkFormService->getAuditRoundWiseDataV6($params),
            'perf1' => $this->odkFormService->getPerformanceV6($params),
        ]);
    }
}

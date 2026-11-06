<?php

declare(strict_types=1);

namespace App\Http\Handlers;

use App\Http\Responder;
use Application\Service\CommonService;
use Application\Service\OdkFormService;
use Application\Service\ProvinceService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The SPI-RT V6 audit pages. Actions that only answer POST render their
 * template inside the layout for any other method, as the Laminas actions
 * did by returning null.
 */
final class SpiV6Handler extends ActionHandler
{
    public function __construct(
        private readonly Responder $respond,
        private readonly OdkFormService $odkFormService,
        private readonly CommonService $commonService,
        private readonly ProvinceService $provinceService,
    ) {
    }

    /** The audit list, and the table rows it posts back for. */
    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $result = $this->odkFormService->getAllSpiV6SubmissionsDetails($this->post($request));
            return $this->respond->html(CommonService::jsonEncode($result));
        }
        return $this->respond->page('application/spi-v6/index', [
            'testingPointResult' => $this->odkFormService->getAllSpiV6TestingPointType(),
            'locationResult' => $this->provinceService->getAllMappedLocations(),
        ]);
    }

    public function printAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->respond->fragment('application/spi-v6/print', [
            'formData' => $this->odkFormService->getFormData($this->routeParam($request, 'id')),
        ]);
    }

    public function exportAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('export');
        }
        return $this->respond->fragment('application/spi-v6/export', [
            'allSubmissions' => $this->odkFormService->exportAllV6Submissions($this->post($request)),
        ]);
    }

    public function exportSAndDAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('export-s-and-d');
        }
        return $this->respond->fragment('application/spi-v6/export-s-and-d', [
            'allSubmissions' => $this->odkFormService->exportSAndDV6Submissions($this->post($request)),
        ]);
    }

    public function approveStatusAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('approve-status');
        }
        return $this->respond->fragment('application/spi-v6/approve-status', [
            'result' => $this->odkFormService->approveSpiV6FormStatus($this->post($request)),
        ]);
    }

    /** The audit as a PDF. The template sends it with its own headers. */
    public function downloadPdfAction(ServerRequestInterface $request): ResponseInterface
    {
        $configData = $this->commonService->getGlobalConfigDetails();
        if ($this->isPost($request)) {
            $params = $this->post($request);
            return $this->respond->output('application/spi-v6/download-pdf', [
                'formData' => $this->odkFormService->getSpiV6FormData($params['auditId'], 'no'),
                'configData' => $configData,
                'tempId' => $params['tempId'],
            ]);
        }
        return $this->respond->output('application/spi-v6/download-pdf', [
            'formData' => $this->odkFormService->getSpiV6FormData($this->routeParam($request, 'id'), 'yes'),
            'configData' => $configData,
        ]);
    }

    /** The corrective actions as a PDF. The template sends it with its own headers. */
    public function correctiveActionPdfAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->respond->output('application/spi-v6/corrective-action-pdf', [
            'formData' => $this->odkFormService->getSpiV6FormData($this->routeParam($request, 'id'), 'yes'),
            'configData' => $this->commonService->getGlobalConfigDetails(),
        ]);
    }

    /** The redirect after saving is a literal path, as before. */
    public function editAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $this->odkFormService->updateSpiV6Form($this->post($request));
            return $this->respond->redirectToUrl('/spi-v6');
        }
        return $this->respond->page('application/spi-v6/edit', [
            'formData' => $this->odkFormService->getSpiV6FormData($this->routeParam($request, 'id')),
            'facilities' => $this->odkFormService->getAllFacilityNames(),
            'provinceList' => $this->odkFormService->getSpiV6FormUniqueLevelNames(),
            'districtList' => $this->odkFormService->getSpiV3FormUniqueDistrict(),
        ]);
    }

    public function auditPerformanceAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('audit-performance');
        }
        $params = $this->post($request);
        return $this->respond->fragment('application/spi-v6/audit-performance', [
            'auditRoundWiseData' => $this->odkFormService->getAuditRoundWiseDataV6($params),
            'perf1' => $this->odkFormService->getPerformanceV6($params),
        ]);
    }

    public function auditPerformanceSectionZeroAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('audit-performance-section-zero');
        }
        $params = $this->post($request);
        return $this->respond->fragment('application/spi-v6/audit-performance-section-zero', [
            'auditRoundWiseData' => $this->odkFormService->getAuditRoundWiseSectionS0DataV6($params),
            'perf1' => $this->odkFormService->getPerformanceV6($params),
        ]);
    }

    public function auditPerformanceSectionZeroProtocolAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('audit-performance-section-zero-protocol');
        }
        $params = $this->post($request);
        return $this->respond->fragment('application/spi-v6/audit-performance-section-zero-protocol', [
            'auditRoundWiseData' => $this->odkFormService->getAuditRoundWiseSectionD0DataV6($params),
            'perf1' => $this->odkFormService->getPerformanceV6($params),
        ]);
    }

    public function worstPerformanceAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('worst-performance');
        }
        $params = $this->post($request);
        return $this->respond->fragment('application/spi-v6/worst-performance', [
            'zeroCounts' => $this->odkFormService->getZeroQuestionCountsV6($params),
            // The template reads the V6 labels under the V3 name.
            'spiV3Labels' => $this->odkFormService->getSpiV6FormLabels(),
            'limitBar' => $params['limitBar'],
        ]);
    }

    public function auditLocationsAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('audit-locations');
        }
        return $this->respond->fragment('application/spi-v6/audit-locations', [
            'allSubmissions' => $this->odkFormService->getAllApprovedSubmissionLocationV6($this->post($request)),
            'configData' => $this->commonService->getGlobalConfigDetails(),
        ]);
    }

    public function deleteAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('delete');
        }
        return $this->respond->fragment('application/spi-v6/delete', [
            'result' => $this->odkFormService->deleteAuditDataV6($this->post($request)),
        ]);
    }

    public function spirtv6DatewiseAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('spirtv6-datewise');
        }
        return $this->respond->fragment('application/spi-v6/spirtv6-datewise', [
            'perflast30' => $this->odkFormService->getPerformanceLast30DaysV6($this->post($request)),
        ]);
    }

    public function testingVolumeDatewiseAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('testing-volume-datewise');
        }
        return $this->respond->fragment('application/spi-v6/testing-volume-datewise', [
            'testingVolume' => $this->odkFormService->getAllApprovedTestingVolumeV6($this->post($request)),
        ]);
    }

    /**
     * The approved audits page, opened from the V6 dashboard with filters
     * in the query string, and the table rows it posts back for.
     */
    public function viewDataV6Action(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $result = $this->odkFormService->getViewDataDetailsV6($this->post($request));
            return $this->respond->html(CommonService::jsonEncode($result));
        }
        return $this->respond->page('application/spi-v6/view-data-v6', [
            ...$this->viewDataFilters($request),
            'testingPointResult' => $this->odkFormService->getAllTestingPointTypeV6(),
            'locationResult' => $this->provinceService->getAllMappedLocations(),
        ]);
    }

    /** As viewDataV6Action, for the section 0 (site) scores. */
    public function viewDataSectionZeroV6Action(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $result = $this->odkFormService->getViewDataS0DetailsV6($this->post($request));
            return $this->respond->html(CommonService::jsonEncode($result));
        }
        return $this->respond->page('application/spi-v6/view-data-section-zero-v6', [
            ...$this->viewDataFilters($request),
            'testingPointResult' => $this->odkFormService->getAllTestingPointTypeV6(),
            'levelNamesResult' => $this->odkFormService->getSpiV6FormUniqueLevelNames(),
        ]);
    }

    /** As viewDataV6Action, for the section 0 protocol scores. */
    public function viewDataSectionZeroProtocolV6Action(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $result = $this->odkFormService->getViewDataD0DetailsV6($this->post($request));
            return $this->respond->html(CommonService::jsonEncode($result));
        }
        return $this->respond->page('application/spi-v6/view-data-section-zero-protocol-v6', [
            ...$this->viewDataFilters($request),
            'testingPointResult' => $this->odkFormService->getAllTestingPointTypeV6(),
            'levelNamesResult' => $this->odkFormService->getSpiV6FormUniqueLevelNames(),
        ]);
    }

    public function getTestingPointTypeNamesAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('get-testing-point-type-names');
        }
        return $this->respond->fragment('application/spi-v6/get-testing-point-type-names', [
            'result' => $this->odkFormService->getTestingPointTypeNamesByTypeV6($this->post($request)),
        ]);
    }

    public function downloadSpiderChartAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('download-spider-chart');
        }
        return $this->respond->fragment('application/spi-v6/download-spider-chart', [
            'configData' => $this->commonService->getGlobalConfigDetails(),
        ]);
    }

    public function downloadSectionZeroSpiderChartAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('download-section-zero-spider-chart');
        }
        return $this->respond->fragment('application/spi-v6/download-section-zero-spider-chart', [
            'configData' => $this->commonService->getGlobalConfigDetails(),
        ]);
    }

    public function downloadSectionZeroProtocolSpiderChartAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('download-section-zero-protocol-spider-chart');
        }
        return $this->respond->fragment('application/spi-v6/download-section-zero-protocol-spider-chart', [
            'configData' => $this->commonService->getGlobalConfigDetails(),
        ]);
    }

    public function exportAsPdfAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('export-as-pdf');
        }
        $params = $this->post($request);
        $result = $this->odkFormService->getAllSpiV6SubmissionsDetails($params);
        // These draw the chart images the template reads from disk. Their
        // return values are not used.
        $this->odkFormService->getSpiV6PerformancePieChart($params);
        $this->odkFormService->getSpiV6AuditRoundWiseDataChart($params);
        $this->odkFormService->getSpiV6AuditRoundWiseS0DataChart($params);
        $this->odkFormService->getSpiV6AuditRoundWiseD0DataChart($params);
        return $this->respond->fragment('application/spi-v6/export-as-pdf', [
            'result' => $result,
            'configData' => $this->commonService->getGlobalConfigDetails(),
            'argument' => $params,
        ]);
    }

    public function duplicateAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->respond->page('application/spi-v6/duplicate', [
            'result' => $this->odkFormService->getAllV6DuplicateSubmissionsDetails(),
        ]);
    }

    public function removeAuditAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('remove-audit');
        }
        return $this->respond->fragment('application/spi-v6/remove-audit', [
            'result' => $this->odkFormService->removeAuditV6($this->post($request)),
        ]);
    }

    public function saveDownloadDataAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('save-download-data');
        }
        return $this->respond->fragment('application/spi-v6/save-download-data', [
            'result' => $this->odkFormService->addV6DownloadData($this->post($request)),
        ]);
    }

    /**
     * The service sends the bulk download itself and exits. There is no
     * template, so a request it does not answer is the error page, as before.
     */
    public function downloadFilesAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->respond->page('application/spi-v6/download-files', [
            'result' => $this->odkFormService->getV6DownloadFilesRow(),
        ]);
    }

    public function getDistrictByProvinceAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('get-district-by-province');
        }
        $params = $this->post($request);
        return $this->respond->fragment('application/spi-v6/get-district-by-province', [
            'result' => $this->odkFormService->getDistrictData($params),
            'params' => $params,
        ]);
    }

    public function viewBulkDownloadsAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->respond->page('application/spi-v6/view-bulk-downloads', [
            'result' => $this->odkFormService->getBulkDownloads('v6'),
        ]);
    }

    /** One bulk download archive, for its requester or a Super Admin. Anyone else gets 404. */
    public function bulkFileAction(ServerRequestInterface $request): ResponseInterface
    {
        $path = $this->odkFormService->getBulkDownloadPath('v6', $this->routeParam($request, 'id'));
        if ($path === null) {
            return $this->respond->status(404);
        }
        return $this->respond->file($path, 'application/zip');
    }

    public function getDistrictBasedProvinceAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('get-district-based-province');
        }
        $provinceId = $this->post($request)['provinceId'];
        return $this->respond->fragment('application/spi-v6/get-district-based-province', [
            'result' => $this->provinceService->getAllDistrictByProvinceAsMuliple($provinceId),
        ]);
    }

    public function exportViewDataV6Action(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->fallback('export-view-data-v6');
        }
        return $this->respond->fragment('application/spi-v6/export-view-data-v6', [
            'result' => $this->odkFormService->exportViewDataV6($this->post($request)),
        ]);
    }

    /** What a Laminas action that returned null rendered. */
    private function fallback(string $action): ResponseInterface
    {
        return $this->respond->page('application/spi-v6/' . $action);
    }

    /**
     * The filters the dashboards pass in the query string. Laminas read each
     * with fromQuery() and kept it only when truthy.
     *
     * @return array{source: mixed, roundno: mixed, drange: mixed, level: mixed}
     */
    private function viewDataFilters(ServerRequestInterface $request): array
    {
        $query = $request->getQueryParams();

        return [
            'source' => empty($query['source']) ? '' : $query['source'],
            'roundno' => empty($query['roundno']) ? '' : $query['roundno'],
            'drange' => empty($query['drange']) ? '' : $query['drange'],
            'level' => empty($query['level']) ? '' : $query['level'],
        ];
    }
}

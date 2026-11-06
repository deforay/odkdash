<?php

declare(strict_types=1);

namespace App\Http\Handlers;

use App\Http\Responder;
use Application\Service\CommonService;
use Application\Service\OdkFormService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The SPI-RT V3 audit pages. Actions that only answer POST render their
 * template inside the layout for any other method, as the Laminas actions
 * did by returning null.
 */
final class SpiV3Handler extends ActionHandler
{
    public function __construct(
        private readonly Responder $respond,
        private readonly OdkFormService $odkFormService,
        private readonly CommonService $commonService,
    ) {
    }

    /** The audit list, and the table rows it posts back for. */
    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $result = $this->odkFormService->getAllSubmissionsDetails($this->post($request));
            return $this->respond->html(CommonService::jsonEncode($result));
        }
        return $this->respond->page('application/spi-v3/index', [
            'testingPointResult' => $this->odkFormService->getAllTestingPointType(),
            'levelNamesResult' => $this->odkFormService->getSpiV3FormUniqueLevelNames(),
        ]);
    }

    public function printAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->respond->fragment('application/spi-v3/print', [
            'formData' => $this->odkFormService->getFormData($this->routeParam($request, 'id')),
        ]);
    }

    public function exportAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->postOnly($request, 'export', fn($params) => [
            'allSubmissions' => $this->odkFormService->exportAllSubmissions($params),
        ]);
    }

    public function approveStatusAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->postOnly($request, 'approve-status', fn($params) => [
            'result' => $this->odkFormService->approveFormStatus($params),
        ]);
    }

    /** The audit as a PDF. The template sends it with its own headers. */
    public function downloadPdfAction(ServerRequestInterface $request): ResponseInterface
    {
        $configData = $this->commonService->getGlobalConfigDetails();
        if ($this->isPost($request)) {
            $params = $this->post($request);
            return $this->respond->output('application/spi-v3/download-pdf', [
                'formData' => $this->odkFormService->getFormData($params['auditId']),
                'configData' => $configData,
                'tempId' => $params['tempId'],
            ]);
        }
        return $this->respond->output('application/spi-v3/download-pdf', [
            'formData' => $this->odkFormService->getFormData($this->routeParam($request, 'id'), 'yes'),
            'configData' => $configData,
        ]);
    }

    public function correctiveActionPdfAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->respond->output('application/spi-v3/corrective-action-pdf', [
            'formData' => $this->odkFormService->getFormData($this->routeParam($request, 'id'), 'yes'),
            'configData' => $this->commonService->getGlobalConfigDetails(),
        ]);
    }

    /** The redirect after saving is a literal path, as before. */
    public function editAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $this->odkFormService->updateSpiForm($this->post($request));
            return $this->respond->redirectToUrl('/spi-v3/manage-facility');
        }
        return $this->respond->page('application/spi-v3/edit', [
            'formData' => $this->odkFormService->getFormData($this->routeParam($request, 'id')),
            'facilities' => $this->odkFormService->getAllFacilityNames(),
            'provinceList' => $this->odkFormService->getSpiV3FormUniqueLevelNames(),
            'districtList' => $this->odkFormService->getSpiV3FormUniqueDistrict(),
        ]);
    }

    public function auditPerformanceAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->postOnly($request, 'audit-performance', fn($params) => [
            'auditRoundWiseData' => $this->odkFormService->getAuditRoundWiseData($params),
            'perf1' => $this->odkFormService->getPerformance($params),
        ]);
    }

    public function worstPerformanceAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->postOnly($request, 'worst-performance', fn($params) => [
            'zeroCounts' => $this->odkFormService->getZeroQuestionCounts($params),
            'spiV3Labels' => $this->odkFormService->getSpiV3FormLabels(),
            'limitBar' => $params['limitBar'],
        ]);
    }

    public function auditLocationsAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->postOnly($request, 'audit-locations', fn($params) => [
            'allSubmissions' => $this->odkFormService->getAllApprovedSubmissionLocation($params),
            'configData' => $this->commonService->getGlobalConfigDetails(),
        ]);
    }

    /** The pending audits page, and the table rows it posts back for. */
    public function manageFacilityAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $result = $this->odkFormService->getAllSubmissionsDatas($this->post($request));
            return $this->respond->html(CommonService::jsonEncode($result));
        }
        return $this->respond->page('application/spi-v3/manage-facility', [
            'pendingFacilityName' => $this->odkFormService->getPendingFacilityNames(),
        ]);
    }

    /** Called from the facility name modal on the facility list. */
    public function mergeFacilityNameAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->postOnly($request, 'merge-facility-name', fn($params) => [
            'result' => $this->odkFormService->mergeFacilityName($params),
        ]);
    }

    public function mapAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->respond->page('application/spi-v3/map');
    }

    public function deleteAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->postOnly($request, 'delete', fn($params) => [
            'result' => $this->odkFormService->deleteAuditData($params),
        ]);
    }

    public function spirtv3DatewiseAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->postOnly($request, 'spirtv3-datewise', fn($params) => [
            'perflast30' => $this->odkFormService->getPerformanceLast30Days($params),
        ]);
    }

    public function testingVolumeDatewiseAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->postOnly($request, 'testing-volume-datewise', fn($params) => [
            'testingVolume' => $this->odkFormService->getAllApprovedTestingVolume($params),
        ]);
    }

    /**
     * The approved audits page, opened from the dashboards with filters in
     * the query string, and the table rows it posts back for.
     */
    public function viewDataAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $result = $this->odkFormService->getViewDataDetails($this->post($request));
            return $this->respond->html(CommonService::jsonEncode($result));
        }
        $query = $request->getQueryParams();
        // Laminas read each filter with fromQuery() and kept it only when truthy.
        $filter = static fn(string $name): mixed => empty($query[$name]) ? '' : $query[$name];
        return $this->respond->page('application/spi-v3/view-data', [
            'source' => $filter('source'),
            'roundno' => $filter('roundno'),
            'drange' => $filter('drange'),
            'level' => $filter('level'),
            'testingPointResult' => $this->odkFormService->getAllTestingPointType(),
            'levelNamesResult' => $this->odkFormService->getSpiV3FormUniqueLevelNames(),
        ]);
    }

    public function getTestingPointTypeNamesAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->postOnly($request, 'get-testing-point-type-names', fn($params) => [
            'result' => $this->odkFormService->getTestingPointTypeNamesByType($params),
        ]);
    }

    public function downloadSpiderChartAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->postOnly($request, 'download-spider-chart', fn($params) => [
            'configData' => $this->commonService->getGlobalConfigDetails(),
        ]);
    }

    public function exportAsPdfAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->postOnly($request, 'export-as-pdf', fn($params) => [
            'result' => $this->odkFormService->getAllSubmissionsDetails($params),
            'configData' => $this->commonService->getGlobalConfigDetails(),
            'argument' => $params,
        ]);
    }

    public function duplicateAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->respond->page('application/spi-v3/duplicate', [
            'result' => $this->odkFormService->getAllDuplicateSubmissionsDetails(),
        ]);
    }

    public function removeAuditAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->postOnly($request, 'remove-audit', fn($params) => [
            'result' => $this->odkFormService->removeAudit($params),
        ]);
    }

    public function saveDownloadDataAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->postOnly($request, 'save-download-data', fn($params) => [
            'result' => $this->odkFormService->addDownloadData($params),
        ]);
    }

    /** Has no template, so it is the error page, as before. */
    public function downloadFilesAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->respond->page('application/spi-v3/download-files', [
            'result' => $this->odkFormService->getDownloadFilesRow(),
        ]);
    }

    public function getDistrictByProvinceAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->postOnly($request, 'get-district-by-province', fn($params) => [
            'result' => $this->odkFormService->getDistrictData($params),
            'params' => $params,
        ]);
    }

    /** The redirect after an upload is a literal path, as before. */
    public function validateSpiv3DataAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $this->odkFormService->validateSPIV3File($this->post($request));
            return $this->respond->redirectToUrl('/spi-v3/validate-spiv3-data');
        }
        return $this->respond->page('application/spi-v3/validate-spiv3-data', [
            'tokenResults' => $this->odkFormService->getSpiV3FormUniqueTokens(),
        ]);
    }

    public function validateSpiv3DetailsAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->respond->page('application/spi-v3/validate-spiv3-details');
        }
        $result = $this->odkFormService->getAllValidateSpiv3Details($this->post($request));
        return $this->respond->html(CommonService::jsonEncode($result));
    }

    public function addSpiv3ValidateDataAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->postOnly($request, 'add-spiv3-validate-data', fn($params) => [
            'result' => $this->odkFormService->addValidateSpiv3Data($params),
        ]);
    }

    public function viewBulkDownloadsAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->respond->page('application/spi-v3/view-bulk-downloads', [
            'result' => $this->odkFormService->getBulkDownloadsFiles(),
        ]);
    }

    /**
     * A terminal template on POST, built from the posted data. Any other
     * method renders the template inside the layout with no variables: what
     * a Laminas action that returned null did.
     *
     * @param callable(\Laminas\Stdlib\Parameters): array<string, mixed> $variables
     */
    private function postOnly(ServerRequestInterface $request, string $action, callable $variables): ResponseInterface
    {
        $template = 'application/spi-v3/' . $action;
        if (!$this->isPost($request)) {
            return $this->respond->page($template);
        }
        return $this->respond->fragment($template, $variables($this->post($request)));
    }
}

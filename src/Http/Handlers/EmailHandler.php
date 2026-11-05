<?php

declare(strict_types=1);

namespace App\Http\Handlers;

use App\Http\Responder;
use Application\Service\CommonService;
use Application\Service\FacilityService;
use Application\Service\OdkFormService;
use Laminas\Stdlib\Parameters;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Emails facility audit reports. The V3 and V6 pages work the same way:
 * after sending, the page reloads with the email and audit ids in the URL,
 * and the page then builds the PDFs for those audits.
 */
final class EmailHandler extends ActionHandler
{
    public function __construct(
        private readonly Responder $respond,
        private readonly FacilityService $facilityService,
        private readonly OdkFormService $odkFormService,
        private readonly CommonService $commonService,
    ) {
    }

    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $params = $this->post($request);
            $tempId = $this->facilityService->addEmail($params);
            return $this->afterSending($tempId, $params, '/email/index/');
        }

        [$ids, $pdfIds] = $this->ids($request);
        return $this->respond->page('application/email/index', [
            'facilityName' => $this->odkFormService->getAllFacilityNames(),
            'facilityResult' => $this->facilityService->getFacilityProfileByAudit($ids),
            'ids' => $pdfIds,
        ]);
    }

    public function emailV6Action(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $params = $this->post($request);
            $tempId = $this->facilityService->addEmailV6($params);
            return $this->afterSending($tempId, $params, '/email/email-v6/');
        }

        [$ids, $pdfIds] = $this->ids($request);
        return $this->respond->page('application/email/email-v6', [
            'facilityName' => $this->odkFormService->getAllFacilityNamesV6(),
            'facilityResult' => $this->facilityService->getFacilityProfileByAuditV6($ids),
            'ids' => $pdfIds,
        ]);
    }

    public function getFacilitiesAuditsAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->respond->page('application/email/get-facilities-audits');
        }
        return $this->respond->fragment('application/email/get-facilities-audits', [
            'result' => $this->odkFormService->getFacilitiesAudits($this->post($request)),
        ]);
    }

    public function getFacilitiesV6AuditsAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->respond->page('application/email/get-facilities-v6-audits');
        }
        return $this->respond->fragment('application/email/get-facilities-v6-audits', [
            'result' => $this->odkFormService->getFacilitiesAuditsV6($this->post($request)),
        ]);
    }

    public function downloadPdfAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->respond->page('application/email/download-pdf');
        }
        $params = $this->post($request);
        return $this->respond->fragment('application/email/download-pdf', [
            'formData' => $this->odkFormService->getFormData($params['auditId']),
            'configData' => $this->commonService->getGlobalConfigDetails(),
            'tempId' => $params['tempId'],
        ]);
    }

    /**
     * Reloads the page with base64("<email id>#<audit id>#...") in the URL.
     * The path has no base path, as in the Laminas controller.
     */
    private function afterSending(mixed $tempId, Parameters $params, string $path): ResponseInterface
    {
        if (!($tempId > 0)) {
            return $this->respond->redirect('email');
        }
        $ids = '';
        if (!empty($params['audits'])) {
            $ids = base64_encode($tempId . '#' . implode('#', array_values((array) $params['audits'])));
        }
        return $this->respond->redirectToUrl($path . $ids);
    }

    /**
     * The id in the URL is either an audit id to preselect, or the encoded
     * list afterSending() built, which the page turns into PDFs.
     *
     * @return array{0: ?string, 1: string} audit id, encoded list
     */
    private function ids(ServerRequestInterface $request): array
    {
        $ids = $this->routeParam($request, 'id');
        if (count(explode('#', base64_decode((string) $ids))) > 1) {
            return ['', (string) $ids];
        }
        return [$ids, ''];
    }
}

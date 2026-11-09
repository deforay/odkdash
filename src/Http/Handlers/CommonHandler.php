<?php

declare(strict_types=1);

namespace App\Http\Handlers;

use App\Http\Responder;
use Application\Service\CommonService;
use Application\Service\OdkFormService;
use InvalidArgumentException;
use App\Http\Parameters;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Helpers the form pages call: duplicate checks, password suggestions and
 * the audit location map.
 */
final class CommonHandler extends ActionHandler
{
    public function __construct(
        private readonly Responder $respond,
        private readonly CommonService $commonService,
        private readonly OdkFormService $odkFormService,
    ) {
    }

    /** Checks whether a value already exists in a table. */
    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        $result = '';
        if ($this->isPost($request)) {
            try {
                $result = $this->commonService->checkFieldValidations($this->post($request));
            } catch (InvalidArgumentException) {
                return $this->respond->status(400);
            }
        }
        return $this->respond->fragment('application/common/index', ['result' => $result]);
    }

    public function auditLocationsAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() !== 'GET') {
            return $this->respond->page('application/common/audit-locations');
        }
        return $this->respond->page('application/common/audit-locations', [
            'id' => new Parameters($request->getQueryParams()),
            'spiV3auditRoundNo' => $this->odkFormService->getSpiV3FormAuditNo(),
        ]);
    }

    public function getAuditLocationBasedOnFormAction(ServerRequestInterface $request): ResponseInterface
    {
        $auditRoundNo = [];
        $id = null;
        if ($this->isPost($request)) {
            $params = $this->post($request);
            $id = $params['inpId'];
            if (trim((string) $params['formVersion']) == 'v3') {
                $auditRoundNo = $this->odkFormService->getSpiV3FormAuditNo();
            }
            if (trim((string) $params['formVersion']) == 'v6') {
                $auditRoundNo = $this->odkFormService->getSpiV6FormAuditNo();
            }
        }
        return $this->respond->fragment('application/common/get-audit-location-based-on-form', [
            'result' => $auditRoundNo,
            'id' => $id,
        ]);
    }

    public function generatePasswordAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            return $this->respond->html($this->commonService->generatePassword());
        }
        // There is no template for this action, so a GET fails.
        return $this->respond->page('application/common/generate-password');
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Handlers;

use App\Http\Responder;
use Application\Service\AuditTrailService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AuditTrailHandler extends ActionHandler
{
    public function __construct(
        private readonly Responder $respond,
        private readonly AuditTrailService $auditTrailService,
    ) {
    }

    /** The filter form posts back to this page, which then lists the matches. */
    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPost($request)) {
            return $this->respond->page('application/audit-trail/index');
        }

        $params = $this->post($request);
        if (!empty($params['version']) && $params['version'] == 'v3') {
            $result = $this->auditTrailService->getSpiV3Details($params);
            $colCount = 239;
        } else {
            $result = $this->auditTrailService->getSpiV6Details($params);
            $colCount = 320;
        }
        return $this->respond->page('application/audit-trail/index', [
            'result' => $result,
            'params' => $params,
            'colCount' => $colCount,
        ]);
    }
}

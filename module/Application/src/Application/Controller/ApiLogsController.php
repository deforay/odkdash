<?php

namespace Application\Controller;

use Application\Model\ApiLogsTable;
use Application\Service\CommonService;
use ArchiveUtil\ArchiveUtility;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

/**
 * Admin viewer for the api_logs table. The same controller serves:
 *
 *   GET  /api-logs                              → HTML page
 *   GET  /api-logs?format=json (with filters)   → list rows
 *   GET  /api-logs/show?id=<request_id>         → single row + decompressed bodies
 *   GET  /api-logs/stats                        → 24h summary tiles
 *
 * Access is gated by ACL resource Application\Controller\ApiLogsController
 * with privileges index / show / stats (seeded in 1.2.0). Bodies live on
 * disk compressed; the show action calls ArchiveUtility::decompressToString
 * to inflate them on demand so the DB row stays small.
 */
class ApiLogsController extends AbstractActionController
{
    public function __construct(private readonly ApiLogsTable $apiLogsTable)
    {
    }

    public function indexAction()
    {
        if ($this->params()->fromQuery('format') === 'json') {
            return $this->jsonResponse($this->apiLogsTable->fetchList($this->collectListParams()));
        }
        return new ViewModel([
            'sources' => $this->apiLogsTable->fetchSources(),
        ]);
    }

    public function showAction()
    {
        $id = (string) $this->params()->fromQuery('id', '');
        if ($id === '') {
            return $this->jsonResponse(['error' => 'missing id'], 400);
        }
        $row = $this->apiLogsTable->fetchOne($id);
        if (!$row) {
            return $this->jsonResponse(['error' => 'not found'], 404);
        }
        $row['request_body']  = $this->readBody($row['request_body_path'] ?? null);
        $row['response_body'] = $this->readBody($row['response_body_path'] ?? null);
        // Don't leak the on-disk paths to the browser — bodies are inlined now.
        unset($row['request_body_path'], $row['response_body_path']);
        return $this->jsonResponse($row);
    }

    public function statsAction()
    {
        $hours = max(1, min(168, (int) $this->params()->fromQuery('hours', 24)));
        return $this->jsonResponse($this->apiLogsTable->fetchStats($hours));
    }

    private function collectListParams(): array
    {
        $q = $this->params()->fromQuery();
        return [
            'page'     => $q['page']     ?? 1,
            'per_page' => $q['per_page'] ?? 50,
            'sort'     => $q['sort']     ?? 'created_at',
            'dir'      => $q['dir']      ?? 'desc',
            'method'   => $q['method']   ?? null,
            'status'   => $q['status']   ?? null,
            'source'   => $q['source']   ?? null,
            'url'      => $q['url']      ?? null,
            'from'     => $q['from']     ?? null,
            'to'       => $q['to']       ?? null,
            'slow'     => $q['slow']     ?? null,
        ];
    }

    private function readBody(?string $path): ?string
    {
        if (!$path || !is_file($path)) {
            return null;
        }
        try {
            return ArchiveUtility::decompressToString($path);
        } catch (\Throwable) {
            return null;
        }
    }

    private function jsonResponse(mixed $data, int $status = 200)
    {
        $response = $this->getResponse();
        $response->setStatusCode($status);
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(CommonService::jsonEncode($data));
        return $response;
    }
}

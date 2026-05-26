<?php

namespace Application\Service;

use ArchiveUtil\ArchiveUtility;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;
use Ramsey\Uuid\Uuid;

/**
 * Records one row per outbound HTTP call to an ODK Central server.
 * Called from GuzzleApiLogMiddleware wrapped around the Guzzle clients
 * built inside OdkFormService::syncOdkCentralV3 / syncOdkCentralV6.
 *
 * Inbound HTTP requests (admin pages, /receiver pushes) intentionally do
 * NOT come through here — event_log + form_dump already cover those.
 *
 * Bodies are written as compressed files under var/api-logs/ (outside
 * web root) via ArchiveUtility so the DB stays small and the cleanup
 * command can drop files and rows together. The body file path is
 * stamped on the row for the show endpoint to inflate on demand.
 */
final class ApiLogger
{
    /** Cap raw body size before compression. Keeps a single bad upload
     *  from blowing up disk and the JSON pretty-print column in the UI. */
    private const MAX_BODY_BYTES = 1048576; // 1 MB

    /** Keys masked before bodies are written to disk. ODK Central
     *  responses don't usually contain passwords, but request bodies for
     *  /v1/sessions absolutely do — never let that JSON land unredacted. */
    private const REDACTED_FIELDS = [
        'password', 'token', 'access_token', 'refresh_token',
        'authorization', 'auth_token', 'secret',
    ];

    public function __construct(private readonly Adapter $dbAdapter)
    {
    }

    /**
     * Persist one HTTP call. Returns the request_id (UUIDv7) the row
     * was stored under. Designed to never throw — a failure here
     * mustn't take down the surrounding sync.
     *
     * @param string      $source       e.g. 'sync-central-v6' — what triggered the call
     * @param string      $method       HTTP method
     * @param string      $url          Full URL (scheme + host + path + query)
     * @param string|null $requestBody  Raw outbound body
     * @param int         $statusCode   Response status (0 on transport failure)
     * @param string|null $responseBody Raw inbound body
     * @param int         $durationMs   Wall-clock ms
     * @param string|null $error        Exception message if Guzzle threw
     */
    public function logCall(
        string $source,
        string $method,
        string $url,
        ?string $requestBody,
        int $statusCode,
        ?string $responseBody,
        int $durationMs,
        ?string $error = null,
    ): ?string {
        try {
            $requestId = Uuid::uuid7()->toString();
            $requestPath  = $this->writeBody($requestId, $requestBody, 'requests');
            $responsePath = $this->writeBody($requestId, $responseBody, 'responses');

            $sql = new Sql($this->dbAdapter);
            $insert = $sql->insert('api_logs');
            $insert->values([
                'request_id'         => $requestId,
                'source'             => substr($source, 0, 64),
                'method'             => substr($method, 0, 10),
                'url'                => substr($url, 0, 1024),
                'request_body_path'  => $requestPath,
                'response_body_path' => $responsePath,
                'response_code'      => $statusCode,
                'duration_ms'        => $durationMs,
                'error'              => $error,
            ]);
            $this->dbAdapter->query($sql->buildSqlString($insert), $this->dbAdapter::QUERY_MODE_EXECUTE);
            return $requestId;
        } catch (\Throwable $t) {
            @error_log('ApiLogger.logCall failed: ' . $t->getMessage());
            return null;
        }
    }

    private function writeBody(string $requestId, ?string $raw, string $bucket): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        // Don't log binary bodies (media downloads). Cheap heuristic:
        // if the first 4KB has more than 5% non-printable, skip.
        $head = substr($raw, 0, 4096);
        $nonPrintable = preg_match_all('/[^\x09\x0A\x0D\x20-\x7E]/u', $head);
        if ($nonPrintable !== false && $nonPrintable > strlen($head) * 0.05) {
            return null;
        }
        if (strlen($raw) > self::MAX_BODY_BYTES) {
            $raw = substr($raw, 0, self::MAX_BODY_BYTES) . "\n…[truncated]";
        }

        $payload = $this->redact($raw);

        $dir = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'var'
            . DIRECTORY_SEPARATOR . 'api-logs'
            . DIRECTORY_SEPARATOR . $bucket;
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return null;
        }
        $stem = $dir . DIRECTORY_SEPARATOR . $requestId . '.json';
        try {
            return ArchiveUtility::compressContent($payload, $stem);
        } catch (\Throwable $t) {
            @error_log('ApiLogger.writeBody failed: ' . $t->getMessage());
            return null;
        }
    }

    private function redact(string $raw): string
    {
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return $raw;
        }
        return (string) json_encode($this->sanitise($decoded), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    private function sanitise(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array(strtolower((string) $key), self::REDACTED_FIELDS, true)) {
                $data[$key] = '[REDACTED]';
                continue;
            }
            if (is_array($value)) {
                $data[$key] = $this->sanitise($value);
            }
        }
        return $data;
    }
}

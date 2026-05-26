<?php

namespace Application\Controller;

use Application\Service\Logger;
use Laminas\Mvc\Controller\AbstractActionController;

/**
 * Accepts JSON error reports from the browser (uncaught JS errors, unhandled
 * promise rejections, manual reports via window.reportClientError) and
 * forwards them to a dedicated Monolog channel that writes
 * var/log/client-errors-YYYY-MM-DD.log. The LogViewer surfaces these
 * alongside server-side logs.
 *
 * Intentionally public so errors that happen on /login or after session
 * expiry still get captured. Hostile flooding is mitigated by per-field
 * size caps and the client-side dedupe in the layout JS snippet — add an
 * IP/time-window limit if abuse appears.
 */
class ClientErrorController extends AbstractActionController
{
    private const int MAX_MESSAGE = 2000;
    private const int MAX_STACK   = 8000;
    private const int MAX_URL     = 1000;
    private const int MAX_UA      = 500;

    public function __construct(private Logger $logger)
    {
    }

    public function indexAction()
    {
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');

        $request = $this->getRequest();
        if (!$request->isPost()) {
            $response->setStatusCode(405);
            $response->setContent('{"error":"method_not_allowed"}');
            return $response;
        }

        $payload = $this->decodePayload((string) $request->getContent());
        $message = $this->clip((string) ($payload['message'] ?? ''), self::MAX_MESSAGE);
        if ($message === '') {
            $response->setStatusCode(400);
            $response->setContent('{"error":"message_required"}');
            return $response;
        }

        $level = match ($payload['level'] ?? '') {
            'warning' => 'warning',
            'info'    => 'info',
            default   => 'error',
        };

        $context = array_filter([
            'source'      => $this->clip((string) ($payload['source'] ?? ''), self::MAX_URL),
            'line'        => isset($payload['line']) ? (int) $payload['line'] : null,
            'column'      => isset($payload['column']) ? (int) $payload['column'] : null,
            'url'         => $this->clip((string) ($payload['url'] ?? ''), self::MAX_URL),
            'user_agent'  => $this->clip((string) $request->getServer('HTTP_USER_AGENT', ''), self::MAX_UA),
            'app_version' => $this->clip((string) ($payload['app_version'] ?? ''), 32),
            'user_id'     => $this->resolveUserId(),
            'ip'          => $this->clientIp(),
            'stack'       => isset($payload['stack']) ? $this->clip((string) $payload['stack'], self::MAX_STACK) : null,
        ], fn ($v) => $v !== null && $v !== '');

        $this->logger->{$level}($message, $context);

        $response->setStatusCode(202);
        $response->setContent('{"received":true}');
        return $response;
    }

    private function decodePayload(string $raw): array
    {
        if ($raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function resolveUserId(): ?int
    {
        $session = new \Laminas\Session\Container('credo');
        return isset($session->userId) ? (int) $session->userId : null;
    }

    private function clientIp(): string
    {
        $request = $this->getRequest();
        $forwarded = (string) $request->getServer('HTTP_X_FORWARDED_FOR', '');
        if ($forwarded !== '') {
            $first = trim(explode(',', $forwarded)[0]);
            if ($first !== '') {
                return $first;
            }
        }
        return (string) $request->getServer('REMOTE_ADDR', '');
    }

    private function clip(string $value, int $max): string
    {
        if (strlen($value) <= $max) {
            return $value;
        }
        return substr($value, 0, $max) . '…';
    }
}

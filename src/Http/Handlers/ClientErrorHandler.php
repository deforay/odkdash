<?php

declare(strict_types=1);

namespace App\Http\Handlers;

use Application\Service\Logger;
use Application\Session\Container;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Accepts JSON error reports from the browser (uncaught JS errors, unhandled
 * promise rejections, manual reports via window.reportClientError) and
 * forwards them to a dedicated Monolog channel that writes
 * var/log/client-errors-YYYY-MM-DD.log. The LogViewer surfaces these
 * alongside server-side logs.
 *
 * Public on purpose, so errors on /login or after the session expires still
 * arrive. Per-field size caps and the client-side dedupe in the layout's
 * reporter limit flooding. Add an IP and time-window limit if abuse appears.
 */
final class ClientErrorHandler implements RequestHandlerInterface
{
    private const int MAX_MESSAGE = 2000;
    private const int MAX_STACK   = 8000;
    private const int MAX_URL     = 1000;
    private const int MAX_UA      = 500;

    public function __construct(
        private readonly ResponseFactoryInterface $responses,
        private readonly Logger $logger,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() !== 'POST') {
            return $this->json(405, '{"error":"method_not_allowed"}');
        }

        $payload = $this->decodePayload((string) $request->getBody());
        $message = $this->clip((string) ($payload['message'] ?? ''), self::MAX_MESSAGE);
        if ($message === '') {
            return $this->json(400, '{"error":"message_required"}');
        }

        $level = match ($payload['level'] ?? '') {
            'warning' => 'warning',
            'info'    => 'info',
            default   => 'error',
        };

        $server = $request->getServerParams();

        $context = array_filter([
            'source'      => $this->clip((string) ($payload['source'] ?? ''), self::MAX_URL),
            'line'        => isset($payload['line']) ? (int) $payload['line'] : null,
            'column'      => isset($payload['column']) ? (int) $payload['column'] : null,
            'url'         => $this->clip((string) ($payload['url'] ?? ''), self::MAX_URL),
            'user_agent'  => $this->clip((string) ($server['HTTP_USER_AGENT'] ?? ''), self::MAX_UA),
            'app_version' => $this->clip((string) ($payload['app_version'] ?? ''), 32),
            'user_id'     => $this->resolveUserId(),
            'ip'          => $this->clientIp($server),
            'stack'       => isset($payload['stack']) ? $this->clip((string) $payload['stack'], self::MAX_STACK) : null,
        ], fn ($v) => $v !== null && $v !== '');

        $this->logger->{$level}($message, $context);

        return $this->json(202, '{"received":true}');
    }

    private function json(int $status, string $body): ResponseInterface
    {
        $response = $this->responses->createResponse($status)
            ->withHeader('Content-Type', 'application/json');
        $response->getBody()->write($body);

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
        $session = new Container('credo');
        return isset($session->userId) ? (int) $session->userId : null;
    }

    /** @param array<string, mixed> $server */
    private function clientIp(array $server): string
    {
        $forwarded = (string) ($server['HTTP_X_FORWARDED_FOR'] ?? '');
        if ($forwarded !== '') {
            $first = trim(explode(',', $forwarded)[0]);
            if ($first !== '') {
                return $first;
            }
        }
        return (string) ($server['REMOTE_ADDR'] ?? '');
    }

    private function clip(string $value, int $max): string
    {
        if (strlen($value) <= $max) {
            return $value;
        }
        return substr($value, 0, $max) . '…';
    }
}

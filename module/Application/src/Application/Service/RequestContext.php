<?php

namespace Application\Service;

use Ramsey\Uuid\Uuid;

/**
 * Per-request helpers for capturing the network/browser/session context
 * onto event_log rows. Lazily memoises the request_id so multiple events
 * in the same request share one ID, which lets us reconstruct a request
 * timeline later.
 *
 * Reads $_SERVER directly because addEventLog is called from deep in the
 * model layer where the Laminas Request object isn't reachable.
 *
 * session_hash is sha256(session_id()) — a stable correlation key that
 * survives CGNAT (where many users share one public IP) without storing
 * the raw session ID (which would be a hijack risk if event_log leaked).
 */
final class RequestContext
{
    private static ?string $requestId = null;

    public static function getRequestId(): string
    {
        return self::$requestId ??= Uuid::uuid7()->toString();
    }

    public static function getClientIp(): ?string
    {
        // Honour the proxy chain header first; fall back to REMOTE_ADDR.
        // Trim to the left-most entry since that's the original client.
        $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        if ($forwarded !== '') {
            $first = trim(explode(',', $forwarded)[0]);
            if ($first !== '') {
                return substr($first, 0, 45);
            }
        }
        $remote = $_SERVER['REMOTE_ADDR'] ?? null;
        return $remote ? substr((string) $remote, 0, 45) : null;
    }

    public static function getUserAgent(): ?string
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
        return $ua ? substr((string) $ua, 0, 500) : null;
    }

    public static function getSessionHash(): ?string
    {
        // PHP sets a session ID once session_start() runs. If we're in a
        // CLI/cron context with no session, return null.
        $id = session_id();
        if ($id === '' || $id === false) {
            return null;
        }
        return hash('sha256', (string) $id);
    }

    public static function getRequestUri(): ?string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? null;
        return $uri ? substr((string) $uri, 0, 500) : null;
    }

    public static function getRequestMethod(): ?string
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? null;
        return $method ? substr((string) $method, 0, 10) : null;
    }

    /**
     * Crude UA → platform classifier. "web" for desktop browsers, "mobile"
     * for phones/tablets, "cli" for non-HTTP contexts. Good enough for the
     * platform chip in the event detail modal; we still store the full UA
     * for anyone who needs to dig in.
     */
    public static function getPlatform(): string
    {
        if (PHP_SAPI === 'cli') {
            return 'cli';
        }
        $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
        if ($ua === '') {
            return 'unknown';
        }
        if (preg_match('/Mobile|Android|iPhone|iPad|iPod/i', $ua)) {
            return 'mobile';
        }
        return 'web';
    }
}

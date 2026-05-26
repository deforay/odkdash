<?php

namespace Application\Service;

use Throwable;
use Monolog\Level;
use Monolog\Logger as MonologLogger;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\RotatingFileHandler;

/**
 * Application logger. Wraps Monolog with a rotating-file handler that writes
 * to data/logs/, falling back to PHP's error_log() if the directory isn't
 * writable. Never throws — logging must never crash the request that asked
 * for it.
 *
 * Pattern adapted from the vlsm project; trimmed because this app doesn't
 * need the recursive context sanitizer or the call-count loop guard.
 *
 * Usage:
 *   $logger = $serviceManager->get('Logger');
 *   $logger->error('boom', ['user' => $userId]);
 *   $logger->logException($throwable);
 */
final class Logger
{
    private const DEFAULT_RETENTION_DAYS = 30;
    private const MAX_MESSAGE_LENGTH = 8000;

    private ?MonologLogger $logger = null;
    private string $logDir;

    public function __construct(?string $logDir = null)
    {
        $this->logDir = $logDir ?? $this->defaultLogDir();
    }

    public function info(string $message, array $context = []): void
    {
        $this->log(Level::Info, $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log(Level::Warning, $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log(Level::Error, $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log(Level::Critical, $message, $context);
    }

    /**
     * Log a Throwable with full class, message, file:line, and trace.
     */
    public function logException(Throwable $e, string $prefix = 'Unhandled exception'): void
    {
        $this->log(
            Level::Error,
            $prefix . ': ' . $e::class . ' — ' . $e->getMessage(),
            [
                'file'  => $e->getFile() . ':' . $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'previous' => $e->getPrevious()?->getMessage(),
            ]
        );
    }

    public function getLogDir(): string
    {
        return $this->logDir;
    }

    private function log(Level $level, string $message, array $context): void
    {
        try {
            if (\strlen($message) > self::MAX_MESSAGE_LENGTH) {
                $message = \substr($message, 0, self::MAX_MESSAGE_LENGTH) . '... [truncated]';
            }
            $this->getLogger()->log($level, $message, $context);
        } catch (Throwable $e) {
            // Logging must never bubble up. Surface to PHP's error_log as a last resort.
            @\error_log('Logger::log failed: ' . $e->getMessage() . ' | original: ' . \substr($message, 0, 300));
        }
    }

    private function getLogger(): MonologLogger
    {
        if ($this->logger instanceof MonologLogger) {
            return $this->logger;
        }

        $this->logger = new MonologLogger('app');

        try {
            if (!is_dir($this->logDir)) {
                @mkdir($this->logDir, 0775, true);
            }
            if (is_dir($this->logDir) && is_writable($this->logDir)) {
                $handler = new RotatingFileHandler(
                    $this->logDir . '/app.log',
                    self::DEFAULT_RETENTION_DAYS,
                    Level::Info,
                    true,
                    0664
                );
                $handler->setFilenameFormat('{filename}-{date}', 'Y-m-d');
                $this->logger->pushHandler($handler);
            } else {
                $this->pushFallbackHandler('log dir not writable: ' . $this->logDir);
            }
        } catch (Throwable $e) {
            $this->pushFallbackHandler('logger init failed: ' . $e->getMessage());
        }

        // Guarantee at least one handler so $logger->log() never errors.
        if ($this->logger->getHandlers() === []) {
            $this->pushFallbackHandler('no handlers configured');
        }

        return $this->logger;
    }

    private function pushFallbackHandler(string $reason): void
    {
        try {
            $this->logger?->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Level::Warning));
            @error_log('Logger fallback to error_log: ' . $reason);
        } catch (Throwable $e) {
            @error_log('Logger fallback handler failed: ' . $e->getMessage() . ' | reason: ' . $reason);
        }
    }

    private function defaultLogDir(): string
    {
        // module/Application/src/Application/Service → repo root
        return dirname(__DIR__, 5) . '/var/log';
    }
}

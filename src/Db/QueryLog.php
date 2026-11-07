<?php

declare(strict_types=1);

namespace App\Db;

use Laminas\Db\Adapter\Profiler\ProfilerInterface;
use Laminas\Db\Adapter\StatementContainerInterface;
use Psr\Log\AbstractLogger;
use RuntimeException;
use Stringable;

/**
 * A development log of every SQL statement the app runs, through laminas-db
 * or DBAL, for comparing two versions of the code.
 *
 * It is off unless the ODKDASH_SQL_LOG environment variable names a file:
 *
 *     ODKDASH_SQL_LOG=/tmp/sql.log php -S 127.0.0.1:8765 -t public
 *
 * Each line is one JSON object: {"sql": "...", "params": {...}}. The
 * laminas-db adapter calls it as its profiler. DBAL calls it as the logger
 * of its logging middleware. Transaction and connection messages are not
 * written, so the log holds statements only.
 *
 * Secrets are written as <redacted>: password hashes, literals next to a
 * password or secret column, and every string parameter of a statement
 * that names such a column. The file is kept readable by its owner only.
 */
final class QueryLog extends AbstractLogger implements ProfilerInterface
{
    /** A column name that holds a secret. */
    private const string SECRET_COLUMN = '\w*(?:password|secret|api_?key)\w*';

    private const array SECRETS = [
        // bcrypt and argon2 hashes, as password_hash() makes them.
        '/\$2[abxy]\$\d{2}\$[.\/A-Za-z0-9]{53}/' => '<redacted>',
        '/\$argon2(?:i|d|id)\$[^\s\'"]+/' => '<redacted>',
        // A literal next to a password or secret column: password = '...'.
        '/(`?\b' . self::SECRET_COLUMN . '`?\s*(?:=|<>|!=)\s*)\'(?:[^\'\\\\]|\\\\.|\'\')*\'/i' => '$1\'<redacted>\'',
    ];

    private function __construct(private readonly string $file)
    {
    }

    /** The log for this process, or null when ODKDASH_SQL_LOG is unset. */
    public static function fromEnvironment(): ?self
    {
        $file = getenv('ODKDASH_SQL_LOG');

        return is_string($file) && $file !== '' ? new self($file) : null;
    }

    /** laminas-db: a query string, or a prepared statement with its parameters. */
    public function profilerStart($target): self
    {
        if ($target instanceof StatementContainerInterface) {
            $this->write($target->getSql(), $target->getParameterContainer()?->getNamedArray() ?? []);
        } else {
            $this->write((string) $target, []);
        }

        return $this;
    }

    public function profilerFinish(): self
    {
        return $this;
    }

    /** DBAL: only the messages that carry a statement. */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        if (isset($context['sql'])) {
            $this->write((string) $context['sql'], $context['params'] ?? []);
        }
    }

    /** @param array<array-key, mixed> $params */
    private function write(string $sql, array $params): void
    {
        // A bound value carries no column name, so a statement that names a
        // secret column has all its string values hidden.
        $namesSecret = preg_match('/\b' . self::SECRET_COLUMN . '\b/i', $sql) === 1;
        $params = array_map(
            static fn(mixed $value): mixed => !is_string($value) ? $value : ($namesSecret ? '<redacted>' : self::redact($value)),
            $params,
        );
        $line = json_encode(['sql' => self::redact($sql), 'params' => $params], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        if (!is_file($this->file) && !@touch($this->file)) {
            throw new RuntimeException(sprintf('Cannot create the SQL log %s.', $this->file));
        }
        if ((fileperms($this->file) & 0077) !== 0 && !@chmod($this->file, 0600)) {
            throw new RuntimeException(sprintf('Cannot make the SQL log %s readable by its owner only.', $this->file));
        }
        clearstatcache(true, $this->file);
        // A missing line would read as a difference between two versions.
        if (@file_put_contents($this->file, $line . "\n", FILE_APPEND | LOCK_EX) !== strlen($line) + 1) {
            throw new RuntimeException(sprintf('Cannot write to the SQL log %s.', $this->file));
        }
    }

    private static function redact(string $text): string
    {
        return (string) preg_replace(array_keys(self::SECRETS), array_values(self::SECRETS), $text);
    }
}

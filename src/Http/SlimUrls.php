<?php

declare(strict_types=1);

namespace App\Http;

use InvalidArgumentException;
use RuntimeException;
use Slim\Interfaces\RouteCollectorInterface;

/**
 * Builds URLs for Slim routes from their segment patterns.
 *
 * Each route registers its segment pattern, such as /roles[/:action][/][:id],
 * with its defaults. URLs are assembled from that pattern with these rules:
 *
 * - The defaults are merged under the given parameters first.
 * - An optional group is left out when every parameter in it is missing or
 *   equal to its default.
 * - An optional group with no parameter, such as [/], is always kept. That
 *   is why url('roles') is /roles/, not /roles.
 * - Values are encoded with rawurlencode(), then the characters in KEEP are
 *   put back.
 *
 * Slim's own urlFor() follows none of these rules.
 */
final class SlimUrls
{
    /** Characters put back after rawurlencode(). */
    private const array KEEP = [
        '%21' => '!', '%24' => '$', '%26' => '&', '%27' => "'", '%28' => '(', '%29' => ')',
        '%2A' => '*', '%2B' => '+', '%2C' => ',', '%3A' => ':', '%3B' => ';', '%3D' => '=', '%40' => '@',
    ];

    /** @var array<string, array{parts: list<array{0: string, 1: mixed}>, defaults: array<string, string>}> */
    private array $patterns = [];

    public function __construct(private readonly RouteCollectorInterface $routes)
    {
    }

    /**
     * Registers the segment pattern of a Slim route.
     *
     * @param array<string, string> $defaults
     */
    public function register(string $route, string $segmentPattern, array $defaults = []): void
    {
        $this->patterns[$route] = ['parts' => self::parse($segmentPattern), 'defaults' => $defaults];
    }

    public function has(string $name): bool
    {
        return isset($this->patterns[$name]);
    }

    /** @return list<string> */
    public function names(): array
    {
        return array_keys($this->patterns);
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $query
     */
    public function build(string $name, array $params = [], array $query = []): string
    {
        $url = $this->routes->getBasePath() . $this->path($name, $params);

        return $query === [] ? $url : $url . '?' . http_build_query($query);
    }

    /**
     * The path below the base path.
     *
     * @param array<string, mixed> $params
     */
    public function path(string $name, array $params = []): string
    {
        $pattern = $this->patterns[$name] ?? throw new RuntimeException(sprintf('No Slim route named "%s".', $name));

        $merged = array_merge($pattern['defaults'], $params);

        return self::buildPath($pattern['parts'], $merged, $pattern['defaults'], false);
    }

    /**
     * @param list<array{0: string, 1: mixed}> $parts
     * @param array<string, mixed> $params
     * @param array<string, string> $defaults
     */
    private static function buildPath(array $parts, array $params, array $defaults, bool $isOptional): string
    {
        $path = '';
        $skip = true;
        $skippable = false;

        foreach ($parts as [$type, $value]) {
            if ($type === 'literal') {
                $path .= $value;
                continue;
            }

            $skippable = true;

            if ($type === 'optional') {
                $optional = self::buildPath($value, $params, $defaults, true);
                if ($optional !== '') {
                    $path .= $optional;
                    $skip = false;
                }
                continue;
            }

            if (!isset($params[$value])) {
                if (!$isOptional) {
                    throw new InvalidArgumentException(sprintf('Missing parameter "%s".', $value));
                }
                return '';
            }
            if (!$isOptional || !isset($defaults[$value]) || $defaults[$value] !== $params[$value]) {
                $skip = false;
            }
            $path .= strtr(rawurlencode((string) $params[$value]), self::KEEP);
        }

        return $isOptional && $skippable && $skip ? '' : $path;
    }

    /**
     * Splits a segment pattern into literal, parameter and optional
     * parts. Covers the syntax odkdash's routes use: literals, :name and
     * nested [...] groups.
     *
     * @return list<array{0: string, 1: mixed}>
     */
    private static function parse(string $pattern): array
    {
        $offset = 0;
        $parts = self::parseGroup($pattern, $offset);
        if ($offset !== strlen($pattern)) {
            throw new InvalidArgumentException(sprintf('Unbalanced "]" in route pattern "%s".', $pattern));
        }

        return $parts;
    }

    /** @return list<array{0: string, 1: mixed}> */
    private static function parseGroup(string $pattern, int &$offset): array
    {
        $parts = [];
        $length = strlen($pattern);

        while ($offset < $length) {
            $char = $pattern[$offset];

            if ($char === '[') {
                $offset++;
                $parts[] = ['optional', self::parseGroup($pattern, $offset)];
                if (($pattern[$offset] ?? '') !== ']') {
                    throw new InvalidArgumentException(sprintf('Unclosed "[" in route pattern "%s".', $pattern));
                }
                $offset++;
            } elseif ($char === ']') {
                return $parts;
            } elseif ($char === ':') {
                preg_match('/\G:([^:\/{\[\]]+)/', $pattern, $match, 0, $offset);
                $parts[] = ['parameter', $match[1]];
                $offset += strlen($match[0]);
            } else {
                preg_match('/\G[^:\[\]]+/', $pattern, $match, 0, $offset);
                $parts[] = ['literal', $match[0]];
                $offset += strlen($match[0]);
            }
        }

        return $parts;
    }
}

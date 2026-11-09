<?php

namespace Application\Model;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

/**
 * Read-side queries for the API Logs viewer. The api_logs table is
 * populated by GuzzleApiLogMiddleware around the sync-central-v3 /
 * sync-central-v6 outbound calls — writes happen via ApiLogger, not
 * through this class.
 */
class ApiLogsTable
{
    private readonly Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Filtered list for the grid.
     *
     * @param array<string,mixed> $params
     * @return array{rows: array<int, array<string, mixed>>, total: int}
     */
    public function fetchList(array $params): array
    {
        [$where, $values] = $this->buildWhere($params);

        $total = (int) $this->db->fetchOne("SELECT COUNT(*) FROM api_logs $where", $values);

        $sortable = ['created_at', 'method', 'url', 'response_code', 'duration_ms', 'source'];
        $sortCol = in_array($params['sort'] ?? '', $sortable, true) ? $params['sort'] : 'created_at';
        $sortDir = strtolower((string) ($params['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';

        $perPage = max(1, min(100, (int) ($params['per_page'] ?? 50)));
        $page = max(1, (int) ($params['page'] ?? 1));
        $offset = ($page - 1) * $perPage;

        $rows = $this->db->fetchAllAssociative(
            "SELECT request_id, created_at, source, method, url, response_code, duration_ms, error
            FROM api_logs $where
            ORDER BY $sortCol $sortDir
            LIMIT $perPage OFFSET $offset",
            $values
        );

        return ['rows' => $rows, 'total' => $total];
    }

    public function fetchOne(string $requestId): ?array
    {
        $row = $this->db->fetchAssociative('SELECT * FROM api_logs WHERE request_id = ? LIMIT 1', [$requestId]);
        return $row ?: null;
    }

    /** Distinct source tags for the filter dropdown. */
    public function fetchSources(): array
    {
        return $this->db->fetchFirstColumn('SELECT source FROM api_logs WHERE source IS NOT NULL GROUP BY source ORDER BY source ASC');
    }

    public function fetchStats(int $hours = 24): array
    {
        $since = (new \DateTimeImmutable("-$hours hours"))->format('Y-m-d H:i:s');

        $totals = $this->db->fetchAssociative(
            'SELECT COUNT(*) AS total,
                SUM(CASE WHEN response_code >= 400 OR error IS NOT NULL THEN 1 ELSE 0 END) AS errors,
                COALESCE(AVG(duration_ms), 0) AS avg_duration
            FROM api_logs WHERE created_at >= ?',
            [$since]
        ) ?: [];
        $total = (int) ($totals['total'] ?? 0);
        $errors = (int) ($totals['errors'] ?? 0);

        $p95 = 0;
        if ($total > 0) {
            $offset = (int) max(0, floor($total * 0.05));
            $p95 = (int) $this->db->fetchOne(
                "SELECT duration_ms FROM api_logs WHERE created_at >= ? ORDER BY duration_ms DESC LIMIT 1 OFFSET $offset",
                [$since]
            );
        }

        $topErrors = $this->db->fetchAllAssociative(
            'SELECT url, response_code, COUNT(*) AS count
            FROM api_logs
            WHERE created_at >= ? AND (response_code >= 400 OR error IS NOT NULL)
            GROUP BY url, response_code
            ORDER BY count DESC
            LIMIT 10',
            [$since]
        );

        return [
            'total_requests'   => $total,
            'error_count'      => $errors,
            'error_rate'       => $total > 0 ? round($errors / $total * 100, 1) : 0.0,
            'avg_duration_ms'  => (int) round((float) ($totals['avg_duration'] ?? 0)),
            'p95_duration_ms'  => $p95,
            'top_errors'       => $topErrors,
        ];
    }

    /**
     * Deletes rows older than $beforeDate but always retains the most
     * recent $floor rows even if older. Returns the deleted body paths
     * so the caller can unlink files.
     *
     * @return array{deleted:int, paths:array<int,array{request_body_path:?string,response_body_path:?string}>}
     */
    public function purgeOlderThan(\DateTimeImmutable $beforeDate, int $floor): array
    {
        $keepIds = [];
        if ($floor > 0) {
            $keepIds = $this->db->fetchFirstColumn("SELECT request_id FROM api_logs ORDER BY created_at DESC LIMIT $floor");
        }

        $sql = 'SELECT request_id, request_body_path, response_body_path FROM api_logs WHERE created_at < ?';
        $values = [$beforeDate->format('Y-m-d H:i:s')];
        $types = [ParameterType::STRING];
        if ($keepIds !== []) {
            $sql .= ' AND request_id NOT IN (?)';
            $values[] = $keepIds;
            $types[] = ArrayParameterType::STRING;
        }
        $victims = $this->db->fetchAllAssociative($sql, $values, $types);

        if ($victims === []) {
            return ['deleted' => 0, 'paths' => []];
        }

        $this->db->executeStatement(
            'DELETE FROM api_logs WHERE request_id IN (?)',
            [array_column($victims, 'request_id')],
            [ArrayParameterType::STRING]
        );

        return [
            'deleted' => count($victims),
            'paths'   => array_map(
                fn ($v) => [
                    'request_body_path'  => $v['request_body_path'] ?? null,
                    'response_body_path' => $v['response_body_path'] ?? null,
                ],
                $victims
            ),
        ];
    }

    /**
     * The WHERE clause for the grid's filters and its values.
     *
     * @param array<string,mixed> $params
     * @return array{0: string, 1: list<mixed>}
     */
    private function buildWhere(array $params): array
    {
        $where = [];
        $values = [];
        if (!empty($params['method'])) {
            $where[] = 'method = ?';
            $values[] = strtoupper((string) $params['method']);
        }
        if (!empty($params['source'])) {
            $where[] = 'source = ?';
            $values[] = $params['source'];
        }
        if (!empty($params['url'])) {
            $where[] = 'url LIKE ?';
            $values[] = '%' . $params['url'] . '%';
        }
        if (!empty($params['status'])) {
            if ($params['status'] === 'error') {
                $where[] = '(response_code >= 400 OR error IS NOT NULL)';
            } elseif ($params['status'] === 'success') {
                $where[] = 'response_code < 400 AND error IS NULL';
            } else {
                $where[] = 'response_code = ?';
                $values[] = (int) $params['status'];
            }
        }
        if (!empty($params['from'])) {
            $where[] = 'created_at >= ?';
            $values[] = $params['from'] . ' 00:00:00';
        }
        if (!empty($params['to'])) {
            $where[] = 'created_at <= ?';
            $values[] = $params['to'] . ' 23:59:59';
        }
        if (!empty($params['slow'])) {
            $where[] = 'duration_ms >= ?';
            $values[] = (int) $params['slow'];
        }

        return [$where === [] ? '' : 'WHERE ' . implode(' AND ', $where), $values];
    }
}

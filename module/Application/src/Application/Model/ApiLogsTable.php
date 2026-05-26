<?php

namespace Application\Model;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\TableGateway\AbstractTableGateway;

/**
 * Read-side queries for the API Logs viewer. The api_logs table is
 * populated by GuzzleApiLogMiddleware around the sync-central-v3 /
 * sync-central-v6 outbound calls — writes happen via ApiLogger, not
 * through this class.
 */
class ApiLogsTable extends AbstractTableGateway
{
    protected $table = 'api_logs';
    protected $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Filtered list for the grid.
     *
     * @param array<string,mixed> $params
     * @return array{rows: array<int, array<string, mixed>>, total: int}
     */
    public function fetchList(array $params): array
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select($this->table);

        $where = $this->buildWhere($params);
        if ($where !== []) {
            $select->where($where);
        }

        $countSelect = clone $select;
        $countSelect->reset(Select::COLUMNS)->reset(Select::ORDER)
            ->columns(['c' => new \Laminas\Db\Sql\Expression('COUNT(*)')]);
        $countRow = $sql->prepareStatementForSqlObject($countSelect)->execute()->current();
        $total = (int) ($countRow['c'] ?? 0);

        $sortable = ['created_at', 'method', 'url', 'response_code', 'duration_ms', 'source'];
        $sortCol = in_array($params['sort'] ?? '', $sortable, true) ? $params['sort'] : 'created_at';
        $sortDir = strtolower((string) ($params['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';

        $perPage = max(1, min(100, (int) ($params['per_page'] ?? 50)));
        $page = max(1, (int) ($params['page'] ?? 1));

        $select->columns([
            'request_id', 'created_at', 'source', 'method', 'url',
            'response_code', 'duration_ms', 'error',
        ])
            ->order("$sortCol $sortDir")
            ->offset(($page - 1) * $perPage)
            ->limit($perPage);

        $rows = iterator_to_array($sql->prepareStatementForSqlObject($select)->execute());

        return ['rows' => $rows, 'total' => $total];
    }

    public function fetchOne(string $requestId): ?array
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select($this->table)->where(['request_id' => $requestId])->limit(1);
        $row = $sql->prepareStatementForSqlObject($select)->execute()->current();
        return $row ?: null;
    }

    /** Distinct source tags for the filter dropdown. */
    public function fetchSources(): array
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select($this->table)
            ->columns(['source'])
            ->where(['source IS NOT NULL'])
            ->group(['source'])
            ->order('source ASC');
        return array_column(iterator_to_array($sql->prepareStatementForSqlObject($select)->execute()), 'source');
    }

    public function fetchStats(int $hours = 24): array
    {
        $since = (new \DateTimeImmutable("-$hours hours"))->format('Y-m-d H:i:s');
        $sql = new Sql($this->adapter);

        $totalsSelect = $sql->select($this->table)
            ->where(['created_at >= ?' => $since])
            ->columns([
                'total' => new \Laminas\Db\Sql\Expression('COUNT(*)'),
                'errors' => new \Laminas\Db\Sql\Expression('SUM(CASE WHEN response_code >= 400 OR error IS NOT NULL THEN 1 ELSE 0 END)'),
                'avg_duration' => new \Laminas\Db\Sql\Expression('COALESCE(AVG(duration_ms), 0)'),
            ]);
        $totals = $sql->prepareStatementForSqlObject($totalsSelect)->execute()->current() ?: [];
        $total = (int) ($totals['total'] ?? 0);
        $errors = (int) ($totals['errors'] ?? 0);

        $p95 = 0;
        if ($total > 0) {
            $offset = (int) max(0, floor($total * 0.05));
            $p95Select = $sql->select($this->table)
                ->where(['created_at >= ?' => $since])
                ->columns(['duration_ms'])
                ->order('duration_ms DESC')
                ->offset($offset)
                ->limit(1);
            $p95Row = $sql->prepareStatementForSqlObject($p95Select)->execute()->current();
            $p95 = (int) ($p95Row['duration_ms'] ?? 0);
        }

        $topErrSelect = $sql->select($this->table)
            ->where(['created_at >= ?' => $since])
            ->where->nest()
                ->greaterThanOrEqualTo('response_code', 400)
                ->or
                ->isNotNull('error')
            ->unnest();
        $topErrSelect
            ->columns([
                'url', 'response_code',
                'count' => new \Laminas\Db\Sql\Expression('COUNT(*)'),
            ])
            ->group(['url', 'response_code'])
            ->order('count DESC')
            ->limit(10);
        $topErrors = iterator_to_array($sql->prepareStatementForSqlObject($topErrSelect)->execute());

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
        $sql = new Sql($this->adapter);

        $keepIds = [];
        if ($floor > 0) {
            $keepSelect = $sql->select($this->table)
                ->columns(['request_id'])
                ->order('created_at DESC')
                ->limit($floor);
            foreach ($sql->prepareStatementForSqlObject($keepSelect)->execute() as $row) {
                $keepIds[] = $row['request_id'];
            }
        }

        $toDeleteSelect = $sql->select($this->table)
            ->columns(['request_id', 'request_body_path', 'response_body_path'])
            ->where(['created_at < ?' => $beforeDate->format('Y-m-d H:i:s')]);
        if ($keepIds !== []) {
            $toDeleteSelect->where->notIn('request_id', $keepIds);
        }
        $victims = iterator_to_array($sql->prepareStatementForSqlObject($toDeleteSelect)->execute());

        if ($victims === []) {
            return ['deleted' => 0, 'paths' => []];
        }

        $ids = array_column($victims, 'request_id');
        $del = $sql->delete($this->table);
        $del->where->in('request_id', $ids);
        $sql->prepareStatementForSqlObject($del)->execute();

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

    /** @param array<string,mixed> $params */
    private function buildWhere(array $params): array
    {
        $where = [];
        if (!empty($params['method'])) {
            $where['method'] = strtoupper((string) $params['method']);
        }
        if (!empty($params['source'])) {
            $where['source'] = $params['source'];
        }
        if (!empty($params['url'])) {
            $where[] = new \Laminas\Db\Sql\Predicate\Like('url', '%' . $params['url'] . '%');
        }
        if (!empty($params['status'])) {
            if ($params['status'] === 'error') {
                $where[] = new \Laminas\Db\Sql\Predicate\PredicateSet([
                    new \Laminas\Db\Sql\Predicate\Operator('response_code', '>=', 400),
                    new \Laminas\Db\Sql\Predicate\IsNotNull('error'),
                ], \Laminas\Db\Sql\Predicate\PredicateSet::OP_OR);
            } elseif ($params['status'] === 'success') {
                $where[] = new \Laminas\Db\Sql\Predicate\Operator('response_code', '<', 400);
                $where[] = new \Laminas\Db\Sql\Predicate\IsNull('error');
            } else {
                $where['response_code'] = (int) $params['status'];
            }
        }
        if (!empty($params['from'])) {
            $where[] = new \Laminas\Db\Sql\Predicate\Operator('created_at', '>=', $params['from'] . ' 00:00:00');
        }
        if (!empty($params['to'])) {
            $where[] = new \Laminas\Db\Sql\Predicate\Operator('created_at', '<=', $params['to'] . ' 23:59:59');
        }
        if (!empty($params['slow'])) {
            $where[] = new \Laminas\Db\Sql\Predicate\Operator('duration_ms', '>=', (int) $params['slow']);
        }
        return $where;
    }
}

<?php

namespace Application\Model;

use App\Db\Dbal;
use Application\Session\Container;
use Application\Service\CommonService;
use Application\Service\RequestContext;
use Doctrine\DBAL\Connection;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;

class EventLogTable
{
    private readonly Connection $db;

    public function __construct(private readonly Adapter $adapter)
    {
        $this->db = Dbal::connection($adapter);
    }

    public function addEventLog($subject, $eventType, $action, $resourceName)
    {
        $loginContainer = new Container('credo');
        $actorId = $loginContainer->userId;
        $currentDateTime = CommonService::getDateTime();

        $data = array(
            'actor'          => $actorId,
            'subject'        => $subject,
            'event_type'     => $eventType,
            'action'         => $action,
            'resource_name'  => $resourceName,
            'date_time'      => $currentDateTime,
            'ip_address'     => RequestContext::getClientIp(),
            'user_agent'     => RequestContext::getUserAgent(),
            'session_hash'   => RequestContext::getSessionHash(),
            'request_id'     => RequestContext::getRequestId(),
            'request_uri'    => RequestContext::getRequestUri(),
            'request_method' => RequestContext::getRequestMethod(),
            'platform'       => RequestContext::getPlatform(),
        );
        $this->db->insert('event_log', $data);
    }

    public function fetchAllDetails($parameters)
    {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */
        $loginContainer = new Container('credo');
        $aColumns = array('event_type', 'action', 'resource_name', 'date_time');

        /*
         * Paging
         */
        $sLimit = "";
        if (isset($parameters['iDisplayStart']) && $parameters['iDisplayLength'] != '-1') {
            $sOffset = $parameters['iDisplayStart'];
            $sLimit = $parameters['iDisplayLength'];
        }

        /*
         * Ordering
         */
        $sOrder = "";
        if (isset($parameters['iSortCol_0'])) {
            for ($i = 0; $i < (int) $parameters['iSortingCols']; $i++) {
                if ($parameters['bSortable_' . (int) $parameters['iSortCol_' . $i]] == "true" && isset($aColumns[(int) $parameters['iSortCol_' . $i]])) {
                    $sOrder .= $aColumns[(int) $parameters['iSortCol_' . $i]] . " " . ($parameters['sSortDir_' . $i]) . ", ";
                }
            }
            $sOrder = rtrim($sOrder, ", ");
        }

        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $sWhere = CommonService::dataTablesSearch($aColumns, $parameters);

        /*
         * SQL queries
         * Get data to display
         */
        // The builder still writes the search and the sort. DBAL runs the SQL.
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from('event_log');

        $startDate = '';
        $endDate = '';
        if (isset($parameters['dateRange']) && ($parameters['dateRange'] != "")) {
            $dateField = explode("to", $parameters['dateRange']);
            if (isset($dateField[0]) && trim($dateField[0]) != "") {
                $startDate = CommonService::isoDateFormat($dateField[0]);
            }
            if (isset($dateField[1]) && trim($dateField[1]) != "") {
                $endDate = CommonService::isoDateFormat($dateField[1]);
            }
        }
        if (trim($startDate) != "" && trim($endDate) != "") {
            $sQuery = $sQuery->where(['date_time >= ?' => $startDate, 'date_time <= ?' => $endDate]);
        }
        if ($parameters['eventType'] != '') {
            $sQuery = $sQuery->where(['event_type LIKE ?' => '%' . $parameters['eventType'] . '%']);
        }

        if ($sWhere !== null) {
            $sQuery->where($sWhere);
        }

        if (isset($sOrder) && $sOrder != "") {
            $sQuery->order($sOrder);
        }

        if (isset($sLimit) && isset($sOffset)) {
            $sQuery->limit($sLimit);
            $sQuery->offset($sOffset);
        }

        $rResult = $this->db->fetchAllAssociative($sql->buildSqlString($sQuery));

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $iFilteredTotal = (int) $this->db->fetchOne('SELECT COUNT(*) FROM (' . $sql->buildSqlString($sQuery) . ') AS filtered');

        /* Total data set length */
        $iTotal = (int) $this->db->fetchOne('SELECT COUNT(*) FROM event_log');

        $output = array(
            "sEcho" => (int) $parameters['sEcho'],
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        foreach ($rResult as $aRow) {
            $row = [];
            $row[] = CommonService::escapeHtml(ucwords($aRow['event_type']));
            $row[] = CommonService::escapeHtml(ucwords($aRow['action']));
            /* resource_name is whatever the logged record was called — a facility
             * name reaches here straight from an ODK submission. */
            $row[] = CommonService::escapeHtml(ucwords($aRow['resource_name']));
            $row[] = ucwords(date('d-M-Y H:i:s', strtotime($aRow['date_time'])));
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function fetchFeed(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $pageSize = (int) ($params['pageSize'] ?? 25);
        $pageSize = max(10, min(200, $pageSize));
        $offset = ($page - 1) * $pageSize;

        $where = [];
        $values = [];
        if (!empty($params['startDate'])) {
            $where[] = 'e.date_time >= ?';
            $values[] = CommonService::isoDateFormat($params['startDate']) . ' 00:00:00';
        }
        if (!empty($params['endDate'])) {
            $where[] = 'e.date_time <= ?';
            $values[] = CommonService::isoDateFormat($params['endDate']) . ' 23:59:59';
        }
        if (!empty($params['eventType'])) {
            $where[] = 'e.event_type = ?';
            $values[] = (string) $params['eventType'];
        }
        if (!empty($params['actor'])) {
            $where[] = 'e.actor = ?';
            $values[] = (int) $params['actor'];
        }
        if (!empty($params['search'])) {
            $where[] = '(e.action LIKE ? OR e.event_type LIKE ? OR e.resource_name LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.login LIKE ?)';
            array_push($values, ...array_fill(0, 6, '%' . $params['search'] . '%'));
        }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int) $this->db->fetchOne("SELECT COUNT(*) FROM event_log AS e LEFT JOIN users AS u ON u.id = e.actor $whereSql", $values);

        $rows = $this->db->fetchAllAssociative(
            "SELECT e.event_id, e.actor, e.subject, e.event_type, e.action, e.resource_name, e.date_time,
                    e.ip_address, e.user_agent, e.session_hash, e.request_id, e.request_uri,
                    e.request_method, e.platform,
                    u.first_name, u.last_name, u.login
            FROM event_log AS e
            LEFT JOIN users AS u ON u.id = e.actor
            $whereSql
            ORDER BY e.date_time DESC, e.event_id DESC
            LIMIT $pageSize OFFSET $offset",
            $values
        );

        $items = [];
        foreach ($rows as $r) {
            $ts = strtotime($r['date_time']);
            $name = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?: ($r['login'] ?? 'System');
            $initials = '';
            $parts = preg_split('/\s+/', $name);
            foreach (array_slice($parts, 0, 2) as $p) {
                $initials .= strtoupper(substr($p, 0, 1));
            }
            $items[] = [
                'id'            => (int) $r['event_id'],
                'action'        => $r['action'] ?? '',
                'eventType'     => $r['event_type'] ?? '',
                'resourceName'  => $r['resource_name'] ?? '',
                'userName'      => $name,
                'userInitials'  => $initials ?: 'S',
                'userLogin'     => $r['login'] ?? '',
                'time'          => $ts ? date('g:i a', $ts) : '',
                'dateKey'       => $ts ? date('Y-m-d', $ts) : '',
                'dateLabel'     => $ts ? date('D, j M Y', $ts) : '',
                'datetime'      => $r['date_time'] ?? '',
                'ipAddress'     => $r['ip_address'] ?? '',
                'userAgent'     => $r['user_agent'] ?? '',
                'sessionHash'   => $r['session_hash'] ?? '',
                'requestId'     => $r['request_id'] ?? '',
                'requestUri'    => $r['request_uri'] ?? '',
                'requestMethod' => $r['request_method'] ?? '',
                'platform'      => $r['platform'] ?? '',
            ];
        }

        return [
            'items'      => $items,
            'total'      => $total,
            'page'       => $page,
            'pageSize'   => $pageSize,
            'totalPages' => $pageSize > 0 ? (int) ceil($total / $pageSize) : 1,
        ];
    }

    public function fetchEventTypes(): array
    {
        return $this->db->fetchFirstColumn("SELECT DISTINCT event_type FROM event_log WHERE event_type IS NOT NULL AND event_type <> '' ORDER BY event_type");
    }
}

<?php

namespace Application\Model;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Sql\Expression;
use Laminas\Session\Container;
use Application\Service\CommonService;


/**
 * Description of Countries
 *
 * @author amit
 */
class EventLogTable extends AbstractTableGateway
{

    protected $table = 'event_log';
    protected $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function addEventLog($subject, $eventType, $action, $resourceName)
    {
        $loginContainer = new Container('credo');
        $actorId = $loginContainer->userId;
        $currentDateTime = CommonService::getDateTime();

        $data = array(
            'actor' => $actorId,
            'subject' => $subject,
            'event_type' => $eventType,
            'action' => $action,
            'resource_name' => $resourceName,
            'date_time' => $currentDateTime
        );
        $id = $this->insert($data);
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
                if ($parameters['bSortable_' . (int) $parameters['iSortCol_' . $i]] == "true") {
                    $sOrder .= $aColumns[(int) $parameters['iSortCol_' . $i]] . " " . ($parameters['sSortDir_' . $i]) . ",";
                }
            }
            $sOrder = substr_replace($sOrder, "", -1);
        }

        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $sWhere = "";
        if (isset($parameters['sSearch']) && $parameters['sSearch'] != "") {
            $searchArray = explode(" ", $parameters['sSearch']);
            $sWhereSub = "";
            foreach ($searchArray as $search) {
                if ($sWhereSub == "") {
                    $sWhereSub .= "(";
                } else {
                    $sWhereSub .= " AND (";
                }
                $colSize = count($aColumns);

                for ($i = 0; $i < $colSize; $i++) {
                    if ($i < $colSize - 1) {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' OR ";
                    } else {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' ";
                    }
                }
                $sWhereSub .= ")";
            }
            $sWhere .= $sWhereSub;
        }
        /* Individual column filtering */
        $counter = count($aColumns);

        /* Individual column filtering */
        for ($i = 0; $i < $counter; $i++) {
            if (isset($parameters['bSearchable_' . $i]) && $parameters['bSearchable_' . $i] == "true" && $parameters['sSearch_' . $i] != '') {
                if ($sWhere == "") {
                    $sWhere .= $aColumns[$i] . " LIKE '%" . ($parameters['sSearch_' . $i]) . "%' ";
                } else {
                    $sWhere .= " AND " . $aColumns[$i] . " LIKE '%" . ($parameters['sSearch_' . $i]) . "%' ";
                }
            }
        }

        /*
         * SQL queries
         * Get data to display
         */
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from('event_log');
        $sQueryStr = $sql->buildSqlString($sQuery);

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
            $sQuery = $sQuery->where(array("date_time >='" . $startDate . "'", "date_time <='" . $endDate . "'"));
        }
        if ($parameters['eventType'] != '') {
            $sQuery = $sQuery->where("event_type like '%" . $parameters['eventType'] . "%'");
        }

        if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }

        if (isset($sOrder) && $sOrder != "") {
            $sQuery->order($sOrder);
        }

        if (isset($sLimit) && isset($sOffset)) {
            $sQuery->limit($sLimit);
            $sQuery->offset($sOffset);
        }

        $sQueryStr = $sql->buildSqlString($sQuery); // Get the string of the Sql, instead of the Select-instance
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->buildSqlString($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $iTotal = $this->select()->count();

        $output = array(
            "sEcho" => (int) $parameters['sEcho'],
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        foreach ($rResult as $aRow) {
            $row = [];
            $row[] = ucwords($aRow['event_type']);
            $row[] = ucwords($aRow['action']);
            $row[] = ucwords($aRow['resource_name']);
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

        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);

        $where = [];
        if (!empty($params['startDate'])) {
            $where[] = "e.date_time >= '" . CommonService::isoDateFormat($params['startDate']) . " 00:00:00'";
        }
        if (!empty($params['endDate'])) {
            $where[] = "e.date_time <= '" . CommonService::isoDateFormat($params['endDate']) . " 23:59:59'";
        }
        if (!empty($params['eventType'])) {
            $safe = preg_replace('/[^A-Za-z0-9_\- ]/', '', (string) $params['eventType']);
            $where[] = "e.event_type = '" . $safe . "'";
        }
        if (!empty($params['actor'])) {
            $where[] = "e.actor = " . (int) $params['actor'];
        }
        if (!empty($params['search'])) {
            $term = $dbAdapter->getPlatform()->quoteValue('%' . $params['search'] . '%');
            $where[] = "(e.action LIKE $term OR e.event_type LIKE $term OR e.resource_name LIKE $term OR u.first_name LIKE $term OR u.last_name LIKE $term OR u.login LIKE $term)";
        }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countSql = "SELECT COUNT(*) AS c FROM event_log AS e LEFT JOIN users AS u ON u.id = e.actor $whereSql";
        $total = (int) ($dbAdapter->query($countSql, $dbAdapter::QUERY_MODE_EXECUTE)->current()['c'] ?? 0);

        $rowsSql = "SELECT e.event_id, e.actor, e.subject, e.event_type, e.action, e.resource_name, e.date_time,
                           u.first_name, u.last_name, u.login
                    FROM event_log AS e
                    LEFT JOIN users AS u ON u.id = e.actor
                    $whereSql
                    ORDER BY e.date_time DESC, e.event_id DESC
                    LIMIT $pageSize OFFSET $offset";
        $rows = $dbAdapter->query($rowsSql, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();

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
                'id'           => (int) $r['event_id'],
                'action'       => $r['action'] ?? '',
                'eventType'    => $r['event_type'] ?? '',
                'resourceName' => $r['resource_name'] ?? '',
                'userName'     => $name,
                'userInitials' => $initials ?: 'S',
                'userLogin'    => $r['login'] ?? '',
                'time'         => $ts ? date('g:i a', $ts) : '',
                'dateKey'      => $ts ? date('Y-m-d', $ts) : '',
                'dateLabel'    => $ts ? date('D, j M Y', $ts) : '',
                'datetime'     => $r['date_time'] ?? '',
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
        $dbAdapter = $this->adapter;
        $sql = 'SELECT DISTINCT event_type FROM event_log WHERE event_type IS NOT NULL AND event_type <> "" ORDER BY event_type';
        $rows = $dbAdapter->query($sql, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        return array_column($rows, 'event_type');
    }
}

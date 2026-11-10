<?php

namespace Application\Model;

use Application\Session\Container;
use Application\Service\CommonService;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

/** Bulk PDF download requests for V6 audits, in r_spi_form_v_6_download. */
class SpiFormVer6DownloadTable
{
    private const string TABLE = 'r_spi_form_v_6_download';

    private readonly Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function addDownloadDataDetails($params)
    {
        $loginContainer = new Container('credo');
        $username = $loginContainer->login;
        $downloadData = array(
            'user' => $loginContainer->userId,
            'auditroundno' => (isset($params['auditRndNo']) && trim($params['auditRndNo']) != '') ? $params['auditRndNo'] : null,
            'assesmentofaudit' => (isset($params['dateRange']) && trim($params['dateRange']) != '') ? $params['dateRange'] : null,
            'testingpointtype' => (isset($params['testPoint']) && trim($params['testPoint']) != '') ? $params['testPoint'] : null,
            'level' => (isset($params['level']) && trim($params['level']) != '') ? $params['level'] : null,
            'affiliation' => (isset($params['affiliation']) && trim($params['affiliation']) != '') ? $params['affiliation'] : null,
            'level_name' => '',
            'AUDIT_SCORE_PERCENTAGE' => (isset($params['scoreLevel']) && trim($params['scoreLevel']) != '') ? $params['scoreLevel'] : null
        );
        $subject = '';
        $eventType = 'Export-SPI RT Form 6-PDF';
        $action = $username . ' has exported the SPI RT Form 6 PDF';
        $resourceName = 'SPI-RT-Form-6-PDF';
        (new EventLogTable($this->db))->addEventLog($subject, $eventType, $action, $resourceName);
        $this->db->insert(self::TABLE, $downloadData);
        return (int) $this->db->lastInsertId();
    }

    /**
     * The oldest pending request and the audits that match its filters.
     * Marks the request as picked up.
     */
    public function fetchDownloadDataList()
    {
        $result = [];
        $queryResult = $this->db->fetchAssociative('SELECT * FROM ' . self::TABLE . ' WHERE download_status = 0 LIMIT 1') ?: null;
        if ($queryResult) {
            $where = ["spiv6.status != 'deleted'"];
            $values = [];
            $types = [];
            $add = function (string $condition, $value, $type = ParameterType::STRING) use (&$where, &$values, &$types) {
                $where[] = $condition;
                $values[] = $value;
                $types[] = $type;
            };
            if (isset($queryResult['auditroundno']) && $queryResult['auditroundno'] != '') {
                $add('spiv6.auditroundno = ?', $queryResult['auditroundno']);
            }
            if (isset($queryResult['assesmentofaudit']) && $queryResult['assesmentofaudit'] != '') {
                $startDate = '';
                $endDate = '';
                $dateField = explode(" ", $queryResult['assesmentofaudit']);
                if (isset($dateField[0]) && trim($dateField[0]) != "") {
                    $startDate = CommonService::isoDateFormat(trim($dateField[0]));
                }
                if (isset($dateField[2]) && trim($dateField[2]) != "") {
                    $endDate = CommonService::isoDateFormat(trim($dateField[2]));
                }
                // A range without its end date ("01-Jan-2024") bounds only the start.
                if (!empty($startDate)) {
                    $add('spiv6.assesmentofaudit >= ?', $startDate);
                }
                if (!empty($endDate)) {
                    $add('spiv6.assesmentofaudit <= ?', $endDate);
                }
            }
            if (isset($queryResult['testingpointtype']) && $queryResult['testingpointtype'] != '') {
                // An "other" audit stores "other" as its type, so it matches like any type.
                $add('spiv6.testingpointtype = ?', $queryResult['testingpointtype']);
            }
            if (isset($queryResult['level']) && $queryResult['level'] != '') {
                $add('spiv6.level = ?', $queryResult['level']);
            }
            if (isset($queryResult['affiliation']) && $queryResult['affiliation'] != '') {
                $add('spiv6.affiliation = ?', $queryResult['affiliation']);
            }

            if (isset($queryResult['AUDIT_SCORE_PERCENTAGE']) && $queryResult['AUDIT_SCORE_PERCENTAGE'] != '') {
                if ($queryResult['AUDIT_SCORE_PERCENTAGE'] == 0) {
                    $where[] = "spiv6.AUDIT_SCORE_PERCENTAGE < 40";
                } elseif ($queryResult['AUDIT_SCORE_PERCENTAGE'] == 1) {
                    $where[] = "spiv6.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv6.AUDIT_SCORE_PERCENTAGE < 60";
                } elseif ($queryResult['AUDIT_SCORE_PERCENTAGE'] == 2) {
                    $where[] = "spiv6.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv6.AUDIT_SCORE_PERCENTAGE < 80";
                } elseif ($queryResult['AUDIT_SCORE_PERCENTAGE'] == 3) {
                    $where[] = "spiv6.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv6.AUDIT_SCORE_PERCENTAGE < 90";
                } elseif ($queryResult['AUDIT_SCORE_PERCENTAGE'] == 4) {
                    $where[] = "spiv6.AUDIT_SCORE_PERCENTAGE >= 90";
                }
            }
            $result = $this->db->fetchAllAssociative(
                'SELECT spiv6.* FROM spi_form_v_6 AS spiv6 WHERE ' . implode(' AND ', $where),
                $values,
                $types
            );
            //update download status
            $this->db->update(self::TABLE, ['download_status' => 1], ['r_download_id' => $queryResult['r_download_id']]);
        }
        return array('downloadResult' => $queryResult, 'formResult' => $result);
    }

    /** Records the archive built for a request and the audits in it. */
    public function setArchive(int $requestId, string $fileName, string $auditIds): int
    {
        return $this->db->update(self::TABLE, ['file_name' => $fileName, 'audit_ids' => $auditIds], ['r_download_id' => $requestId]);
    }

    /**
     * The signed-in user's bulk download requests, newest first, with the
     * requester's login. A Super Admin sees everyone's.
     */
    public function fetchRequestsForUser(): array
    {
        $loginContainer = new Container('credo');
        $sql = 'SELECT d.*, u.login FROM ' . self::TABLE . ' AS d LEFT JOIN users AS u ON u.id = d.user';
        $values = [];
        if ($loginContainer->roleCode !== 'SA') {
            $sql .= ' WHERE d.user = ?';
            $values[] = (int) $loginContainer->userId;
        }
        return $this->db->fetchAllAssociative($sql . ' ORDER BY d.r_download_id DESC', $values);
    }

    /**
     * The archive name of one request and the audit ids in it, if
     * the signed-in user made the request or is a Super Admin. Null for
     * anyone else, so another user's request reads as missing.
     *
     * @return array{file_name: string, audit_ids: string}|null
     */
    public function fetchArchiveForUser(mixed $requestId): ?array
    {
        if (!is_scalar($requestId) || preg_match('/^\d+$/', (string) $requestId) !== 1) {
            return null;
        }
        $loginContainer = new Container('credo');
        if (empty($loginContainer->userId)) {
            return null;
        }
        $sql = 'SELECT file_name, audit_ids FROM ' . self::TABLE . ' WHERE r_download_id = ?';
        $values = [(string) $requestId];
        if ($loginContainer->roleCode !== 'SA') {
            $sql .= ' AND user = ?';
            $values[] = (int) $loginContainer->userId;
        }
        $row = $this->db->fetchAssociative($sql, $values);
        if (!$row || $row['file_name'] === null || $row['file_name'] === '') {
            return null;
        }
        return ['file_name' => (string) $row['file_name'], 'audit_ids' => (string) $row['audit_ids']];
    }
}

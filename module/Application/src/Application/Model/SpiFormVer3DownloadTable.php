<?php

namespace Application\Model;

use Application\Session\Container;
use Application\Service\CommonService;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

/** Bulk PDF download requests for V3 audits, in r_spi_form_v_3_download. */
class SpiFormVer3DownloadTable
{
    private const string TABLE = 'r_spi_form_v_3_download';

    private readonly Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function addDownloadDataDetails($params)
    {
        $loginContainer = new Container('credo');
        $username = $loginContainer->login;
        $province = null;
        if (isset($params['province']) && is_array($params['province']) && $params['province'] !== []) {
            $province = implode(',', $params['province']);
        }
        $downloadData = [
            'user' => $loginContainer->userId,
            'auditroundno' => (isset($params['auditRndNo']) && trim($params['auditRndNo']) != '') ? $params['auditRndNo'] : null,
            'assesmentofaudit' => (isset($params['dateRange']) && trim($params['dateRange']) != '') ? $params['dateRange'] : null,
            'testingpointtype' => (isset($params['testPoint']) && trim($params['testPoint']) != '') ? $params['testPoint'] : null,
            'testingpointname' => (isset($params['testPointName']) && trim($params['testPointName']) != '') ? $params['testPointName'] : null,
            'level' => (isset($params['level']) && trim($params['level']) != '') ? $params['level'] : null,
            'affiliation' => (isset($params['affiliation']) && trim($params['affiliation']) != '') ? $params['affiliation'] : null,
            'level_name' => $province,
            'AUDIT_SCORE_PERCANTAGE' => (isset($params['scoreLevel']) && trim($params['scoreLevel']) != '') ? $params['scoreLevel'] : null
        ];
        $this->db->insert(self::TABLE, $downloadData);
        $downloadId = (int) $this->db->lastInsertId();
        $subject = '';
        $eventType = 'Export-SPI RT Form 3-PDF';
        $action = $username . ' has exported the SPI RT Form 3 PDF';
        $resourceName = 'SPI-RT-Form-3-PDF';
        (new EventLogTable($this->db))->addEventLog($subject, $eventType, $action, $resourceName);
        return $downloadId;
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
            $where = ["spiv3.status != 'deleted'"];
            $values = [];
            $types = [];
            $add = function (string $condition, $value, $type = ParameterType::STRING) use (&$where, &$values, &$types) {
                $where[] = $condition;
                $values[] = $value;
                $types[] = $type;
            };
            if (!empty($queryResult['auditroundno'])) {
                $add('spiv3.auditroundno = ?', $queryResult['auditroundno']);
            }
            if (!empty($queryResult['assesmentofaudit'])) {
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
                    $add('spiv3.assesmentofaudit >= ?', $startDate);
                }
                if (!empty($endDate)) {
                    $add('spiv3.assesmentofaudit <= ?', $endDate);
                }
            }
            if (isset($queryResult['testingpointtype']) && $queryResult['testingpointtype'] != '') {
                $add('spiv3.testingpointtype = ?', $queryResult['testingpointtype']);
                if (isset($queryResult['testingpointname']) && trim($queryResult['testingpointname']) != '') {
                    if (strtolower(trim($queryResult['testingpointtype'])) != 'other') {
                        $add('spiv3.testingpointname = ?', $queryResult['testingpointname']);
                    } else {
                        $add('spiv3.testingpointtype_other = ?', $queryResult['testingpointname']);
                    }
                }
            }
            if (isset($queryResult['level']) && $queryResult['level'] != '') {
                $add('spiv3.level = ?', $queryResult['level']);
            }
            if (isset($queryResult['affiliation']) && $queryResult['affiliation'] != '') {
                $add('spiv3.affiliation = ?', $queryResult['affiliation']);
            }
            if (isset($queryResult['level_name']) && $queryResult['level_name'] != '') {
                $add('spiv3.level_name IN (?)', explode(",", $queryResult['level_name']), ArrayParameterType::STRING);
            }

            if (isset($queryResult['AUDIT_SCORE_PERCANTAGE']) && $queryResult['AUDIT_SCORE_PERCANTAGE'] != '') {
                if ($queryResult['AUDIT_SCORE_PERCANTAGE'] == 0) {
                    $where[] = "FLOOR(spiv3.AUDIT_SCORE_PERCANTAGE + 0.5) < 40";
                } elseif ($queryResult['AUDIT_SCORE_PERCANTAGE'] == 1) {
                    $where[] = "FLOOR(spiv3.AUDIT_SCORE_PERCANTAGE + 0.5) >= 40 AND FLOOR(spiv3.AUDIT_SCORE_PERCANTAGE + 0.5) < 60";
                } elseif ($queryResult['AUDIT_SCORE_PERCANTAGE'] == 2) {
                    $where[] = "FLOOR(spiv3.AUDIT_SCORE_PERCANTAGE + 0.5) >= 60 AND FLOOR(spiv3.AUDIT_SCORE_PERCANTAGE + 0.5) < 80";
                } elseif ($queryResult['AUDIT_SCORE_PERCANTAGE'] == 3) {
                    $where[] = "FLOOR(spiv3.AUDIT_SCORE_PERCANTAGE + 0.5) >= 80 AND FLOOR(spiv3.AUDIT_SCORE_PERCANTAGE + 0.5) < 90";
                } elseif ($queryResult['AUDIT_SCORE_PERCANTAGE'] == 4) {
                    $where[] = "FLOOR(spiv3.AUDIT_SCORE_PERCANTAGE + 0.5) >= 90";
                }
            }
            $result = $this->db->fetchAllAssociative(
                'SELECT spiv3.* FROM spi_form_v_3 AS spiv3 WHERE ' . implode(' AND ', $where),
                $values,
                $types
            );
            //update download status
            $this->db->update(self::TABLE, ['download_status' => 1], ['r_download_id' => $queryResult['r_download_id']]);
        }
        return array('downloadResult' => $queryResult, 'formResult' => $result);
    }

    /** The signed-in user's five newest finished requests. */
    public function fetchDownloadFilesRow()
    {
        $loginContainer = new Container('credo');
        return $this->db->fetchAllAssociative(
            'SELECT * FROM ' . self::TABLE . ' WHERE download_status = 1 AND user = ? ORDER BY r_download_id desc LIMIT 5',
            [$loginContainer->userId]
        );
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

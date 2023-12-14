<?php

namespace Application\Model;

use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Session\Container;
use Laminas\Db\Adapter\Adapter;
use Application\Model\EventLogTable;
use Application\Service\CommonService;
use Laminas\Db\TableGateway\AbstractTableGateway;


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Countries
 *
 * @author amit
 */
class SpiFormVer3DownloadTable extends AbstractTableGateway
{

    protected $table = 'r_spi_form_v_3_download';
    protected $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function addDownloadDataDetails($params)
    {
        $loginContainer = new Container('credo');
        $username = $loginContainer->login;
        $dbAdapter = $this->adapter;
        $trackTable = new EventLogTable($dbAdapter);
        $province = null;
        if (isset($params['province']) && is_array($params['province']) && $params['province'] !== []) {
            $province = implode(',', $params['province']);
        }
        $downloadData = array(
            'user' => $loginContainer->userId,
            'auditroundno' => (isset($params['auditRndNo']) && trim($params['auditRndNo']) != '') ? $params['auditRndNo'] : null,
            'assesmentofaudit' => (isset($params['dateRange']) && trim($params['dateRange']) != '') ? $params['dateRange'] : null,
            'testingpointtype' => (isset($params['testPoint']) && trim($params['testPoint']) != '') ? $params['testPoint'] : null,
            'testingpointname' => (isset($params['testPointName']) && trim($params['testPointName']) != '') ? $params['testPointName'] : null,
            'level' => (isset($params['level']) && trim($params['level']) != '') ? $params['level'] : null,
            'affiliation' => (isset($params['affiliation']) && trim($params['affiliation']) != '') ? $params['affiliation'] : null,
            'level_name' => $province,
            'AUDIT_SCORE_PERCANTAGE' => (isset($params['scoreLevel']) && trim($params['scoreLevel']) != '') ? $params['scoreLevel'] : null
        );
        $this->insert($downloadData);
        $subject = '';
        $eventType = 'Export-SPI RT Form 3-PDF';
        $action = $username . ' has exported the SPI RT Form 3 PDF';
        $resourceName = 'SPI-RT-Form-3-PDF';
        $trackTable->addEventLog($subject, $eventType, $action, $resourceName);
        return $this->lastInsertValue;
    }

    public function fetchDownloadDataList()
    {
        $result = array();
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $query = $sql->select()->from('r_spi_form_v_3_download')->where(array('download_status' => 0));
        $queryStr = $sql->buildSqlString($query);
        $queryResult = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
        if ($queryResult) {
            $sQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))
                ->where('spiv3.status != "deleted"');
            if (!empty($queryResult['auditroundno'])) {
                $sQuery = $sQuery->where("spiv3.auditroundno='" . $queryResult['auditroundno'] . "'");
            }
            if (!empty($queryResult['assesmentofaudit'])) {
                $dateField = explode(" ", $queryResult['assesmentofaudit']);
                if (isset($dateField[0]) && trim($dateField[0]) != "") {
                    $startDate = CommonService::isoDateFormat(trim($dateField[0]));
                }
                if (isset($dateField[2]) && trim($dateField[2]) != "") {
                    $endDate = CommonService::isoDateFormat(trim($dateField[2]));
                }
                $sQuery = $sQuery->where(array("spiv3.assesmentofaudit >='" . $startDate . "'", "spiv3.assesmentofaudit <='" . $endDate . "'"));
            }
            if (isset($queryResult['testingpointtype']) && $queryResult['testingpointtype'] != '') {
                $sQuery = $sQuery->where("spiv3.testingpointtype='" . $queryResult['testingpointtype'] . "'");
                if (isset($queryResult['testingpointname']) && trim($queryResult['testingpointname']) != '') {
                    if (trim($queryResult['testingpointname']) != 'other') {
                        $sQuery = $sQuery->where("spiv3.testingpointname='" . $queryResult['testingpointname'] . "'");
                    } else {
                        $sQuery = $sQuery->where("spiv3.testingpointtype_other='" . $queryResult['testingpointname'] . "'");
                    }
                }
            }
            if (isset($queryResult['level']) && $queryResult['level'] != '') {
                $sQuery = $sQuery->where("spiv3.level='" . $queryResult['level'] . "'");
            }
            if (isset($queryResult['affiliation']) && $queryResult['affiliation'] != '') {
                $sQuery = $sQuery->where("spiv3.affiliation='" . $queryResult['affiliation'] . "'");
            }
            if (isset($queryResult['level_name']) && $queryResult['level_name'] != '') {
                $provinces = explode(",", $queryResult['level_name']);
                $sQuery = $sQuery->where('spiv3.level_name IN ("' . implode('", "', $provinces) . '")');
            }

            if (isset($queryResult['AUDIT_SCORE_PERCANTAGE']) && $queryResult['AUDIT_SCORE_PERCANTAGE'] != '') {
                if ($queryResult['AUDIT_SCORE_PERCANTAGE'] == 0) {
                    $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE < 40");
                } elseif ($queryResult['AUDIT_SCORE_PERCANTAGE'] == 1) {
                    $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 40 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 59");
                } elseif ($queryResult['AUDIT_SCORE_PERCANTAGE'] == 2) {
                    $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 60 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 79");
                } elseif ($queryResult['AUDIT_SCORE_PERCANTAGE'] == 3) {
                    $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 80 AND spiv3.AUDIT_SCORE_PERCANTAGE <= 89");
                } elseif ($queryResult['AUDIT_SCORE_PERCANTAGE'] == 4) {
                    $sQuery = $sQuery->where("spiv3.AUDIT_SCORE_PERCANTAGE >= 90");
                }
            }
            $sQueryStr = $sql->buildSqlString($sQuery);
            $result = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
            //update download status
            $this->update(array('download_status' => 1), array('r_download_id' => $queryResult['r_download_id']));
        }
        return array('downloadResult' => $queryResult, 'formResult' => $result);
    }


    public function fetchDownloadFilesRow()
    {
        $loginContainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $query = $sql->select()->from('r_spi_form_v_3_download')->where(array('download_status' => 1, 'user' => $loginContainer->userId))->order('r_download_id desc')->limit(5);
        $queryStr = $sql->buildSqlString($query);
        return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    }
}

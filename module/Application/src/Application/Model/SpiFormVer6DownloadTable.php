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
class SpiFormVer6DownloadTable extends AbstractTableGateway
{

    protected $table = 'r_spi_form_v_6_download';
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
        $province = null;

        $downloadData = array(
            'user' => $loginContainer->userId,
            'auditroundno' => (isset($params['auditRndNo']) && trim($params['auditRndNo']) != '') ? $params['auditRndNo'] : null,
            'assesmentofaudit' => (isset($params['dateRange']) && trim($params['dateRange']) != '') ? $params['dateRange'] : null,
            'testingpointtype' => (isset($params['testPoint']) && trim($params['testPoint']) != '') ? $params['testPoint'] : null,
            //'testingpointname' => (isset($params['testPointName']) && trim($params['testPointName']) != '') ? $params['testPointName'] : null,
            'level' => (isset($params['level']) && trim($params['level']) != '') ? $params['level'] : null,
            'affiliation' => (isset($params['affiliation']) && trim($params['affiliation']) != '') ? $params['affiliation'] : null,
            'level_name' => '',
            'AUDIT_SCORE_PERCENTAGE' => (isset($params['scoreLevel']) && trim($params['scoreLevel']) != '') ? $params['scoreLevel'] : null
        );
        $subject = '';
        $eventType = 'Export-SPI RT Form 6-PDF';
        $action = $username . ' has exported the SPI RT Form 6 PDF';
        $resourceName = 'SPI-RT-Form-6-PDF';
        $trackTable->addEventLog($subject, $eventType, $action, $resourceName);
        // print_r($downloadData);die;
        $this->insert($downloadData);
        return $this->lastInsertValue;
    }

    public function fetchDownloadDataList()
    {
        $result = array();
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $query = $sql->select()->from('r_spi_form_v_6_download')->where(array('download_status' => 0));
        $queryStr = $sql->buildSqlString($query);
        $queryResult = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
        if ($queryResult) {
            $sQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
                ->where('spiv6.status != "deleted"');
            if (isset($queryResult->auditroundno) && $queryResult->auditroundno != '') {
                $sQuery = $sQuery->where("spiv6.auditroundno='" . $queryResult->auditroundno . "'");
            }
            if (isset($queryResult->assesmentofaudit) && $queryResult->assesmentofaudit != '') {
                $dateField = explode(" ", $queryResult->assesmentofaudit);
                if (isset($dateField[0]) && trim($dateField[0]) != "") {
                    $startDate = CommonService::isoDateFormat(trim($dateField[0]));
                }
                if (isset($dateField[2]) && trim($dateField[2]) != "") {
                    $endDate = CommonService::isoDateFormat(trim($dateField[2]));
                }
                $sQuery = $sQuery->where(array("spiv6.assesmentofaudit >='" . $startDate . "'", "spiv6.assesmentofaudit <='" . $endDate . "'"));
            }
            if (isset($queryResult->testingpointtype) && $queryResult->testingpointtype != '') {


                if (strtolower(trim($queryResult->testingpointtype)) != 'other') {
                    $sQuery = $sQuery->where("spiv6.testingpointtype='" . $queryResult->testingpointtype . "'");
                } else {
                    $sQuery = $sQuery->where("spiv6.testingpointtype_other='" . $queryResult->testingpointtype . "'");
                }
            }
            if (isset($queryResult->level) && $queryResult->level != '') {
                $sQuery = $sQuery->where("spiv6.level='" . $queryResult->level . "'");
            }
            if (isset($queryResult->affiliation) && $queryResult->affiliation != '') {
                $sQuery = $sQuery->where("spiv6.affiliation='" . $queryResult->affiliation . "'");
            }
            if (isset($queryResult->level_name) && $queryResult->level_name != '') {
                $provinces = explode(",", $queryResult->level_name);
                $sQuery = $sQuery->where('spiv6.level_name IN ("' . implode('", "', $provinces) . '")');
            }
            if (isset($queryResult->AUDIT_SCORE_PERCENTAGE) && $queryResult->AUDIT_SCORE_PERCENTAGE != '') {
                if ($queryResult->AUDIT_SCORE_PERCENTAGE == 0) {
                    $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE < 40");
                } elseif ($queryResult->AUDIT_SCORE_PERCENTAGE == 1) {
                    $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 59");
                } elseif ($queryResult->AUDIT_SCORE_PERCENTAGE == 2) {
                    $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 79");
                } elseif ($queryResult->AUDIT_SCORE_PERCENTAGE == 3) {
                    $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 89");
                } elseif ($queryResult->AUDIT_SCORE_PERCENTAGE == 4) {
                    $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 90");
                }
            }
            $sQueryStr = $sql->buildSqlString($sQuery);
            $result = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
            //update download status
            $this->update(array('download_status' => 1), array('r_download_id' => $queryResult->r_download_id));
        }
        return array('downloadResult' => $queryResult, 'formResult' => $result);
    }

    public function fetchDownloadFilesRow()
    {
        $loginContainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $query = $sql->select()->from('r_spi_form_v_6_download')->where(array('download_status' => 1, 'user' => $loginContainer->userId))->order('r_download_id desc')->limit(5);
        $queryStr = $sql->buildSqlString($query);
        return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    }
}

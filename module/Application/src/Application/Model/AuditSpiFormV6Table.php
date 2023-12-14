<?php

namespace Application\Model;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Sql\Sql;

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
class AuditSpiFormV6Table extends AbstractTableGateway
{

    protected $table = 'audit_spi_form_v_6';
    protected $adapter;
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function fetchAllDetails($parameters)
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $spiV6Db = new SpiFormVer6Table($this->adapter);

        $columnsSql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE  table_name = 'audit_spi_form_v_6' order by ordinal_position";
        $response['auditColumns'] = $dbAdapter->query($columnsSql, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();

        $columnsSql1 = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE  table_name = 'spi_form_v_6' order by ordinal_position";
        $response['spiColumns'] = $dbAdapter->query($columnsSql1, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();

        $metaInstance = trim($parameters['metaInstance']);
        $response['currentRecord'] = $spiV6Db->fetchV6DetailsByMetaInstanceId($metaInstance);
        if (isset($metaInstance) && $metaInstance != '') {
            $sQuery = $sql->select()->from(array('a' => 'audit_spi_form_v_6'))
                ->where("`meta-instance-id` = '$metaInstance'");
            $sQueryStr = $sql->buildSqlString($sQuery);
            $response['auditInfo'] = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        } else {
            $response["status"] = "fail";
            $response["message"] = "Please select valid Sample Code!";
        }
        return $response;
    }

    public function updateInitialAuditMailStatus($id)
    {
        return $this->update(array('status' => 'not-sent'), array('mail_id' => $id));
    }

    public function updateAuditMailStatus($id)
    {
        return $this->update(array('status' => 'sent'), array('mail_id' => $id));
    }
}

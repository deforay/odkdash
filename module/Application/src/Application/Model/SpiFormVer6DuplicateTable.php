<?php

namespace Application\Model;

use Laminas\Session\Container;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Application\Model\SpiRtFacilitiesTable;
use Application\Model\GlobalTable;

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
class SpiFormVer6DuplicateTable extends AbstractTableGateway
{

    protected $table = 'spi_form_v_6_duplicate';
    protected $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //remove audit data
    public function removeAuditData($params)
    {
        $result = false;
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $dResult = $dbAdapter->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'spi_form_v_6_duplicate'", $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        if (count($dResult) > 0) {
            $sQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
                ->where('spiv6.id = "' . $params['id'] . '"');
            $sQueryStr = $sql->buildSqlString($sQuery);
            $aResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
            foreach ($dResult as $dData) {
                $data[$dData['COLUMN_NAME']] = $aResult[$dData['COLUMN_NAME']];
            }

            $sql = new Sql($this->adapter);
            $insert = $sql->insert('spi_form_v_6_duplicate');
            $dbAdapter = $this->adapter;
            $result = $insert->values($data);
            $selectString = $sql->buildSqlString($insert);
            $results = $dbAdapter->query($selectString, $dbAdapter::QUERY_MODE_EXECUTE);
            if ($result) {
                $spiver3table = new \Application\Model\SpiFormVer3Table($dbAdapter);
                $spiver3table->delete(array('id' => $params['id']));
                return $params['id'];
            }
        }
        return $result;
    }
}

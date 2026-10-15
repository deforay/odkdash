<?php

namespace Application\Model;

use Application\Session\Container;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Application\Model\SpiRtFacilitiesTable;
use Application\Model\GlobalTable;



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
        // One audit at a time: an array would select one row to archive but
        // delete every id in it.
        $id = $params['id'] ?? null;
        if (!is_scalar($id) || trim((string) $id) === '') {
            return false;
        }
        $dbAdapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $dResult = $dbAdapter->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'spi_form_v_6_duplicate'", $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        if (count($dResult) > 0) {
            $sQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
                ->where(['spiv6.id' => $id]);
            $sQueryStr = $sql->buildSqlString($sQuery);
            $aResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
            if (!$aResult) {
                return false;
            }
            $data = [];
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
                $auditTable = new \Application\Model\SpiFormVer6Table($dbAdapter);
                $auditTable->delete(['id' => $id]);
                return $id;
            }
        }
        return $result;
    }
}

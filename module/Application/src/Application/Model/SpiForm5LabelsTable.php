<?php

namespace Application\Model;

use Laminas\Session\Container;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
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
class SpiForm5LabelsTable extends AbstractTableGateway {

    protected $table = 'spi_v5_form_labels';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
      
    public function getAllLabels(){
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from(array('spiv3' => 'spi_v5_form_labels'));
        $sQueryStr = $sql->buildSqlString($sQuery);
        //echo $sQueryStr;//die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        $response = array();
        foreach($rResult as $row){
            $response[$row['field']] = array($row['short_label'],$row['label']);
        }
        return $response;
    }
    
}

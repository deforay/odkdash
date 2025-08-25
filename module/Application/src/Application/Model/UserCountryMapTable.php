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
class UserCountryMapTable extends AbstractTableGateway
{

    protected $table = 'user_country_map';
    protected $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function fetchSelectedCountry($id)
    {
        return $this->select(array('user_id' => $id))->toArray();
    }
}

<?php

namespace Application\Model;

use App\Db\Dbal;
use Doctrine\DBAL\Connection;
use Laminas\Db\Adapter\Adapter;

/** The V3 form's field labels. */
class SpiFormLabelsTable
{
    private readonly Connection $db;

    public function __construct(Adapter $adapter)
    {
        $this->db = Dbal::connection($adapter);
    }

    public function getAllLabels()
    {
        $response = [];
        foreach ($this->db->fetchAllAssociative('SELECT * FROM spi_v3_form_labels') as $row) {
            $response[$row['field']] = array($row['short_label'], $row['label']);
        }

        return $response;
    }
}

<?php

namespace Application\Model;

use Doctrine\DBAL\Connection;

/** The V3 form's field labels. */
class SpiFormLabelsTable
{
    private readonly Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
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

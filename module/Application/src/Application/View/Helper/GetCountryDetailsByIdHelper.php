<?php

namespace Application\View\Helper;

use Laminas\View\Helper\AbstractHelper;


class GetCountryDetailsByIdHelper extends AbstractHelper
{

    private $countriesTable;
    public function __construct($countriesTable)
    {
        $this->countriesTable = $countriesTable;
    }

    public function __invoke()
    {
        return $this->countriesTable->fetchMapedCountries();
    }
}

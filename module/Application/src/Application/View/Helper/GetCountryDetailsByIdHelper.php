<?php

namespace Application\View\Helper;



class GetCountryDetailsByIdHelper
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

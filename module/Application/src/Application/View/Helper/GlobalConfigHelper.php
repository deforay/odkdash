<?php

namespace Application\View\Helper;



class GlobalConfigHelper
{

    private $globalTable;
    public function __construct($globalTable)
    {
        $this->globalTable = $globalTable;
    }

    public function __invoke()
    {
        return $this->globalTable->getGlobalConfig();
    }
}

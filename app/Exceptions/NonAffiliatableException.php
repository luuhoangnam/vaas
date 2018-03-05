<?php

namespace App\Exceptions;

class NonAffiliatableException extends \Exception
{
    protected $asin;

    public function __construct($asin)
    {
        $this->asin = $asin;
    }

    public function getAsin()
    {
        return $this->asin;
    }
}
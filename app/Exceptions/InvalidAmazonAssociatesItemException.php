<?php

namespace App\Exceptions;

class InvalidAmazonAssociatesItemException extends \Exception
{
    protected $asin;

    protected $previous;

    public function __construct($asin, \Throwable $previous = null)
    {
        $this->asin     = $asin;
        $this->previous = $previous;
    }

    public function getAsin()
    {
        return $this->asin;
    }
}
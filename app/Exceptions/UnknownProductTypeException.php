<?php

namespace App\Exceptions;

class UnknownProductTypeException extends \Exception
{
    protected $productType;

    public function __construct($productType)
    {
        $this->productType = $productType;
    }

    public function getProductType()
    {
        return $this->productType;
    }
}
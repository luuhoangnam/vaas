<?php

namespace App\Exceptions;

use App\Sourcing\SourceProduct;

class CanNotFetchProductInformation extends \Exception
{
    protected $product;

    public function __construct(SourceProduct $product)
    {
        $this->product = $product;
    }

    public function getProduct(): SourceProduct
    {
        return $this->product;
    }
}
<?php

namespace App\Exceptions;

use App\Sourcing\SourceProductInterface;

class CanNotFetchProductInformation extends \Exception
{
    protected $product;

    public function __construct(SourceProductInterface $product)
    {
        $this->product = $product;
    }

    public function getProduct(): SourceProductInterface
    {
        return $this->product;
    }
}
<?php

namespace App\Sourcing;

class AmazonCom extends Amazon
{
    protected function getProductUrl(): string
    {
        return "https://www.amazon.com/dp/{$this->productId}";
    }
}
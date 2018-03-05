<?php

namespace App\Cashback;

interface CashbackProgram
{
    /**
     * @param string $productID
     *
     * @return string|null
     */
    public function link($productID);
}
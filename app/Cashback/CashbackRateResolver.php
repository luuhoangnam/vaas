<?php

namespace App\Cashback;

interface CashbackRateResolver
{
    public function resolve($categoryID);
}
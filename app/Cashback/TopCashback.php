<?php

namespace App\Cashback;

class TopCashback implements CashbackProgram
{
    public function link($productID): string
    {
        return 'https://www.topcashback.com/amazon/';
    }
}
<?php

namespace App\Cashback;

class BeFrugal implements CashbackProgram
{
    public function link($productID): string
    {
        return 'https://www.befrugal.com/store/amazon/';
    }
}
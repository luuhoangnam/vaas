<?php

namespace App\eBay;

use LuuHoangNam\Support\Enum;

class ItemCondition extends Enum
{
    const NEW = '1000';
    const NEW_OTHER = '1500';
    const NEW_WITH_DEFECTS = '1750';
    const MANUFACTURER_REFURBISHED = '2000';
    const SELLER_REFURBISHED = '2500';
    const USED = '3000';
    const VERY_GOOD = '4000';
    const GOOD = '5000';
    const ACCEPTABLE = '6000';
    const FOR_PARTS_OR_NOT_WORKING = '7000';
}
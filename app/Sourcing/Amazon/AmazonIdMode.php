<?php

namespace App\Sourcing\Amazon;

use LuuHoangNam\Support\Enum;

class AmazonIdMode extends Enum
{
    const ASIN = 'ASIN';
    const SKU = 'SKU';
    const UPC = 'UPC';
    const EAN = 'EAN';
    const ISBN = 'ISBN';
}
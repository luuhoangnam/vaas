<?php

namespace App\Listeners\Miner;

use LuuHoangNam\Support\Enum;

class PerformancePeriod extends Enum
{
    const LAST_7_DAYS = 7;
    const LAST_14_DAYS = 14;
    const LAST_21_DAYS = 21;
    const LAST_30_DAYS = 30;
}
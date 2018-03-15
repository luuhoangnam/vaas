<?php

namespace App\Reporting;

class DailyReports
{
    protected $orderReports;

    public function __construct(OrderReports $orderReports)
    {
        $this->orderReports = $orderReports;
    }

    public function revenue()
    {

    }
}

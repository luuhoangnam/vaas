<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class DateRange extends Collection
{
    public function toDateStringCollection()
    {
        return $this->map(function (Carbon $carbon) {
            return $carbon->toDateString();
        });
    }

    public function toDateTimeStringCollection()
    {
        return $this->map(function (Carbon $carbon) {
            return $carbon->toDateTimeString();
        });
    }
}
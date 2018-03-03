<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class DateRange extends Collection
{
    public function toDateString()
    {
        return $this->map(function (Carbon $carbon) {
            return $carbon->toDateString();
        });
    }

    public function toDateTimeString()
    {
        return $this->map(function (Carbon $carbon) {
            return $carbon->toDateTimeString();
        });
    }
}
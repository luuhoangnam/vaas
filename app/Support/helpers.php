<?php

use App\Support\DateRange;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

if ( ! function_exists('app_carbon')) {
    /**
     * Make Carbon instance and set timezone for it based on current app.timezone config
     *
     * @param \DateTime|string|null     $time
     * @param \DateTimeZone|string|null $tz
     *
     * @return Carbon
     */
    function app_carbon($time = null, $tz = null)
    {
        if ($time instanceof \DateTime) {
            $carbon = Carbon::instance($time);
        } else {
            $carbon = new Carbon($time, $tz);
        }

        return $carbon->timezone(config('app.timezone'));
    }
}

if ( ! function_exists('dt')) {
    /**
     * Make \DateTime instance from string or \Carbon\Carbon instance
     *
     * @param string|Carbon|null       $time
     *
     * @param DateTimeZone|string|null $tz
     *
     * @return DateTime
     */
    function dt($time = null, $tz = null): \DateTime
    {
        if (is_string($time)) {
            $time = new Carbon($time);
        }

        $time->setTimezone($tz);

        return new DateTime($time, $time->timezone);
    }
}

if ( ! function_exists('date_range')) {
    /**
     * @param \Carbon\Carbon $from
     * @param \Carbon\Carbon $until
     *
     * @return DateRange
     */
    function date_range(\Carbon\Carbon $from, \Carbon\Carbon $until = null): DateRange
    {
        $from  = $from->startOfDay();
        $until = $until ?: Carbon::today();

        $dates = new DateRange;

        $current = clone $from;

        while ($current->lessThanOrEqualTo($until)) {
            $dates->push(clone $current);

            $current->addDay();
        }

        return $dates;
    }
}

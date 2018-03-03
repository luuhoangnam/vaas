<?php

use Illuminate\Support\Carbon;

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
     * @param string|Carbon|null $time
     *
     * @return DateTime
     */
    function dt($time = null): \DateTime
    {
        if (is_string($time)) {
            $time = new Carbon($time);
        }

        return new DateTime($time);
    }
}

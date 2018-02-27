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

<?php

use Illuminate\Support\Carbon;

if ( ! function_exists('app_carbon')) {
    /**
     * Make Carbon instance and set timezone for it based on current app.timezone config
     *
     * @param string|null               $time
     * @param \DateTimeZone|string|null $tz
     *
     * @return Carbon
     */
    function app_carbon($time = null, $tz = null)
    {
        return (new Carbon($time, $tz))->timezone(config('app.timezone'));
    }
}

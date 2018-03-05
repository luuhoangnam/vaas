<?php

use App\Support\DateRange;
use App\Support\Retention;
use Illuminate\Support\Carbon;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;

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
     * @param string         $retention
     *
     * @return DateRange
     */
    function date_range(\Carbon\Carbon $from, \Carbon\Carbon $until = null, $retention = Retention::DAILY): DateRange
    {
        $from  = $from->startOfDay();
        $until = $until ?: Carbon::today();

        $dates = new DateRange;

        $current = clone $from;

        while ($current->lessThanOrEqualTo($until)) {
            $dates->push(clone $current);

            switch ($retention) {
                case Retention::HOURLY:
                    $current->addHour();
                    break;
                case Retention::DAILY:
                    $current->addDay();
                    break;
                case Retention::WEEKLY:
                    $current->addWeek();
                    break;
                case Retention::MONTHLY:
                    $current->addMonth();
                    break;
                case Retention::YEARLY:
                    $current->addYear();
                    break;
                default:
                    $current->addDay();
                    break;
            }
        }

        return $dates;
    }
}

if ( ! function_exists('usd')) {
    /**
     * @param int|string|double|float $amount
     *
     * @return string
     */
    function usd($amount)
    {
        return money_format('$%.2n' ,$amount);
    }
}



<?php

use App\Support\DateRange;
use App\Support\eBay;
use App\Support\Retention;
use DTS\eBaySDK\Finding\Types\SellerInfo;
use DTS\eBaySDK\Shopping\Enums\SeverityCodeType as ShoppingSeverityCodeType;
use DTS\eBaySDK\Shopping\Types\SimpleUserType;
use DTS\eBaySDK\Trading\Enums\SeverityCodeType as TradingSeverityCodeType;
use DTS\eBaySDK\Trading\Types\UserType;
use DTS\eBaySDK\Types\RepeatableType;
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
        return money_format('$%.2n', $amount);
    }
}

if ( ! function_exists('severity_code')) {
    /**
     * Transform severity code ebay to class name for bootstrap
     *
     * @param string $code
     *
     * @return null|string
     */
    function severity_code_to_class($code)
    {
        switch ($code) {
            case ShoppingSeverityCodeType::C_ERROR:
            case TradingSeverityCodeType::C_ERROR:
                return 'danger';
            case ShoppingSeverityCodeType::C_WARNING:
            case TradingSeverityCodeType::C_WARNING:
                return 'warning';
        }

        return null;
    }
}

if ( ! function_exists('is_highest')) {
    /**
     * @param Iterator|array $items
     * @param string         $field
     * @param mixed          $value
     *
     * @return bool
     */
    function is_highest(Iterator $items, $field, $value): bool
    {
        $values = array_pluck($items, $field);

        return max($values) == $value;
    }
}

if ( ! function_exists('is_lowest')) {
    /**
     * @param Iterator|array $items
     * @param string         $field
     * @param mixed          $value
     *
     * @return bool
     */
    function is_lowest(Iterator $items, $field, $value): bool
    {
        $values = array_pluck($items, $field);

        return min($values) == $value;
    }
}

if ( ! function_exists('seller_url')) {
    /**
     * Transform seller into user url page on eBay
     *
     * @param UserType|SellerInfo|SimpleUserType|string $seller
     *
     * @return string
     */
    function seller_url($seller): string
    {
        return eBay::sellerUrl($seller);
    }
}

if ( ! function_exists('item_url')) {
    /**
     * Return eBay full item URL
     *
     * @param mixed $item
     *
     * @return string
     */
    function item_url($item): string
    {
        return eBay::itemUrl($item);
    }
}
<?php

use App\Support\eBay;
use DTS\eBaySDK\Finding\Types\ItemFilter;
use DTS\eBaySDK\Finding\Types\SellerInfo;
use DTS\eBaySDK\Shopping\Enums\SeverityCodeType as ShoppingSeverityCodeType;
use DTS\eBaySDK\Shopping\Types\SimpleUserType;
use DTS\eBaySDK\Trading\Enums\SeverityCodeType as TradingSeverityCodeType;
use DTS\eBaySDK\Trading\Types\UserType;

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

if ( ! function_exists('is_assoc')) {
    /**
     * Check array is assoc or not
     *
     * @param array $array
     *
     * @return bool
     */
    function is_assoc($array)
    {
        $keys = array_keys($array);

        // If the array keys of the keys match the keys, then the array must
        // not be associative (e.g. the keys array looked like {0:0, 1:1...}).
        return array_keys($keys) !== $keys;
    }
}

if ( ! function_exists('item_filter')) {
    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return ItemFilter
     */
    function item_filter($name, $value): ItemFilter
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = (string)$v;
            }
        } else {
            $value = [(string)$value];
        }

        return new ItemFilter(compact('name', 'value'));
    }
}
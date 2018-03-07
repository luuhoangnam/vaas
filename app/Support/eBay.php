<?php

namespace App\Support;

use DTS\eBaySDK\Finding\Types\SellerInfo;
use DTS\eBaySDK\Shopping\Types\SimpleItemType;
use DTS\eBaySDK\Shopping\Types\SimpleUserType;
use DTS\eBaySDK\Trading\Types\ItemType;
use DTS\eBaySDK\Trading\Types\UserType;

class eBay
{
    public static function sellerUrl($seller): string
    {
        if ($seller instanceof UserType || $seller instanceof SellerInfo || $seller instanceof SimpleUserType) {
            return static::sellerUrl($seller->UserID);
        }

        return "https://www.ebay.com/usr/{$seller}";
    }

    public static function itemUrl($item): string
    {
        if ($item instanceof ItemType || $item instanceof SimpleItemType) {
            static::itemUrl($item->ItemID);
        }

        return "https://www.ebay.com/itm/{$item}";
    }
}
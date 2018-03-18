<?php

namespace App\Events\PlatformNotifications;

use DTS\eBaySDK\Trading\Types\GetItemTransactionsResponseType;

class FixedPriceTransaction extends PlatformNotificationEvent
{
    protected function getPayloadClass(): string
    {
        return GetItemTransactionsResponseType::class;
    }
}

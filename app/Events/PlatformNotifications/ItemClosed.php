<?php

namespace App\Events\PlatformNotifications;

use DTS\eBaySDK\Trading\Types\GetItemResponseType;

class ItemClosed extends PlatformNotificationEvent
{
    protected function getPayloadClass(): string
    {
        return GetItemResponseType::class;
    }
}

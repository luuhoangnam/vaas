<?php

namespace App\Events\PlatformNotifications;

use DTS\eBaySDK\Trading\Types\GetItemResponseType;

class ItemRevised extends PlatformNotificationEvent
{
    protected function getPayloadClass(): string
    {
        return GetItemResponseType::class;
    }
}

<?php

namespace App\Listeners;

use App\Account;
use App\Events\PlatformNotifications\PlatformNotificationEvent;
use DTS\eBaySDK\Trading\Types\GetItemResponseType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SyncItem implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param  PlatformNotificationEvent $event
     *
     * @return void
     */
    public function handle($event)
    {
        /** @var GetItemResponseType $payload */
        $payload = $event->getPayload();

        Account::find($payload->RecipientUserID)->updateOrCreateItem($payload->Item);
    }
}

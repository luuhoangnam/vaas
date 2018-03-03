<?php

namespace App\Listeners;

use App\Events\PlatformNotifications\FixedPriceTransaction;
use App\Item;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RefillItemQuantity implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(FixedPriceTransaction $event): void
    {
        $ignoreAccounts = ['goodie.depot'];

        if (in_array($event->payload->RecipientUserID, $ignoreAccounts)) {
            return;
        }

        $item = Item::find($event->payload->Item->ItemID);

        $item->refillQuantity(1);
    }
}

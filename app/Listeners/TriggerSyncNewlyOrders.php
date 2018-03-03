<?php

namespace App\Listeners;

use App\Account;
use App\Events\PlatformNotifications\FixedPriceTransaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Carbon;

class TriggerSyncNewlyOrders implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(FixedPriceTransaction $event): void
    {
        Account::find($event->payload->RecipientUserID)
               ->syncOrdersByTimeRange(
                   Carbon::now()->subMinutes(15),
                   Carbon::now()
               );
    }
}

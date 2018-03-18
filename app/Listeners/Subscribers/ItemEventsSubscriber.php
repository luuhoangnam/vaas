<?php

namespace App\Listeners\Subscribers;

use App\Account;
use App\Events\PlatformNotifications\FixedPriceTransaction;
use App\Events\PlatformNotifications\ItemClosed;
use App\Events\PlatformNotifications\ItemListed;
use App\Events\PlatformNotifications\ItemRevised;
use DTS\eBaySDK\Trading\Types\GetItemResponseType;
use DTS\eBaySDK\Trading\Types\GetItemTransactionsResponseType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\InteractsWithQueue;

class ItemEventsSubscriber implements ShouldQueue
{
    use InteractsWithQueue;

    public function subscribe(Dispatcher $events)
    {
        $events->listen(
            ItemListed::class,
            [$this, 'sync']
        );

        $events->listen(
            ItemRevised::class,
            [$this, 'sync']
        );

        $events->listen(
            ItemClosed::class,
            [$this, 'sync']
        );

        $events->listen(
            FixedPriceTransaction::class,
            [$this, 'sync']
        );
    }

    public function sync($event)
    {
        /** @var GetItemTransactionsResponseType|GetItemResponseType $payload */
        /** @var ItemListed|ItemRevised|ItemClosed|FixedPriceTransaction $event */
        $payload = $event->getPayload();

        Account::find($payload->RecipientUserID)->updateOrCreateItem($payload->Item);
    }
}
<?php

namespace App\Listeners;

use App\Account;
use App\Events\PlatformNotifications\FixedPriceTransaction;
use App\Events\PlatformNotifications\ItemClosed;
use App\Events\PlatformNotifications\ItemListed;
use App\Events\PlatformNotifications\ItemRevised;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\InteractsWithQueue;

class ItemEventSubscriber implements ShouldQueue
{
    use InteractsWithQueue;

    public function fixedPriceTransaction(FixedPriceTransaction $event): void
    {
        Account::find($event->payload->RecipientUserID)->updateOrCreateItem($event->payload->Item);
    }

    public function listed(ItemListed $event): void
    {
        Account::find($event->payload->RecipientUserID)->updateOrCreateItem($event->payload->Item);
    }

    public function revised(ItemRevised $event): void
    {
        Account::find($event->payload->RecipientUserID)->updateOrCreateItem($event->payload->Item);
    }

    public function closed(ItemClosed $event): void
    {
        Account::find($event->payload->RecipientUserID)->updateOrCreateItem($event->payload->Item);
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            ItemListed::class,
            [$this, 'listed']
        );

        $events->listen(
            ItemRevised::class,
            [$this, 'revised']
        );

        $events->listen(
            ItemClosed::class,
            [$this, 'closed']
        );

        $events->listen(
            FixedPriceTransaction::class,
            [$this, 'fixedPriceTransaction']
        );
    }
}

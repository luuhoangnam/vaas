<?php

namespace App\Listeners;

use App\Account;
use App\Events\PlatformNotifications\FixedPriceTransaction;
use App\Events\PlatformNotifications\ItemClosed;
use App\Events\PlatformNotifications\ItemListed;
use App\Events\PlatformNotifications\ItemRevised;
use App\Item;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\InteractsWithQueue;

class ItemEventSubscriber implements ShouldQueue
{
    use InteractsWithQueue;

    public function fixedPriceTransaction(FixedPriceTransaction $event): void
    {
        $this->syncDownItemWithoutUpdateStartTime($event);
    }

    public function listed(ItemListed $event): void
    {
        $this->syncDownItem($event);
    }

    public function revised(ItemRevised $event): void
    {
        $this->syncDownItemWithoutUpdateStartTime($event);
    }

    public function closed(ItemClosed $event): void
    {
        $this->syncDownItemWithoutUpdateStartTime($event);
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

    protected function syncDownItem($event, $only = [], $except = []): Item
    {
        return Account::find($event->payload->RecipientUserID)
                      ->updateOrCreateItem($event->payload->Item, $only, $except);
    }

    protected function syncDownItemWithoutUpdateStartTime($event): Item
    {
        return $this->syncDownItem($event, ['start_time']);
    }
}

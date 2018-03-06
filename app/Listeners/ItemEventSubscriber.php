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
        $this->syncDownItem($event);
    }

    public function listed(ItemListed $event): void
    {
        $this->syncDownItem($event);
    }

    public function revised(ItemRevised $event): void
    {
        $this->syncDownItem($event);
    }

    public function closed(ItemClosed $event): void
    {
        $this->syncDownItem($event);
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

    /**
     * @param FixedPriceTransaction|ItemListed|ItemRevised|ItemClosed $event
     *
     * @return Item
     */
    protected function syncDownItem($event): Item
    {
        return Account::find($event->payload->RecipientUserID)->updateOrCreateItem($event->payload->Item);
    }
}

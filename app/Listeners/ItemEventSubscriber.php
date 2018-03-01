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

    public function newOrder(FixedPriceTransaction $event): void
    {
        $responsePayload = $event->payload;

        $account = Account::find($responsePayload->RecipientUserID);

        // Update Quantity on eBay
//        $request = $account->reviseItemRequest();
//
//        $request->Item           = new ItemType;
//        $request->Item->Quantity = $responsePayload->Item->Quantity++; // Plus 1
//
//        $response = $account->trading()->reviseItem($request);
//
//        if ($response->Ack !== 'Success') {
//            throw new TradingApiException($request, $response);
//        }
    }

    public function listed(ItemListed $event): void
    {
        $itemPayload     = $event->payload->Item;
        $responsePayload = $event->payload;

        $account = Account::find($responsePayload->RecipientUserID);

        $attributes = Item::extractItemAttributes($itemPayload);

        $account->saveItem(
            array_only($attributes, [
                'item_id',
                'title',
                'price',
                'quantity',
                'quantity_sold',
                'primary_category_id',
                'start_time',
                'status',
            ])
        );
    }

    public function revised(ItemRevised $event): void
    {
        $this->updateItem($event);
    }

    public function closed(ItemClosed $event): void
    {
        $this->updateItem($event);
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
            [$this, 'newOrder']
        );
    }

    protected function updateItem($event): void
    {
        /** @var ItemRevised|ItemClosed $event */
        $itemPayload = $event->payload->Item;

        $attributes = Item::extractItemAttributes($itemPayload);

        Item::find($itemPayload->ItemID)->update(
            array_only($attributes, [
                'title',
                'price',
                'quantity',
                'quantity_sold',
                'primary_category_id',
                'start_time',
                'status',
            ])
        );
    }
}

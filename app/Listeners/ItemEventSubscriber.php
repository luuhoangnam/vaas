<?php

namespace App\Listeners;

use App\Account;
use App\Events\PlatformNotifications\ItemClosed;
use App\Events\PlatformNotifications\ItemListed;
use App\Events\PlatformNotifications\ItemRevised;
use App\Item;
use DTS\eBaySDK\Trading\Types\ItemType;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ItemEventSubscriber implements ShouldQueue
{
    use InteractsWithQueue;

    public function listed(ItemListed $event): void
    {
        $itemPayload     = $event->payload->Item;
        $responsePayload = $event->payload;

        $account = Account::find($responsePayload->RecipientUserID);

        $attributes = $this->extractItemAttributes($itemPayload);

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
    }

    protected function extractItemAttributes(ItemType $item): array
    {
        return [
            'item_id'             => $item->ItemID,
            'price'               => $item->SellingStatus->CurrentPrice->value,
            'quantity'            => $item->Quantity,
            'quantity_sold'       => $item->SellingStatus->QuantitySold,
            'primary_category_id' => $item->PrimaryCategory->CategoryID,
            'start_time'          => app_carbon($item->ListingDetails->StartTime),
            'status'              => $item->SellingStatus->ListingStatus,
        ];
    }

    protected function updateItem($event): void
    {
        /** @var ItemRevised|ItemClosed $event */
        $itemPayload = $event->payload->Item;

        $attributes = $this->extractItemAttributes($itemPayload);

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

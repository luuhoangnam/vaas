<?php

namespace App\Listeners;

use App\Account;
use App\Events\PlatformNotifications\FixedPriceTransaction;
use App\Exceptions\TradingApiException;
use Carbon\Carbon;
use DTS\eBaySDK\Trading\Enums\AckCodeType;
use DTS\eBaySDK\Trading\Types\ItemType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RefillItemQuantity implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'quantity_manager';

    public function handle(FixedPriceTransaction $event)
    {
        if ($this->ignoreAccount($event->payload->RecipientUserID)) {
            return;
        }

        $displayQuantity = config('ebay.quantityManager.autoRefillQuantity', 1);

        $this->reviseItem(
            Account::find($event->payload->RecipientUserID),
            $event->payload->Item,
            $displayQuantity
        );
    }

    protected function ignoreAccount($username): bool
    {
        return in_array($username, config('ebay.quantityManager.ignore', []));
    }

    protected function reviseItem(Account $account, ItemType $item, $displayQuantity = 1): void
    {
        $request = $account->reviseItemRequest();

        $request->Item = new ItemType;

        $request->Item->ItemID   = $item->ItemID;
        $request->Item->Quantity = $displayQuantity;

        $response = $account->trading()->reviseItem($request);

        if ($response->Ack === AckCodeType::C_FAILURE) {
            $this->release(now()->addMinutes(15));

            // Send notification if needed

            throw new TradingApiException($request, $response);
        }
    }
}

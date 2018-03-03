<?php

namespace App\Listeners;

use App\Account;
use App\Events\PlatformNotifications\FixedPriceTransaction;
use App\Exceptions\TradingApiException;
use App\Item;
use DTS\eBaySDK\Trading\Enums\AckCodeType;
use DTS\eBaySDK\Trading\Types\ItemType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Queue\InteractsWithQueue;

class RefillItemQuantity implements ShouldQueue
{
    use InteractsWithQueue;

    protected $ignore = [
        'goodie.depot',
    ];

    public function handle(FixedPriceTransaction $event)
    {
        if ($this->ignoreAccount($event->payload->RecipientUserID)) {
            return;
        }

        $this->reviseItem(
            Account::find($event->payload->RecipientUserID),
            $event->payload->Item
        );
    }

    protected function ignoreAccount($username)
    {
        return in_array($username, $this->ignore);
    }

    protected function reviseItem(Account $account, ItemType $item, $displayQuantity = 1): void
    {
        $request = $account->reviseItemRequest();

        $request->Item = new ItemType;

        $request->Item->ItemID   = $item->ItemID;
        $request->Item->Quantity = $displayQuantity;

        $response = $account->trading()->reviseItem($request);

        if ($response->Ack === AckCodeType::C_FAILURE) {
            throw new TradingApiException($request, $response);
        }
    }
}

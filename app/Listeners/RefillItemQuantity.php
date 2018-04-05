<?php

namespace App\Listeners;

use App\Account;
use App\Events\PlatformNotifications\FixedPriceTransaction;
use App\Exceptions\TradingApiException;
use DTS\eBaySDK\Trading\Enums\AckCodeType;
use DTS\eBaySDK\Trading\Types\GetItemTransactionsResponseType;
use DTS\eBaySDK\Trading\Types\ItemType;
use DTS\eBaySDK\Trading\Types\ReviseItemRequestType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RefillItemQuantity implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * @param FixedPriceTransaction $event
     *
     * @throws TradingApiException
     */
    public function handle(FixedPriceTransaction $event)
    {
        /** @var GetItemTransactionsResponseType $payload */
        $payload = $event->getPayload();

        if ($this->ignoreAccount($payload->RecipientUserID)) {
            return;
        }

        $displayQuantity = config('ebay.quantity_manager.refill_quantity', 1);

        $this->reviseItem(
            Account::find($payload->RecipientUserID),
            $payload->Item,
            $displayQuantity
        );
    }

    protected function ignoreAccount($username): bool
    {
        return in_array($username, config('ebay.quantity_manager.ignore', []));
    }

    /**
     * @param Account  $account
     * @param ItemType $item
     * @param int      $displayQuantity
     *
     * @throws TradingApiException
     */
    protected function reviseItem(Account $account, ItemType $item, $displayQuantity = 1): void
    {
        $request = new ReviseItemRequestType;

        $request->Item = new ItemType;

        $request->Item->ItemID = $item->ItemID;
        $request->Item->Quantity = $displayQuantity;

        $response = $account->trading()->reviseItem($request);

        if ($response->Ack === AckCodeType::C_FAILURE) {
            $this->release(now()->addMinutes(2 ^ $this->attempts()));

            // Send notification if needed

            throw new TradingApiException($request, $response);
        }
    }
}

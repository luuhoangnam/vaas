<?php

namespace App\Jobs;

use App\Account;
use App\Exceptions\TradingApiException;
use App\Item;
use DTS\eBaySDK\Trading\Enums\AckCodeType;
use DTS\eBaySDK\Trading\Enums\CurrencyCodeType;
use DTS\eBaySDK\Trading\Types\AmountType;
use DTS\eBaySDK\Trading\Types\ReviseItemRequestType;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ReviseItemPrice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Item
     */
    protected $item;

    protected $newPrice;

    /**
     * Create a new job instance.
     *
     * @param Item $item
     * @param      $newPrice
     */
    public function __construct(Item $item, $newPrice)
    {
        $this->item     = $item;
        $this->newPrice = $newPrice;
    }

    public function handle()
    {
        $request = new ReviseItemRequestType;

        $request->Item = $this->item->itemType();

        $request->Item->StartPrice             = new AmountType;
        $request->Item->StartPrice->value      = (float)$this->newPrice;
        $request->Item->StartPrice->currencyID = CurrencyCodeType::C_USD;

        $response = $this->account()->trading()->reviseItem($request);

        if ($response === AckCodeType::C_FAILURE) {
            // Do we need to retry?
            // I don't think so

            throw new TradingApiException($request, $response);
        }
    }


    protected function account(): Account
    {
        return $this->item['account'];
    }
}

<?php

namespace App\Jobs;

use App\Account;
use App\Exceptions\TradingApiException;
use App\Item;
use DTS\eBaySDK\Trading\Enums\AckCodeType;
use DTS\eBaySDK\Trading\Types\ItemType;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ReviseItemQuantityToZero implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const ZERO = 0;

    /**
     * @var Item
     */
    protected $item;

    /**
     * Create a new job instance.
     *
     * @param Item $item
     */
    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    public function handle()
    {
        $request = $this->account()->reviseItemRequest();

        $request->Item = $this->item->itemType();

        $request->Item->Quantity = self::ZERO;

        $response = $this->account()->trading()->reviseItem($request);

        if ($response === AckCodeType::C_FAILURE) {
            // Do we need to retry?
            // I don't think so

            throw new TradingApiException($request, $response);
        }

        // Do nothing
        // May be log the action into database
        // But nothing for now
    }

    protected function account(): Account
    {
        return $this->item['account'];
    }
}

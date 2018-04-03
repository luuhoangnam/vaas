<?php

namespace App\Jobs\Miner;

use App\eBay\TradingAPI;
use App\Exceptions\TradingApiException;
use App\Miner\Item;
use DTS\eBaySDK\Trading\Enums\AckCodeType;
use DTS\eBaySDK\Trading\Types\GetItemRequestType;
use DTS\eBaySDK\Trading\Types\ItemType;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GetItem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $item;

    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    /**
     * @throws TradingApiException
     */
    public function handle()
    {
        $request = new GetItemRequestType;

        $request->ItemID = ($this->item->item_id);

        $response = TradingAPI::random()->getItem($request);

        if ($response->Ack === AckCodeType::C_FAILURE) {
            throw new TradingApiException($request, $response);
        }

        $itemType = $response->Item;

        $this->item->update([
            'picture_url'         => array_first($itemType->PictureDetails->PictureURL),
            'quantity_sold'       => $itemType->SellingStatus->QuantitySold,
            'primary_category_id' => $itemType->PrimaryCategory->CategoryID,
            'sku'                 => $itemType->SKU,
            'upc'                 => $this->extractUPC($itemType),
            'ean'                 => $this->extractEAN($itemType),
            'isbn'                => $this->extractISBN($itemType),
            'start_time'          => app_carbon($itemType->ListingDetails->StartTime),
            'end_time'            => app_carbon($itemType->ListingDetails->EndTime),
            'status'              => $itemType->SellingStatus->ListingStatus,
        ]);

        // Get Item Performance if it qualified
        $qualifiedPrice = config('miner.criterias.min_price', 10);
        if ($this->item->price >= $qualifiedPrice) {
            GetItemTransactions::dispatch($this->item)->onQueue('miner.item.performance');
        }
    }

    protected function itemASIN(ItemType $item)
    {
        if ($item->SKU && preg_match('/^[\d\w]{10}$/i', $item->SKU)) {
            return $item->SKU;
        }

        return null;
    }

    protected function extractUPC(ItemType $item)
    {
        if ($upc = $this->itemAttribute($item, 'UPC')) {
            return $upc;
        }

        // ProductListingDetails
        if ($upc = @$item->ProductListingDetails->UPC) {
            return $upc;
        }

        return null;
    }

    protected function extractEAN(ItemType $item)
    {
        if ($ean = $this->itemAttribute($item, 'EAN')) {
            return $ean;
        }

        if ($ean = @$item->ProductListingDetails->EAN) {
            return $ean;
        }

        return null;
    }

    protected function extractISBN(ItemType $item)
    {
        if ($isbn = $this->itemAttribute($item, 'ISBN')) {
            return $isbn;
        }

        if ($isbn = @$item->ProductListingDetails->ISBN) {
            return $isbn;
        }

        return null;
    }

    protected function itemAttribute(ItemType $item, $name)
    {
        if ( ! @$item->ItemSpecifics) {
            return null;
        }

        foreach ($item->ItemSpecifics->NameValueList as $attr) {
            if ($attr->Name === $name) {
                return array_first($attr->Value);
            }
        }

        return null;
    }
}

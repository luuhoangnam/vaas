<?php

namespace App;

use App\Exceptions\TradingApiException;
use App\Services\Ebay;
use DTS\eBaySDK\Trading\Enums\AckCodeType;
use DTS\eBaySDK\Trading\Types\ItemType;
use DTS\eBaySDK\Trading\Types\ReviseItemResponseType;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'item_id',
        'title',
        'price',
        'quantity',
        'quantity_sold',
        'primary_category_id',
        'start_time',
        'status',
        'sku',
        'upc',
    ];

    protected $casts = [
        'start_time' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public static function find($itemID)
    {
        return static::query()->where('item_id', $itemID)->first();
    }

    public static function exists($itemID)
    {
        return static::query()->where('item_id', $itemID)->exists();
    }

    public function getQuantityAvailableAttribute()
    {
        return $this['quantity'] - $this['quantity_sold'];
    }

    public function getTitleRankAttribute()
    {
        $result = cache()->remember("item(id:{$this['item_id']}):title_rank", 60, function () {
            return (new Ebay)->search($this['title'], [
                'ranking' => [
                    ['id' => $this['item_id'], 'type' => 'item_id'], // Self
                ],
            ]);
        });

        $rank  = $result['ranking']->first()['rank'];
        $total = $result['total'];

        return compact('rank', 'total');
    }

    public static function extractItemAttributes(ItemType $item, $fields = []): array
    {
        $attrs = [
            'item_id'             => $item->ItemID,
            'title'               => $item->Title,
            'price'               => $item->SellingStatus->CurrentPrice->value,
            'quantity'            => $item->Quantity,
            'quantity_sold'       => $item->SellingStatus->QuantitySold,
            'primary_category_id' => $item->PrimaryCategory->CategoryID,
            'start_time'          => app_carbon($item->ListingDetails->StartTime),
            'status'              => $item->SellingStatus->ListingStatus,
            //
            'sku'                 => $item->SKU,
            'upc'                 => optional($item->ProductListingDetails)->UPC,
        ];

        if ($fields) {
            return array_only($attrs, $fields);
        }

        return $attrs;
    }

    public function refillQuantity($displayQuantity = 1, ItemType $item = null)
    {
        /** @var \App\Account $account */
        $account = $this['account'];

        $request = $account->reviseItemRequest();

        $request->Item = new ItemType;

        $quantitySold = $item->SellingStatus->QuantitySold;

        // NewQuantity = CurrentQuantity + DisplayQuantity
        if ($item->Quantity - $quantitySold === $displayQuantity) {
            return null;
        }

        if ($item) {
            $newQuantity = $quantitySold + $displayQuantity;
        } else {
            $newQuantity = $this['quantity'] + $displayQuantity;
        }

        $request->Item->Quantity = $newQuantity;

        $response = $account->trading()->reviseItem($request);

        if ($response->Ack === AckCodeType::C_FAILURE) {
            throw new TradingApiException($request, $response);
        }

        $this->update([
            'quantity'      => $newQuantity,
            'quantity_sold' => $quantitySold,
        ]);

        return $response;
    }
}

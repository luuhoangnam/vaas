<?php

namespace App;

use App\Ranking\Trackable;
use App\Repricing\Repricer;
use App\Services\Ebay;
use DTS\eBaySDK\Trading\Types\ItemType;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Item extends Model
{
    use Searchable, Trackable;

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

    public function searchableAs()
    {
        return 'items';
    }

    public function toSearchableArray()
    {
        return array_merge(
            $this->toArray(),
            [
                'seller' => $this['account']['username'],
            ]
        );
    }

    public static function find($itemID): Item
    {
        return static::query()->where('item_id', $itemID)->firstOrFail();
    }

    public static function exists($itemID)
    {
        return static::query()->where('item_id', $itemID)->exists();
    }

    public function itemType(): ItemType
    {
        return new ItemType(['ItemID' => $this['item_id']]);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function repricer()
    {
        return $this->hasOne(Repricer::class);
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
}

<?php

namespace App;

use App\Cashback\AmazonAssociates;
use App\Exceptions\CanNotFetchProductInformation;
use App\Ranking\Trackable;
use App\Repricing\Repricer;
use App\Services\Ebay;
use App\Sourcing\AmazonProduct;
use DTS\eBaySDK\Trading\Enums\ListingStatusCodeType;
use DTS\eBaySDK\Trading\Types\ItemType;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

/**
 * Class Item
 *
 * @method active():Item
 *
 * @package App
 */
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
        return 'item';
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

    public function orders()
    {
        return $this->hasManyThrough(
            Order::class,
            Transaction::class,
            'item_id',
            'id',
            'item_id',
            'order_id'
        );
    }

    public function getEarningAttribute()
    {
        return $this->orders()->sum('total');
    }

    public function repricer()
    {
        return $this->hasOne(Repricer::class);
    }

    public function scopeActive(Builder $builder)
    {
        return $builder->where('status', ListingStatusCodeType::C_ACTIVE);
    }

    public function getEbayLinkAttribute()
    {
        return "https://www.ebay.com/itm/{$this['item_id']}";
    }

    public function getAssociateLinkAttribute()
    {
        return (new AmazonAssociates)->link($this['sku']);
    }

    public function getCashbackLinkAttribute()
    {
        try {
            return (new AmazonProduct($this['sku']))->getCashbackLink();
        } catch (CanNotFetchProductInformation $exception) {
            return null;
        } catch (RequestException $exception) {
            return null;
        }
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

    public function getSourcePriceAttribute()
    {
        try {
            return optional(new AmazonProduct($this['sku']))->price;
        } catch (CanNotFetchProductInformation $exception) {
            return null;
        }
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

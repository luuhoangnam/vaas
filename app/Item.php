<?php

namespace App;

use App\Events\ItemCreated;
use App\Ranking\Trackable;
use App\Ranking\Tracker;
use App\Repricing\Repricer;
use Carbon\Carbon;
use DTS\eBaySDK\Trading\Enums\ListingStatusCodeType;
use DTS\eBaySDK\Trading\Types\ItemType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

/**
 * Class Item
 *
 * @method active():Item
 * @method highValue($minimum = 50)
 * @method lowValue($maximum = 10)
 * @method static listedOn($asin)
 * @method priceBetween(float $minimum, float $maximum)
 * @method static since(Carbon | string | null $since)
 * @method static until(Carbon | string | null $until)
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
        'picture_url',
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

    protected $dispatchesEvents = ['created' => ItemCreated::class];

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

    public function getRouteKeyName()
    {
        return 'item_id';
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

    public function scopeListedOn(Builder $query, $asin)
    {
        $query->with('account')->where('sku', $asin);
    }

    public function scopeHighValue(Builder $query, $minimum = 50)
    {
        $query->where('price', '>=', $minimum);
    }

    public function scopeLowValue(Builder $query, $maximum = 10)
    {
        $query->where('price', '<=', $maximum);
    }

    public function scopeSince(Builder $query, $since)
    {
        if ( ! $since instanceof Carbon) {
            $since = new Carbon($since);
        }

        $query->where('start_time', '>=', $since);
    }

    public function scopeUntil(Builder $query, $until)
    {
        if ( ! $until instanceof Carbon) {
            $until = new Carbon($until);
        }

        $query->where('start_time', '<=', $until);
    }

    public function scopePriceBetween(Builder $query, $minimum, $maximum)
    {
        $query->where('price', '>=', $minimum)
              ->where('price', '<=', $maximum);
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

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'item_id', 'item_id');
    }

    public function getEarningAttribute()
    {
        return $this->orders()->sum('total');
    }

    public function repricer()
    {
        return $this->hasOne(Repricer::class);
    }

    public function createRepricer($productType, $productId, array $rule = null): Repricer
    {
        $data = [
            'product_type' => $productType,
            'product_id'   => $productId,
            'rule'         => $rule,
        ];

        return $this->repricer()->updateOrCreate(
            array_only($data, ['product_type', 'product_id']),
            $data
        );
    }

    public function trackers()
    {
        return $this->morphMany(Tracker::class, 'trackable');
    }

    public function scopeActive(Builder $builder)
    {
        $builder->where('status', ListingStatusCodeType::C_ACTIVE);
    }

    public function getEbayLinkAttribute()
    {
        return "https://www.ebay.com/itm/{$this['item_id']}";
    }

    public function getQuantityAvailableAttribute()
    {
        return $this['quantity'] - $this['quantity_sold'];
    }

    public function getTimeTookForFirstSaleAttribute()
    {
        $firstOrder = $this->orders()->oldest('created_time')->first();

        if ( ! $firstOrder instanceof Order) {
            return null;
        }

        return $firstOrder['created_time']->diffForHumans($this['start_time']);
    }

    public function getLastOrderSinceAttribute()
    {
        $lastOrder = $this->orders()->latest('created_time')->first();

        if ( ! $lastOrder instanceof Order) {
            return null;
        }

        return $lastOrder['created_time'];
    }

    public static function extractItemAttributes(ItemType $item, $only = [], $except = []): array
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
            // pictures
            'picture_url'         => array_first(optional($item->PictureDetails)->PictureURL) ?: null,
            //
            'sku'                 => $item->SKU,
            'upc'                 => optional($item->ProductListingDetails)->UPC,
        ];

        if ($only) {
            return array_only($attrs, $only);
        }

        if ($except) {
            return array_except($attrs, $except);
        }

        return $attrs;
    }
}

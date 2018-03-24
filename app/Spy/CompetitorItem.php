<?php

namespace App\Spy;

use App\Account;
use App\Events\FoundNewCompetitorItem;
use App\Product;
use DTS\eBaySDK\Finding\Types\SearchItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CompetitorItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'start_time' => 'date',
        'end_time'   => 'date',
    ];

    protected $dispatchesEvents = [
        'created' => FoundNewCompetitorItem::class,
    ];

    public function owner()
    {
        return $this->belongsTo(Competitor::class, 'competitor_id', 'id');
    }

    public function source()
    {
        return $this->hasOne(Product::class, 'asin', 'sku');
    }

    public static function persist($competitor, SearchItem $item): CompetitorItem
    {
        if ( ! $competitor instanceof Competitor) {
            $competitor = Competitor::find($competitor);
        }

        return $competitor->items()->updateOrCreate(
            ['item_id' => $item->itemId],
            static::extractSearchItem($item)
        );
    }

    public static function extractSearchItem(SearchItem $item)
    {
        return [
            'item_id'             => $item->itemId,
            'title'               => $item->title,
            'price'               => $item->sellingStatus->currentPrice->value,
            'primary_category_id' => $item->primaryCategory->categoryId,
            'start_time'          => app_carbon($item->listingInfo->startTime),
            'end_time'            => app_carbon($item->listingInfo->endTime),
            'status'              => $item->sellingStatus->sellingState,
            'picture_url'         => $item->galleryURL,
        ];
    }

    public static function find($id): CompetitorItem
    {
        return static::query()->where('item_id', $id)->firstOrFail();
    }

    public function getListedOnAttribute()
    {
        if ( ! $this['sku']) {
            return null;
        }

        return Account::query()
                      ->whereHas('items', function (Builder $builder) {
                          $builder->where('sku', $this['sku']);
                      })
                      ->get();
    }
}
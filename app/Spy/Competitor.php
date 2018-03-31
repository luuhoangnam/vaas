<?php

namespace App\Spy;

use App\Events\CompetitorSpied;
use DTS\eBaySDK\Finding\Types\SearchItem;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string username
 */
class Competitor extends Model
{
    protected $guarded = [];

    protected $dispatchesEvents = [
        'created' => CompetitorSpied::class,
    ];

    public static function spy($username, $watch = false): Competitor
    {
        return static::query()->updateOrCreate(
            compact('username'),
            compact('username', 'watch')
        );
    }

    public static function exists($username): bool
    {
        return static::query()->where('username', $username)->exists();
    }

    public static function find($username): Competitor
    {
        return static::query()->where('username', $username)->firstOrFail();
    }

    public function items()
    {
        return $this->hasMany(CompetitorItem::class, 'competitor_id', 'id');
    }

    public function persistItem(SearchItem $item): CompetitorItem
    {
        return $this->items()->updateOrCreate(
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
}

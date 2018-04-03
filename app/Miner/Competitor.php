<?php

namespace App\Miner;

use App\Events\Miner\CompetitorCreated;
use DTS\eBaySDK\Finding\Types\SearchItem;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string username
 */
class Competitor extends Model
{
    protected $guarded = [];

    protected $dispatchesEvents = [
        'created' => CompetitorCreated::class
    ];

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
        return $this->hasMany(Item::class);
    }

    public function persistSearchItem(SearchItem $item): Item
    {
        $find = ['item_id' => $item->itemId];
        $data = [
            'item_id' => $item->itemId,
            'title'   => $item->title,
            'price'   => $item->sellingStatus->currentPrice->value,
        ];

        return $this->items()->updateOrCreate($find, $data);
    }
}

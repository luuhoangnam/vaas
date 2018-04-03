<?php

namespace App\Miner;

use App\Events\Miner\CompetitorItemCreated;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string item_id
 * @property float  price
 */
class Item extends Model
{
    protected $table = 'competitor_items';
    protected $guarded = [];

    protected $dispatchesEvents = ['created' => CompetitorItemCreated::class];

    public static function exists($itemID): bool
    {
        return static::query()->where('item_id', $itemID)->exists();
    }

    public static function find($itemID): Item
    {
        return static::query()->where('item_id', $itemID)->firstOrFail();
    }

    public function performance()
    {
        return $this->hasMany(PerformanceIndicator::class, 'item_id', 'item_id');
    }

    public function updatePerformance($period, $quantitySold, $revenue): PerformanceIndicator
    {
        $values = [
            'item_id'       => $this->item_id,
            'period'        => $period,
            'quantity_sold' => $quantitySold,
            'revenue'       => $revenue,
        ];

        return $this->performance()->updateOrCreate(array_only($values, ['period', 'item_id']), $values);
    }
}

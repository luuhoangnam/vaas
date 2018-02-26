<?php

namespace App;

use App\Services\Ebay;
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
    ];

    protected $casts = [
        'start_time' => 'datetime',
    ];

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
}

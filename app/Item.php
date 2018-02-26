<?php

namespace App;

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
}

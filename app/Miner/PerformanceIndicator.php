<?php

namespace App\Miner;

use Illuminate\Database\Eloquent\Model;

class PerformanceIndicator extends Model
{
    protected $guarded = [];

    protected $casts = ['item_id' => 'integer'];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }
}

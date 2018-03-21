<?php

namespace App;

use DTS\eBaySDK\Trading\Types\TransactionType;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['transaction_id', 'quantity', 'item_id', 'item_site', 'item_title', 'item_sku'];

    protected $casts = ['item_id' => 'integer'];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }
}

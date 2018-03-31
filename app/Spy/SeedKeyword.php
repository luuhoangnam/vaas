<?php

namespace App\Spy;

use App\Events\SeedKeywordAdded;
use Illuminate\Database\Eloquent\Model;

class SeedKeyword extends Model
{
    protected $guarded = [];

    protected $dispatchesEvents = [
        'created' => SeedKeywordAdded::class,
    ];

    public function competitors()
    {
        return $this->belongsTo(SeedKeyword::class);
    }
}

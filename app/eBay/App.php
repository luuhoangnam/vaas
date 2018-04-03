<?php

namespace App\eBay;

use Illuminate\Database\Eloquent\Model;

class App extends Model
{
    protected $guarded = [];
    protected $casts = ['usage' => 'array'];
}

<?php

namespace App\Ranking;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    protected $fillable = ['rank', 'total'];
}

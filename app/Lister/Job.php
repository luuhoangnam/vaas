<?php

namespace App\Lister;

use App\Events\ListerJobCreated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'pictures'   => 'array',
        'attributes' => 'array',
        'errors'     => 'array',
    ];

    protected $dispatchesEvents = [
        'created' => ListerJobCreated::class,
    ];
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = ['name', 'type', 'content'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

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

    public function render($data = []): string
    {
        $engine = new \Mustache_Engine();

        return $engine->render($this['content'], $data);
    }
}

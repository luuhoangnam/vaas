<?php

namespace App\Ranking;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Trackable
{
    public function trackers(): MorphMany
    {
        return $this->morphMany(Tracker::class, 'trackable');
    }

    public function track($keyword): Tracker
    {
        return $this->trackers()->create(compact('keyword'));
    }
}
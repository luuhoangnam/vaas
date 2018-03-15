<?php

namespace App\Ranking;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    protected $fillable = ['rank', 'total'];

    public function tracker()
    {
        return $this->belongsTo(Tracker::class);
    }

    public function getPreviousAttribute()
    {
        return static::query()
                     ->where('tracker_id', $this['tracker_id'])
                     ->where('id', '!=', $this['id'])
                     ->latest()
                     ->first();
    }

    public function getChangeAttribute()
    {
        $previous = $this->getPreviousAttribute();

        if (is_null($previous) || is_null($previous['rank']) || is_null($this['rank'])) {
            return null;
        }

        return $previous['rank'] - $this['rank'];
    }
}

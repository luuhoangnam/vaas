<?php

namespace App\Spy;

use App\Events\CompetitorSpied;
use DTS\eBaySDK\Finding\Types\SearchItem;
use Illuminate\Database\Eloquent\Model;

class Competitor extends Model
{
    protected $guarded = [];

    protected $dispatchesEvents = [
        'created' => CompetitorSpied::class,
    ];

    public static function spy($username, $watch = false): Competitor
    {
        return static::query()->updateOrCreate(
            compact('username'),
            compact('username', 'watch')
        );
    }

    public static function exists($username): bool
    {
        return static::query()->where('username', $username)->exists();
    }

    public static function find($username): Competitor
    {
        return static::query()->where('username', $username)->firstOrFail();
    }

    public function items()
    {
        return $this->hasMany(CompetitorItem::class, 'competitor_id', 'id');
    }
}

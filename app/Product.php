<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    protected $casts = [
        'images'     => 'array',
        'attributes' => 'array',
        'features'   => 'array',
        'offers'     => 'array',
    ];

    public static function find($asin): Product
    {
        return static::query()
                     ->with('owners')
                     ->where('asin', $asin)
                     ->firstOrFail();
    }

    public static function sync(array $data): Product
    {
        $data = array_except($data, 'processor');

        return static::query()->updateOrCreate(
            ['asin' => $data['asin']],
            $data
        );
    }

    public function owners()
    {
        return $this->belongsToMany(User::class, 'product_user', 'product_id', 'user_id');
    }
}

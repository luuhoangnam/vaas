<?php

namespace App;

use App\Exceptions\SourceProductClassDoesNotExistsException;
use App\Sourcing\AmazonAPI;
use App\Sourcing\AmazonCom;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    protected $casts = ['images' => 'array', 'attributes' => 'array'];

    public static function find($sourceId, $type = AmazonCom::class): Product
    {
        return static::query()
                     ->where('type', $type)
                     ->where('source_id', $sourceId)
                     ->firstOrFail();
    }

    public function owners()
    {
        return $this->belongsToMany(User::class, 'product_user', 'product_id', 'user_id');
    }

    public function sync(): void
    {
        /** @var AmazonAPI $handler */
        $handler = $this->scraper();

        $data = $handler->scrape();

        $this->update($data, ['touch' => true]);
    }

    protected function scraper()
    {
        if ( ! class_exists($this['type'])) {
            throw new SourceProductClassDoesNotExistsException($this['type']);
        }

        return new $this['type']($this['source_id']);
    }
}

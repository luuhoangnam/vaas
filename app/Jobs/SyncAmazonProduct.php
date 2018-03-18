<?php

namespace App\Jobs;

use App\Product;
use App\Sourcing\AmazonAPI;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SyncAmazonProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Product|string
     */
    protected $product;

    /**
     * SyncAmazonProduct constructor.
     *
     * @param Product|string $product
     */
    public function __construct($product)
    {
        $this->product = $product;
    }

    public function handle()
    {
        $asin = $this->product instanceof Product ? $this->product['asin'] : $this->product;

        $data = AmazonAPI::inspect($asin);

        Product::sync($data);
    }
}

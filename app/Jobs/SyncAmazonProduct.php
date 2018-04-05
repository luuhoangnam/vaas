<?php

namespace App\Jobs;

use App\Exceptions\Amazon\ProductAdvertisingAPIException;
use App\Jobs\Amazon\ExtractOffers;
use App\Product;
use App\Amazon\AmazonAPI;
use App\Amazon\AmazonCrawler;
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

        try {
            $data = AmazonAPI::inspect($asin);
        } catch (ProductAdvertisingAPIException $exception) {
            if ($exception->getCode() !== 'AWS.ECommerceService.ItemNotAccessible') {
                throw $exception;
            }

            $data = AmazonCrawler::get($asin);
        }

        Product::sync($data);

        // Sync Offers
        ExtractOffers::dispatch($asin);

        return $data;
    }
}

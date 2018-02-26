<?php

namespace App\Providers;

use App\Services\ApiTransporter;
use DTS\eBaySDK\Constants\SiteIds;
use DTS\eBaySDK\Finding\Services\FindingService;
use DTS\eBaySDK\Sdk;
use DTS\eBaySDK\Shopping\Services\ShoppingService;
use DTS\eBaySDK\Trading\Services\TradingService;
use Illuminate\Support\ServiceProvider;

class EbayServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Sdk::class, function () {
            return new Sdk([
                'siteId'      => SiteIds::US,
                'credentials' => [
                    'appId'  => env('EBAY_APP_ID'),
                    'certId' => env('EBAY_CERT_ID'),
                    'devId'  => env('EBAY_DEV_ID'),
                ],
            ]);
        });

        $this->app->bind(FindingService::class, function () {
            $sdk = $this->app->make(Sdk::class);

            return new ApiTransporter($sdk->createFinding());
        });

        $this->app->bind(TradingService::class, function () {
            $sdk = $this->app->make(Sdk::class);

            return $sdk->createTrading();
        });

        $this->app->bind(ShoppingService::class, function () {
            $sdk = $this->app->make(Sdk::class);

            return $sdk->createShopping();
        });
    }
}

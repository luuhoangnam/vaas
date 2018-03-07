<?php

namespace App\Providers;

use DTS\eBaySDK\BusinessPoliciesManagement\Services\BusinessPoliciesManagementService;
use DTS\eBaySDK\Constants\GlobalIds;
use DTS\eBaySDK\Constants\SiteIds;
use DTS\eBaySDK\Feedback\Services\FeedbackService;
use DTS\eBaySDK\Finding\Services\FindingService;
use DTS\eBaySDK\ReturnManagement\Services\ReturnManagementService;
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

            return $sdk->createFinding();
        });

        $this->app->bind(TradingService::class, function () {
            $sdk = $this->app->make(Sdk::class);

            return $sdk->createTrading();
        });

        $this->app->bind(ShoppingService::class, function () {
            $sdk = $this->app->make(Sdk::class);

            return $sdk->createShopping();
        });

        $this->app->bind(BusinessPoliciesManagementService::class, function () {
            $sdk = $this->app->make(Sdk::class);

            return $sdk->createBusinessPoliciesManagement();
        });

        $this->app->bind(ReturnManagementService::class, function () {
            $sdk = $this->app->make(Sdk::class);

            return $sdk->createReturnManagement();
        });

        $this->app->bind(FeedbackService::class, function () {
            $sdk = $this->app->make(Sdk::class);

            return $sdk->createFeedback();
        });
    }
}

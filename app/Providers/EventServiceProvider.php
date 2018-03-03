<?php

namespace App\Providers;

use App\Events\PlatformNotifications\FixedPriceTransaction;
use App\Listeners\ItemEventSubscriber;
use App\Listeners\RefillItemQuantity;
use App\Listeners\TriggerSyncNewlyOrders;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        FixedPriceTransaction::class => [
            TriggerSyncNewlyOrders::class,
            RefillItemQuantity::class,
        ],
    ];

    protected $subscribe = [
        ItemEventSubscriber::class,
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}

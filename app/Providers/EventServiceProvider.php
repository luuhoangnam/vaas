<?php

namespace App\Providers;

use App\Events\ItemCreated;
use App\Events\ListerJobCreated;
use App\Events\PlatformNotificationReceived;
use App\Events\PlatformNotifications\FixedPriceTransaction;
use App\Listeners\AttachTracker;
use App\Listeners\CreateCompanionListerQueueJob;
use App\Listeners\LogPlatformNotificationPayload;
use App\Listeners\RefillItemQuantity;
use App\Listeners\Subscribers\ItemEventsSubscriber;
use App\Listeners\TriggerSyncNewlyOrders;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Laravel\Passport\Events\AccessTokenCreated;
use Laravel\Passport\Events\RefreshTokenCreated;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        # Laravel Passport
        AccessTokenCreated::class => [
            // RevokeOldTokens::class,
        ],

        RefreshTokenCreated::class          => [
            // PruneOldTokens::class,
        ],

        # Platform
        PlatformNotificationReceived::class => [
            LogPlatformNotificationPayload::class,
        ],

        FixedPriceTransaction::class => [
            TriggerSyncNewlyOrders::class,
            RefillItemQuantity::class,
        ],

        ItemCreated::class => [
            AttachTracker::class,
        ],

        ListerJobCreated::class => [
            CreateCompanionListerQueueJob::class,
        ],
    ];

    protected $subscribe = [
        ItemEventsSubscriber::class,
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

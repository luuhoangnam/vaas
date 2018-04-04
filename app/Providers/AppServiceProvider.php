<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Horizon;

class AppServiceProvider extends ServiceProvider
{
    protected $developers = [
        'hoangnam0705@icloud.com',
        'davidhazeland@gmail.com',
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        # HORIZON
        Horizon::auth(function (Request $request) {
            return $this->app->environment('local') || $request->user() && $request->user()->isDeveloper();
        });

        Horizon::routeSlackNotificationsTo(config('horizon.notifications.slack'));
        # END HORIZON
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('developers', function () {
            return $this->developers;
        });
    }
}

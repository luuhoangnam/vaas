<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Horizon;

class AppServiceProvider extends ServiceProvider
{
    protected $developers = [
        'hoangnam0705@icloud.com',
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Horizon::auth(function (Request $request) {
            return $this->app->environment('local') ||
                   $request->user() && in_array($request->user()['email'], $this->developers);
        });
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

<?php

namespace App\Sourcing;

use Illuminate\Support\ServiceProvider;

class SourcingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('sourcing', function ($app) {
            return new SourceManager($app);
        });
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::if ('env', function ($environment) {
            return app()->environment($environment);
        });

        Blade::if ('beta', function () {
            return app()->environment('local') || request()->user()->isDeveloper();
        });

        # COMPONENTS
        Blade::component('components.statistic', 'statistic');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

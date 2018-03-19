<?php

namespace App\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class DebugServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->mapTestRoutes();
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

    protected function mapTestRoutes()
    {
        if ( ! $this->app->environment('local') || ! file_exists(base_path('routes/test.php'))) {
            return;
        }

        /** @var Router $router */
        $router = $this->app->make(Router::class);

        $router->middleware('web')
               ->namespace('App\Http\Controllers')
               ->group(base_path('routes/test.php'));
    }
}

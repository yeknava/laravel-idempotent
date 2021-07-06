<?php

namespace Yeknava\LaravelIdempotent;

use Illuminate\Support\ServiceProvider;

class LaravelIdempotentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/idempotent.php' => $this->app->configPath().'/idempotent.php',
        ]);
    }
}

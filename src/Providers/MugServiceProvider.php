<?php

namespace Mralston\Mug\Providers;

use Illuminate\Support\ServiceProvider;
use Mralston\Mug\Mug;

class MugServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/config.php', 'mug');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton('mug', function ($app) {
            return new Mug(
                config('mug.client_id'),
                config('mug.secret'),
                config('mug.endpoint'),
            );
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/config.php' => config_path('mug.php'),
            ], 'pdf-config');
        }
    }
}

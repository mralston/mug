<?php

namespace Mralston\Mug\Providers;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Mralston\Mug\Mug;

class MugServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/config.php', 'mug');

        $this->app->singleton('mug', function (Application $app): Mug {
            $store = config('mug.token_cache_store');

            return new Mug(
                (string) config('mug.client_id'),
                (string) config('mug.secret'),
                (string) config('mug.endpoint'),
                $app->make(CacheFactory::class)->store(is_string($store) && $store !== '' ? $store : null),
            );
        });

        $this->app->alias('mug', Mug::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/config.php' => config_path('mug.php'),
            ], ['mug-config', 'pdf-config']);
        }
    }
}

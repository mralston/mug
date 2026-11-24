<?php

namespace Mralston\Mug\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Http;
use Mralston\Mug\Providers\MugServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    /**
     * @param  Application  $app
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [MugServiceProvider::class];
    }

    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('mug.client_id', 'fake-client');
        $app['config']->set('mug.secret', 'fake-secret');
        $app['config']->set('mug.endpoint', 'testing');
        $app['config']->set('cache.default', 'array');
    }
}

<?php

namespace Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use TheShit\Finance\FinanceServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [FinanceServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('finance.plaid.client_id', 'test-client-id');
        $app['config']->set('finance.plaid.secret', 'test-secret');
        $app['config']->set('finance.plaid.access_token', 'test-access-token');
        $app['config']->set('finance.plaid.environment', 'sandbox');
        $app['config']->set('finance.privacy.driver', 'passthrough');
    }
}

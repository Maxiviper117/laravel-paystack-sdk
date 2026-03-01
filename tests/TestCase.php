<?php

namespace Maxiviper117\Paystack\Tests;

use Maxiviper117\Paystack\PaystackServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            PaystackServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('paystack.secret_key', 'sk_test_123');
        $app['config']->set('paystack.base_url', 'https://api.paystack.co');
        $app['config']->set('paystack.timeout', 30);
        $app['config']->set('paystack.connect_timeout', 10);
        $app['config']->set('paystack.retry_times', 2);
        $app['config']->set('paystack.retry_sleep_ms', 250);
        $app['config']->set('paystack.throw_on_api_error', true);
    }
}

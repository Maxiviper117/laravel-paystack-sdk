<?php

namespace Maxiviper117\Paystack\Tests;

use Illuminate\Foundation\Application;
use Maxiviper117\Paystack\PaystackServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Spatie\WebhookClient\WebhookClientServiceProvider;

class TestCase extends BaseTestCase
{
    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /**
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            WebhookClientServiceProvider::class,
            PaystackServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('queue.default', 'sync');
        $app['config']->set('paystack.secret_key', 'sk_test_123');
        $app['config']->set('paystack.base_url', 'https://api.paystack.co');
        $app['config']->set('paystack.timeout', 30);
        $app['config']->set('paystack.connect_timeout', 10);
        $app['config']->set('paystack.retry_times', 2);
        $app['config']->set('paystack.retry_sleep_ms', 250);
        $app['config']->set('paystack.throw_on_api_error', true);
        $app['config']->set('paystack.webhooks.signing_secret', 'sk_test_123');
        $app['config']->set('paystack.webhooks.store_headers', ['x-paystack-signature', 'content-type', 'user-agent']);
    }
}

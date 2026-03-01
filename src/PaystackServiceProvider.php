<?php

namespace Maxiviper117\Paystack;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Container\Container;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Support\Payload;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PaystackServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('paystack')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(PaystackConnector::class, function (Container $app): PaystackConnector {
            /** @var ConfigRepository $configRepository */
            $configRepository = $app->make('config');
            $rawConfig = $configRepository->get('paystack', []);

            if (! is_array($rawConfig)) {
                $rawConfig = [];
            }

            /** @var array<string, mixed> $config */
            $config = $rawConfig;

            return new PaystackConnector(
                secretKey: Payload::string($config, 'secret_key'),
                baseUrl: Payload::string($config, 'base_url', 'https://api.paystack.co'),
                timeout: Payload::int($config, 'timeout', 30),
                connectTimeout: Payload::int($config, 'connect_timeout', 10),
                tries: Payload::int($config, 'retry_times', 2),
                retryInterval: Payload::int($config, 'retry_sleep_ms', 250),
                throwOnApiError: Payload::bool($config, 'throw_on_api_error', true),
            );
        });

        $this->app->singleton(PaystackManager::class, fn (Container $app) => new PaystackManager($app));
        $this->app->singleton('paystack', fn (Container $app) => $app->make(PaystackManager::class));
    }
}

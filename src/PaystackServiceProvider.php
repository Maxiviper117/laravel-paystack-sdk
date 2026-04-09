<?php

namespace Maxiviper117\Paystack;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Container\Container;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Jobs\ProcessPaystackWebhookJob;
use Maxiviper117\Paystack\Models\PaystackWebhookCall;
use Maxiviper117\Paystack\Support\Payload;
use Maxiviper117\Paystack\Webhooks\PaystackSignatureValidator;
use Maxiviper117\Paystack\Webhooks\PaystackWebhookProfile;
use Maxiviper117\Paystack\Webhooks\PaystackWebhookResponse;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PaystackServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('paystack')
            ->hasConfigFile()
            ->hasMigrations([
                'create_paystack_customers_table',
                'create_paystack_subscriptions_table',
            ]);
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

        /** @var ConfigRepository $config */
        $config = $this->app->make('config');

        $rawWebhookConfig = $config->get('paystack.webhooks', []);

        if (! is_array($rawWebhookConfig)) {
            $rawWebhookConfig = [];
        }

        /** @var array<string, mixed> $webhookConfig */
        $webhookConfig = $rawWebhookConfig;
        $configName = Payload::string($webhookConfig, 'config_name', 'paystack');

        $config->set('webhook-client.configs', [[
            'name' => $configName,
            'signing_secret' => Payload::string($webhookConfig, 'signing_secret'),
            'signature_header_name' => Payload::string($webhookConfig, 'signature_header_name', 'x-paystack-signature'),
            'signature_validator' => PaystackSignatureValidator::class,
            'webhook_profile' => PaystackWebhookProfile::class,
            'webhook_response' => PaystackWebhookResponse::class,
            'webhook_model' => PaystackWebhookCall::class,
            'store_headers' => $webhookConfig['store_headers'] ?? [],
            'process_webhook_job' => ProcessPaystackWebhookJob::class,
        ]]);
        $config->set('webhook-client.delete_after_days', Payload::int($webhookConfig, 'delete_after_days', 30));
    }
}

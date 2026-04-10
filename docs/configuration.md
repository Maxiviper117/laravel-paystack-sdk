# Configuration

Set the following environment variables in your Laravel application.

```env
PAYSTACK_SECRET_KEY=sk_test_xxx
PAYSTACK_PUBLIC_KEY=pk_test_xxx
PAYSTACK_BASE_URL=https://api.paystack.co
PAYSTACK_TIMEOUT=30
PAYSTACK_CONNECT_TIMEOUT=10
PAYSTACK_RETRY_TIMES=2
PAYSTACK_RETRY_SLEEP_MS=250
PAYSTACK_THROW_ON_API_ERROR=true
PAYSTACK_WEBHOOK_DELETE_AFTER_DAYS=30
PAYSTACK_WEBHOOK_ALLOWED_IPS=52.31.139.75,52.49.173.169,52.214.14.220
PAYSTACK_WEBHOOK_QUEUE=
PAYSTACK_WEBHOOK_CONNECTION=
```

## Required environment variables

- `PAYSTACK_SECRET_KEY`: required for authenticated Paystack API requests and Paystack webhook signature verification

## Optional environment variables

- `PAYSTACK_PUBLIC_KEY`: optional; not used directly by this package, but needed if your application also integrates Paystack frontend tooling such as Paystack Inline or mobile SDKs
- `PAYSTACK_BASE_URL`: override only if you need a non-default Paystack API host
- `PAYSTACK_TIMEOUT`: overall request timeout in seconds
- `PAYSTACK_CONNECT_TIMEOUT`: connect timeout in seconds
- `PAYSTACK_RETRY_TIMES`: retry count applied by the connector
- `PAYSTACK_RETRY_SLEEP_MS`: delay between retries in milliseconds
- `PAYSTACK_THROW_ON_API_ERROR`: whether API failures are promoted to package exceptions
- `PAYSTACK_WEBHOOK_DELETE_AFTER_DAYS`: retention period for stored `webhook_calls`
- `PAYSTACK_WEBHOOK_ALLOWED_IPS`: comma-separated list of accepted Paystack webhook IPs; leave empty to disable the whitelist check
- `PAYSTACK_WEBHOOK_QUEUE`: optional queue name for webhook processing jobs; if omitted, the package uses Laravel's default queue name
- `PAYSTACK_WEBHOOK_CONNECTION`: optional queue connection for webhook processing jobs; if omitted, the package uses Laravel's default queue connection

## Connector behavior

`PaystackConnector` is responsible for:

- base URL selection
- bearer authentication
- timeout configuration
- retry behavior
- API error handling

## Next step

Choose a feature area:

- [Transactions](/transactions)
- [Customers](/customers)
- [Disputes](/disputes)
- [Billing Layer](/billing-layer)
- [Plans](/plans)
- [Subscriptions](/subscriptions)
- [Webhooks](/webhooks)
- [Examples](/examples/)

## Webhook infrastructure

Webhook handling is endpoint-first and asynchronous:

- register the endpoint with `Route::webhooks('paystack/webhook', 'paystack')`
- publish the package config with `php artisan vendor:publish --tag=paystack-config` if you want to edit `config/paystack.php`
- in `config/paystack.php`, keep `webhooks.allowed_ips` set to the documented Paystack webhook IPs so only requests from Paystack's published webhook IPs are accepted
- if you need to override the list, update `webhooks.allowed_ips` in `config/paystack.php` or set `PAYSTACK_WEBHOOK_ALLOWED_IPS` in your environment
- publish the webhook client migration with `php artisan vendor:publish --provider="Spatie\WebhookClient\WebhookClientServiceProvider" --tag="webhook-client-migrations"`
- run `php artisan migrate` so the `webhook_calls` table exists
- run a queue worker so `ProcessPaystackWebhookJob` can dispatch `PaystackWebhookReceived`

## `config/paystack.php`

The package ships a published configuration file at `config/paystack.php`. This is the main place to tune the SDK in a Laravel app because it keeps the connector, webhook security, and queue behavior in one predictable place.

Publish it with:

```bash
php artisan vendor:publish --tag=paystack-config
```

After publishing the config file, you can review or override these settings:

- `secret_key`: Paystack secret key used for authenticated API requests and webhook signature verification
- `public_key`: optional public key for apps that also use Paystack client-side tooling
- `base_url`: the API base URL, which defaults to `https://api.paystack.co`
- `timeout`: request timeout in seconds
- `connect_timeout`: connection timeout in seconds
- `retry_times`: retry count for the Saloon connector
- `retry_sleep_ms`: delay between retries in milliseconds
- `throw_on_api_error`: whether API failures should throw package exceptions
- `webhooks.config_name`: webhook-client config name, usually `paystack`
- `webhooks.signing_secret`: signing secret used to validate incoming webhook signatures
- `webhooks.signature_header_name`: signature header name, usually `x-paystack-signature`
- `webhooks.allowed_ips`: the Paystack webhook IP whitelist used to reject requests that do not come from Paystack's published webhook IPs
- `webhooks.store_headers`: request headers stored with each webhook call
- `webhooks.delete_after_days`: retention period for stored webhook calls
- `webhooks.queue`: optional queue name for webhook processing jobs
- `webhooks.connection`: optional queue connection for webhook processing jobs

For the webhook IP whitelist, keep the published Paystack IPs in `webhooks.allowed_ips` unless you have a specific reason to change them. If you need to temporarily replay a webhook from another source in local development, you can override the list with `PAYSTACK_WEBHOOK_ALLOWED_IPS` in your environment.

Example shape:

```php
return [
    'secret_key' => env('PAYSTACK_SECRET_KEY'),
    'public_key' => env('PAYSTACK_PUBLIC_KEY'),
    'base_url' => env('PAYSTACK_BASE_URL', 'https://api.paystack.co'),
    'timeout' => (int) env('PAYSTACK_TIMEOUT', 30),
    'connect_timeout' => (int) env('PAYSTACK_CONNECT_TIMEOUT', 10),
    'retry_times' => (int) env('PAYSTACK_RETRY_TIMES', 2),
    'retry_sleep_ms' => (int) env('PAYSTACK_RETRY_SLEEP_MS', 250),
    'throw_on_api_error' => env('PAYSTACK_THROW_ON_API_ERROR', true),
    'webhooks' => [
        'config_name' => 'paystack',
        'signing_secret' => env('PAYSTACK_SECRET_KEY'),
        'signature_header_name' => 'x-paystack-signature',
        'allowed_ips' => [
            '52.31.139.75',
            '52.49.173.169',
            '52.214.14.220',
        ],
        'store_headers' => ['x-paystack-signature', 'content-type', 'user-agent'],
        'delete_after_days' => (int) env('PAYSTACK_WEBHOOK_DELETE_AFTER_DAYS', 30),
        'queue' => env('PAYSTACK_WEBHOOK_QUEUE'),
        'connection' => env('PAYSTACK_WEBHOOK_CONNECTION'),
    ],
];
```

## Example workflows

- [One-Time Checkout](/examples/checkout)
- [Optional Billing Layer](/examples/billing-layer)
- [Webhook Processing](/examples/webhooks)

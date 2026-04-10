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
- publish the webhook client migration with `php artisan vendor:publish --provider="Spatie\WebhookClient\WebhookClientServiceProvider" --tag="webhook-client-migrations"`
- run `php artisan migrate` so the `webhook_calls` table exists
- run a queue worker so `ProcessPaystackWebhookJob` can dispatch `PaystackWebhookReceived`

## Example workflows

- [One-Time Checkout](/examples/checkout)
- [Optional Billing Layer](/examples/billing-layer)
- [Webhook Processing](/examples/webhooks)

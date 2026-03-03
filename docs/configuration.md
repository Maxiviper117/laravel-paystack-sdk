# Configuration

Set the following environment variables in your Laravel application:

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
PAYSTACK_WEBHOOK_QUEUE=
PAYSTACK_WEBHOOK_CONNECTION=
```

## What these settings control

- `PAYSTACK_SECRET_KEY`: server-side authentication for API requests and Paystack webhook signature verification
- `PAYSTACK_PUBLIC_KEY`: public Paystack key for application use where needed
- `PAYSTACK_BASE_URL`: override only if you need a non-default Paystack API host
- `PAYSTACK_TIMEOUT`: overall request timeout in seconds
- `PAYSTACK_CONNECT_TIMEOUT`: connect timeout in seconds
- `PAYSTACK_RETRY_TIMES`: retry count applied by the connector
- `PAYSTACK_RETRY_SLEEP_MS`: delay between retries in milliseconds
- `PAYSTACK_THROW_ON_API_ERROR`: whether API failures are promoted to package exceptions
- `PAYSTACK_WEBHOOK_DELETE_AFTER_DAYS`: retention period for stored `webhook_calls`
- `PAYSTACK_WEBHOOK_QUEUE`: optional queue name for webhook processing jobs
- `PAYSTACK_WEBHOOK_CONNECTION`: optional queue connection for webhook processing jobs

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
- [Plans](/plans)
- [Subscriptions](/subscriptions)
- [Webhooks](/webhooks)

## Webhook infrastructure

Webhook handling is endpoint-first and asynchronous:

- register the endpoint with `Route::webhooks('paystack/webhook', 'paystack')`
- run the webhook client migration so the `webhook_calls` table exists
- run a queue worker so `ProcessPaystackWebhookJob` can dispatch `PaystackWebhookReceived`

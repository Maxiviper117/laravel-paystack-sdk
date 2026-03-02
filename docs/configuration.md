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
```

## What these settings control

- `PAYSTACK_SECRET_KEY`: server-side authentication for API requests and webhook signature verification
- `PAYSTACK_PUBLIC_KEY`: public Paystack key for application use where needed
- `PAYSTACK_BASE_URL`: override only if you need a non-default Paystack API host
- `PAYSTACK_TIMEOUT`: overall request timeout in seconds
- `PAYSTACK_CONNECT_TIMEOUT`: connect timeout in seconds
- `PAYSTACK_RETRY_TIMES`: retry count applied by the connector
- `PAYSTACK_RETRY_SLEEP_MS`: delay between retries in milliseconds
- `PAYSTACK_THROW_ON_API_ERROR`: whether API failures are promoted to package exceptions

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

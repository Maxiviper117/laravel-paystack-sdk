# Getting Started

This package is for Laravel applications that want a typed Paystack integration without passing raw arrays through the public SDK surface.

## What you get

- container-resolved action classes
- typed input DTOs under `src/Data/Input`
- action-specific response DTOs under `src/Data/Output`
- a Saloon connector that handles auth, base URL, retries, timeouts, and API error behavior
- optional `Paystack` facade and `PaystackManager`
- optional Billable persistence for local Paystack customer and subscription records

## Typical flow

1. Install the package.
2. Configure your Paystack secret and public keys.
3. Resolve an action or use the facade.
4. Pass a typed input DTO.
5. Work with the typed response DTO returned by the package.

## Example

```php
use Maxiviper117\Paystack\Actions\Transaction\InitializeTransactionAction;
use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;

$initializeTransaction = app(InitializeTransactionAction::class);

$response = $initializeTransaction(
    new InitializeTransactionInputData(
        email: 'customer@example.com',
        amount: 15.50,
        channels: ['card', 'bank_transfer'],
        callbackUrl: 'https://example.com/payments/callback',
        reference: 'order_123',
    )
);
```

From here, continue with:

- [Installation](/installation)
- [Configuration](/configuration)
- [Transactions](/transactions)
- [Examples Overview](/examples/)
- [Billing Layer](/billing-layer)
- [One-Time Checkout](/examples/checkout)
- [Webhook Processing](/examples/webhooks)

# Getting Started

This package is for Laravel applications that want a typed Paystack integration without passing raw arrays through the public SDK surface.

## What you get

- a Laravel-first facade and manager convenience layer
- typed input DTOs under `src/Data/Input`
- action-specific response DTOs under `src/Data/Output`
- a Saloon connector that handles auth, base URL, retries, timeouts, and API error behavior
- optional injectable action classes for custom use
- optional Billable persistence for local Paystack customer, plan, subscription, transaction, refund, and dispute records

## Typical flow

1. Install the package.
2. Configure your Paystack secret and public keys.
3. Use the facade or manager for standard application code, or resolve an action for custom use.
4. Pass a typed input DTO.
5. Work with the typed response DTO returned by the package.

## Example

```php
namespace App\Services\Billing;

use Maxiviper117\Paystack\Actions\Transaction\InitializeTransactionAction;
use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;

class StartCheckout
{
    public function __construct(
        private InitializeTransactionAction $initializeTransaction,
    ) {}

    public function handle(): void
    {
        $response = ($this->initializeTransaction)(
            InitializeTransactionInputData::from([
                'email' => 'customer@example.com',
                'amount' => 15.50,
                'channels' => ['card', 'bank_transfer'],
                'callbackUrl' => 'https://example.com/payments/callback',
                'reference' => 'order_123',
            ])
        );
    }
}
```

From here, continue with:

- [Installation](/installation)
- [Configuration](/configuration)
- [Transactions](/transactions)
- [Examples Overview](/examples/)
- [Billing Layer](/billing-layer)
- [One-Time Checkout](/examples/checkout)
- [Webhook Processing](/examples/webhooks)

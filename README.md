# Laravel Paystack SDK

[![Latest Version on Packagist](https://img.shields.io/packagist/v/maxiviper117/laravel-paystack-sdk.svg?style=flat-square)](https://packagist.org/packages/maxiviper117/laravel-paystack-sdk)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/Maxiviper117/laravel-paystack-sdk/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/Maxiviper117/laravel-paystack-sdk/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/Maxiviper117/laravel-paystack-sdk/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/Maxiviper117/laravel-paystack-sdk/actions?query=workflow%3A%22Fix+PHP+code+style+issues%22+branch%3Amain)

Laravel package for working with Paystack through Saloon-backed requests and a Laravel-first convenience layer. The current supported surface covers transactions, customers, disputes, refunds, webhooks, plans, subscriptions, and billing helpers with typed input and response DTOs. Actions remain available for custom integration paths.

> [!WARNING]
> This package is still a work in progress and is not yet stable. Expect API changes, incomplete endpoint coverage, and breaking changes until the first `1.0.0` release.

## Documentation

Package documentation is available through the repository docs site:

- GitHub Pages: [https://maxiviper117.github.io/laravel-paystack-sdk/](https://maxiviper117.github.io/laravel-paystack-sdk/)

Run the docs site locally from the repository root:

```bash
pnpm install --frozen-lockfile
pnpm run docs:dev
```

Build the static docs output:

```bash
pnpm run docs:build
```

## Installation

```bash
composer require maxiviper117/laravel-paystack-sdk
```

Publish the config file if you want to override defaults:

```bash
php artisan vendor:publish --tag="paystack-config"
```

## Configuration

Set these environment variables in your Laravel app:

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

## Usage

### Facade and manager

```php
use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;
use Maxiviper117\Paystack\Facades\Paystack;

$initialized = Paystack::initializeTransaction(
    new InitializeTransactionInputData(
        email: 'customer@example.com',
        amount: 15.50,
        callbackUrl: 'https://example.com/payments/callback',
    )
);

$verified = Paystack::verifyTransaction(
    new VerifyTransactionInputData(reference: $initialized->reference)
);
```

If you prefer explicit dependency injection, use `PaystackManager`:

```php
namespace App\Services\Billing;

use Maxiviper117\Paystack\Data\Input\Customer\CreateCustomerInputData;
use Maxiviper117\Paystack\PaystackManager;

class CreatePaystackCustomer
{
    public function __construct(
        private PaystackManager $paystack,
    ) {}

    public function handle(): void
    {
        $customer = $this->paystack->createCustomer(
            new CreateCustomerInputData(
                email: 'customer@example.com',
                firstName: 'Jane',
                lastName: 'Doe',
            )
        );
    }
}
```

### Actions for custom use

```php
namespace App\Services\Billing;

use Maxiviper117\Paystack\Actions\Customer\CreateCustomerAction;
use Maxiviper117\Paystack\Data\Input\Customer\CreateCustomerInputData;

class CreatePaystackCustomer
{
    public function __construct(
        private CreateCustomerAction $createCustomer,
    ) {}

    public function handle(): void
    {
        $customer = ($this->createCustomer)(
            new CreateCustomerInputData(
                email: 'customer@example.com',
                firstName: 'Jane',
                lastName: 'Doe',
            )
        );
    }
}
```

### Billing

```php
use Maxiviper117\Paystack\Data\Input\Plan\CreatePlanInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\CreateSubscriptionInputData;
use Maxiviper117\Paystack\Facades\Paystack;

$plan = Paystack::createPlan(
    new CreatePlanInputData(
        name: 'Starter',
        amount: 5000,
        interval: 'monthly',
    )
);

$subscription = Paystack::createSubscription(
    new CreateSubscriptionInputData(
        customer: 'CUS_123',
        plan: $plan->plan->planCode,
    )
);
```

## Implemented surface

- Transactions: initialize, verify, fetch, list
- Customers: create, update, list
- Disputes: list, fetch, transaction-specific lookup, update, evidence creation, upload URL generation, resolve, export
- Refunds: create, retry, fetch, list
- Webhooks: signature verification and generic event parsing
- Plans: create, update, fetch, list
- Subscriptions: create, fetch, list, enable, disable, generate update link, send update link

## Amount handling

`InitializeTransactionInputData` accepts an amount in major currency units and converts it to Paystack subunits during request serialization. For example, `15.50` becomes `1550`.

## Action resolution

Action classes are container-resolved services. They expose `execute(...)` and `__invoke(...)`, and both methods accept typed input DTOs and return action-specific response DTOs. For convenience-oriented usage in application code, prefer the facade or `PaystackManager`. Use actions when you want explicit custom composition.

## Webhook verification

```php
use Illuminate\Http\Request;
use Maxiviper117\Paystack\Data\Input\Webhook\VerifyWebhookSignatureInputData;
use Maxiviper117\Paystack\Facades\Paystack;

Route::post('/paystack/webhook', function (Request $request) {
    $event = Paystack::verifyWebhookSignature(
        new VerifyWebhookSignatureInputData(
            payload: $request->getContent(),
            signature: (string) $request->header('x-paystack-signature', ''),
        )
    );

    return response()->json([
        'event' => $event->event,
        'resource_type' => $event->resourceType,
    ]);
});
```

Webhook verification reuses `PAYSTACK_SECRET_KEY` to validate the `x-paystack-signature` HMAC and returns a generic typed event DTO.

## Testing

```bash
composer test
```

## Static analysis

```bash
composer analyse
```

PHPStan is configured against the package source, config, and tests so package-level regressions are caught before release.

## Automated refactoring

Rector is configured for this package with PHP-version-aware upgrades plus conservative dead-code, code-quality, and coding-style sets.

Preview changes:

```bash
composer refactor-dry
```

Apply changes:

```bash
composer refactor
```

## Roadmap

- Transfers and transfer recipients
- Broader DTO coverage for additional Paystack resources

## License

The MIT License (MIT). Please see [LICENSE.md](LICENSE.md) for more information.

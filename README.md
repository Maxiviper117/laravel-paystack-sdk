# Laravel Paystack SDK

[![Latest Version on Packagist](https://img.shields.io/packagist/v/maxiviper117/laravel-paystack-sdk.svg?style=flat-square)](https://packagist.org/packages/maxiviper117/laravel-paystack-sdk)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/Maxiviper117/laravel-paystack-sdk/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/Maxiviper117/laravel-paystack-sdk/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/Maxiviper117/laravel-paystack-sdk/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/Maxiviper117/laravel-paystack-sdk/actions?query=workflow%3A%22Fix+PHP+code+style+issues%22+branch%3Amain)

Laravel package for working with Paystack through Saloon-backed requests and action classes. The current MVP covers transactions and customers with typed DTO responses and an optional Laravel facade.

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

### Action classes

```php
use Maxiviper117\Paystack\Actions\Transaction\InitializeTransactionAction;
use Maxiviper117\Paystack\Actions\Transaction\VerifyTransactionAction;

$initialized = app(InitializeTransactionAction::class)->execute(
    email: 'customer@example.com',
    amount: 15.50,
    options: [
        'callback_url' => 'https://example.com/payments/callback',
    ],
);

$verified = app(VerifyTransactionAction::class)->execute($initialized->reference);
```

Static convenience methods are also available:

```php
use Maxiviper117\Paystack\Actions\Customer\CreateCustomerAction;

$customer = CreateCustomerAction::run('customer@example.com', [
    'first_name' => 'Jane',
    'last_name' => 'Doe',
]);
```

### Facade

```php
use Maxiviper117\Paystack\Facades\Paystack;

$response = Paystack::initializeTransaction('customer@example.com', 15.50, [
    'callback_url' => 'https://example.com/payments/callback',
]);
```

## Implemented MVP endpoints

- Transactions: initialize, verify, fetch, list
- Customers: create, update, list

## Amount handling

`InitializeTransactionAction` accepts an amount in major currency units and converts it to Paystack subunits before sending the request. For example, `15.50` becomes `1550`.

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

## Live testing with a local Laravel app

This repository includes a minimal Laravel workbench app in `workbench/` for live Paystack test-mode checks.

Install the package into the workbench app via the local path repository:

```bash
cd workbench
composer install
```

Add your Paystack test keys to `workbench/.env`:

```env
PAYSTACK_SECRET_KEY=sk_test_xxx
PAYSTACK_PUBLIC_KEY=pk_test_xxx
```

Start the app:

```bash
php artisan serve
```

Then open:

- `/paystack/test/start` to initialize a real test transaction and redirect to Paystack checkout
- `/paystack/test/callback` as the configured callback route used by the workbench live-test flow

Use Paystack's documented test cards in test mode to complete the checkout.

## Roadmap

- Webhook signature verification
- Plans and subscriptions
- Transfers and transfer recipients
- Broader DTO coverage for additional Paystack resources

## License

The MIT License (MIT). Please see [LICENSE.md](LICENSE.md) for more information.

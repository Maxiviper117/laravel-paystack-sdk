# Workbench

This directory contains a minimal Laravel app for manually testing the local `maxiviper117/laravel-paystack-sdk` package.

## Purpose

Use the workbench when you want to verify the package in a real Laravel application instead of only through mocked package tests.

The current workbench flow covers:

- initializing a Paystack test-mode transaction
- redirecting to Paystack checkout
- verifying the transaction on the callback route
- receiving Paystack webhooks through a stored and queued endpoint
- listing customers through the local package
- listing disputes through the local package
- creating and listing refunds through the local package
- creating plans through the local package
- creating subscriptions through the local package
- exercising the optional billing persistence layer against a local `users` table and mirrored billing tables
- using the dedicated search-first billing sync page to search Paystack records and sync selected customers, plans, subscriptions, transactions, refunds, and disputes into the local mirror
- opening the dedicated billing sync page at `/paystack/demo/billing-sync`
- browsing the Tailwind demo hub at `/paystack/demo`
- using the legacy playground compatibility page at `/paystack/demo/playground`
- opening the dedicated transactions page at `/paystack/demo/transactions`
- opening the dedicated customers page at `/paystack/demo/customers`
- opening the dedicated disputes page at `/paystack/demo/disputes`
- opening the dedicated refunds page at `/paystack/demo/refunds`
- opening the dedicated plans page at `/paystack/demo/plans`
- opening the dedicated subscriptions page at `/paystack/demo/subscriptions`
- opening the dedicated webhooks page at `/paystack/demo/webhooks`
- opening the dedicated billing layer page at `/paystack/demo/billing-layer` for lifecycle customer/subscription flows

## Setup

Install PHP dependencies:

```bash
composer install
```

Install frontend dependencies if you need the default Laravel assets:

```bash
pnpm install
```

Add your Paystack test keys to `.env`:

```env
PAYSTACK_SECRET_KEY=sk_test_xxx
PAYSTACK_PUBLIC_KEY=pk_test_xxx
```

Publish the webhook client and package billing migrations, then run the workbench migrations so inbound webhook calls and local billing mirrors can be stored:

```bash
php artisan vendor:publish --provider="Spatie\WebhookClient\WebhookClientServiceProvider" --tag="webhook-client-migrations"
php artisan vendor:publish --tag="paystack-migrations"
php artisan migrate
```

## Run

Start the Laravel dev server:

```bash
php artisan serve
```

If you want the workbench to run **without** the Vite dev server, use the built-assets workflow instead:

```bash
composer dev-built
```

That mode removes `public/hot`, clears stale Laravel caches, primes the config and view caches, runs Vite in build-watch mode, and serves the compiled assets directly.

The workbench intentionally does **not** route-cache or event-cache by default because the demo app still uses closure routes and a configured fluent webhook handler.

For webhook processing, also run a queue worker:

```bash
php artisan queue:work
```

Then open:

- `/paystack/demo` for the demo hub
- `/paystack/demo/playground` for the legacy compatibility page
- `/paystack/demo/transactions` for transaction demos
- `/paystack/demo/customers` for customer demos
- `/paystack/demo/disputes` for dispute demos
- `/paystack/demo/plans` for plan demos
- `/paystack/demo/subscriptions` for subscription demos
- `/paystack/demo/webhooks` for webhook inspection
- `/paystack/demo/billing-layer` for the optional billing lifecycle flow
- `/paystack/demo/billing-sync` for the search-first billing mirror sync flow
- `/paystack/test/start` to begin a real Paystack test transaction
- `/paystack/test/callback` as the callback route used by the live test flow
- `/paystack/test/webhook` for the webhook endpoint instructions
- `/paystack/test/webhook/latest-call` to inspect the most recently stored webhook call
- `/paystack/test/webhook/latest-event` to inspect the latest processed event captured by the workbench listener
- `/paystack/test/customers` to list customers with optional `per_page`, `page`, `email`, `from`, and `to` query filters
- `/paystack/test/plan` for the plan creation example route
- `/paystack/test/subscription` for the subscription creation example route
- `/paystack/test/billing-layer` for the optional billing lifecycle flow that stores Paystack customer, plan, subscription, transaction, refund, and dispute records locally

## Current integration shape

The workbench uses Laravel controllers for action-based examples so actions are injected through the container:

- `InitializeTransactionAction`
- `VerifyTransactionAction`
- `ListCustomersAction`
- `ListDisputesAction`
- `CreateRefundAction`
- `FetchRefundAction`
- `ListRefundsAction`
- `RetryRefundAction`

Outbound Paystack API usage still follows the package's current pattern:

- the `Paystack` facade and `PaystackManager` are the primary Laravel convenience layer
- action classes are container-resolved services for custom composition
- actions expose `execute(...)` and `__invoke(...)`
- actions accept typed input DTOs and return action-specific response DTOs
- response DTOs can be returned directly from Laravel routes and controllers as JSON responses
- the manager and facade use the same DTO-first methods as the underlying actions
- the optional billing layer stores Paystack customers, plans, subscriptions, transactions, refunds, and disputes in local package tables when you publish and migrate them
- billable lifecycle orchestration now goes through `PaystackManager` / `Paystack` pass-through methods, not trait remote methods
- the billing mirror treats customers as one row per billable model, but treats subscriptions as many rows per billable model keyed by `subscription_code`
- the dedicated billing sync page searches Paystack first, then uses typed DTOs to upsert the mirrored tables without going through the customer/subscription orchestration page

Webhook handling is now endpoint-first:

- register the endpoint with `Route::post('/paystack/test/webhook', 'Spatie\WebhookClient\Http\Controllers\WebhookController')->name('webhook-client-paystack')`
- let the package validate the `x-paystack-signature` header
- let the package filter requests by the documented Paystack webhook IP whitelist unless you explicitly disable it in `PAYSTACK_WEBHOOK_ALLOWED_IPS`
- inspect stored calls in `webhook_calls`
- react to `PaystackWebhookReceived` after the queued job runs

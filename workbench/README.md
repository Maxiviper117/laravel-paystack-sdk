# Workbench

This directory contains a minimal Laravel app for manually testing the local `maxiviper117/laravel-paystack-sdk` package.

## Purpose

Use the workbench when you want to verify the package in a real Laravel application instead of only through mocked package tests.

The current workbench flow covers:

- initializing a Paystack test-mode transaction
- redirecting to Paystack checkout
- verifying the transaction on the callback route
- receiving Paystack webhooks through a stored and queued endpoint
- creating plans through the local package
- creating subscriptions through the local package

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

Run the webhook client migration so the workbench can store inbound webhook calls:

```bash
php artisan migrate
```

## Run

Start the Laravel dev server:

```bash
php artisan serve
```

For webhook processing, also run a queue worker:

```bash
php artisan queue:work
```

Then open:

- `/paystack/test/start` to begin a real Paystack test transaction
- `/paystack/test/callback` as the callback route used by the live test flow
- `/paystack/test/webhook` for the webhook endpoint instructions
- `/paystack/test/webhook/latest-call` to inspect the most recently stored webhook call
- `/paystack/test/webhook/latest-event` to inspect the latest processed event captured by the workbench listener
- `/paystack/test/plan` for the plan creation example route
- `/paystack/test/subscription` for the subscription creation example route

## Current integration shape

The workbench resolves package actions from the Laravel container and uses their invokable form:

- `InitializeTransactionAction`
- `VerifyTransactionAction`

Outbound Paystack API usage still follows the package's current pattern:

- action classes are container-resolved services
- actions expose `execute(...)` and `__invoke(...)`
- actions accept typed input DTOs and return action-specific response DTOs
- convenience access for application code also exists through the package manager and facade

Webhook handling is now endpoint-first:

- register the endpoint with `Route::webhooks('/paystack/test/webhook', 'paystack')`
- let the package validate the `x-paystack-signature` header
- inspect stored calls in `webhook_calls`
- react to `PaystackWebhookReceived` after the queued job runs

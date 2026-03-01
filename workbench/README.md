# Workbench

This directory contains a minimal Laravel app for manually testing the local `maxiviper117/laravel-paystack-sdk` package.

## Purpose

Use the workbench when you want to verify the package in a real Laravel application instead of only through mocked package tests.

The current workbench flow covers:

- initializing a Paystack test-mode transaction
- redirecting to Paystack checkout
- verifying the transaction on the callback route
- verifying webhook signatures against a posted raw payload

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

## Run

Start the Laravel dev server:

```bash
php artisan serve
```

Then open:

- `/paystack/test/start` to begin a real Paystack test transaction
- `/paystack/test/callback` as the callback route used by the live test flow
- `/paystack/test/webhook` for the webhook verification example route

## Current integration shape

The workbench resolves package actions from the Laravel container and uses their invokable form:

- `InitializeTransactionAction`
- `VerifyTransactionAction`

This matches the package's current pattern:

- action classes are container-resolved services
- actions expose `execute(...)` and `__invoke(...)`
- actions accept typed input DTOs and return action-specific response DTOs
- convenience access for application code also exists through the package manager and facade

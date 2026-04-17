---
title: Laravel Paystack SDK
---

# Laravel Paystack SDK

Laravel Paystack SDK is a Laravel-native package for working with Paystack through Saloon-backed requests and a Laravel-first convenience layer.

The package currently supports:

- transactions
- customers
- disputes
- refunds
- an optional local billing layer for stored Paystack customers, plans, subscriptions, transactions, refunds, and disputes
- plans
- subscriptions
- Paystack webhook endpoints with persisted calls and queued processing

## Why this package

- Facade-first usage for application service classes and controllers
- DTO-first inputs and action-specific response DTOs
- Saloon-backed HTTP integration with package-managed retries, timeouts, and API error handling
- Laravel-native service provider, manager, and facade access for outbound Paystack operations
- optional Billable Eloquent persistence and local Paystack billing mirroring when your app wants package-owned billing tables
- Endpoint-first webhook handling powered by `spatie/laravel-webhook-client`

## Start here

- New project setup: [Getting Started](/getting-started)
- Install the package: [Installation](/installation)
- Configure credentials and transport behavior: [Configuration](/configuration)
- Follow realistic Laravel workflows: [Examples](/examples/)
- See supported features and current gaps: [Support Matrix](/support-matrix)

## Example workflows

The new examples area is a cookbook for application integrators. Start there if you want end-to-end flows instead of isolated API snippets.

- [One-Time Checkout](/examples/checkout)
- [Verify a Transaction](/examples/verify-transaction)
- [Webhook Processing](/examples/webhooks)

## Feature guides

- Transactions: [Transactions](/transactions)
- Customers: [Customers](/customers)
- Disputes: [Disputes](/disputes)
- Refunds: [Refunds](/refunds)
- Optional billing layer: [Billing Layer](/billing-layer)
- Billing plans: [Plans](/plans)
- Subscriptions: [Subscriptions](/subscriptions)
- Webhooks: [Webhooks](/webhooks)

## Package model

The public API is centered on the Laravel convenience layer and typed DTOs:

```text
Input DTO -> Facade / Manager -> Action-specific response DTO
```

For custom or advanced usage, the package also exposes injectable action classes with the same DTO-first style.

Because response DTOs extend `spatie/laravel-data`, you can also return them directly from Laravel routes and controllers when you want a built-in JSON response.

Response DTO timestamp fields such as transaction `paidAt`, subscription `nextPaymentDate`, and webhook `occurredAt` are exposed as `CarbonImmutable` in PHP and serialize to ISO-8601 strings in JSON responses.

Webhook intake is intentionally separate from the manager and facade. Incoming Paystack webhooks are received through `Route::webhooks(...)`, stored, queued, and dispatched as package events.

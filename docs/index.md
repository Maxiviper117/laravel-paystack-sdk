---
title: Laravel Paystack SDK
---

# Laravel Paystack SDK

Laravel Paystack SDK is a Laravel-native package for working with Paystack through Saloon-backed requests and Actions-first services.

The package currently supports:

- transactions
- customers
- plans
- subscriptions
- webhook signature verification

## Why this package

- Actions-first usage for application service classes and controllers
- DTO-first inputs and action-specific response DTOs
- Saloon-backed HTTP integration with package-managed retries, timeouts, and API error handling
- Laravel-native service provider, manager, and facade access

## Start here

- New project setup: [Getting Started](/getting-started)
- Install the package: [Installation](/installation)
- Configure credentials and transport behavior: [Configuration](/configuration)
- See supported features and current gaps: [Support Matrix](/support-matrix)

## Feature guides

- Transactions: [Transactions](/transactions)
- Customers: [Customers](/customers)
- Billing plans: [Plans](/plans)
- Subscriptions: [Subscriptions](/subscriptions)
- Webhook verification: [Webhooks](/webhooks)

## Package model

The public API is centered on injectable action classes and typed DTOs:

```text
Input DTO -> Action -> Action-specific response DTO
```

For convenience-oriented Laravel usage, the package also exposes `PaystackManager` and the `Paystack` facade with the same DTO-first style.

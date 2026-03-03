# Support Matrix

This page summarizes the currently implemented Paystack surface in the package.

## Supported now

### Transactions

- initialize transaction
- verify transaction
- fetch transaction
- list transactions

### Customers

- create customer
- update customer
- list customers

### Plans

- create plan
- update plan
- fetch plan
- list plans

### Subscriptions

- create subscription
- fetch subscription
- list subscriptions
- enable subscription
- disable subscription

### Shared capabilities

- Saloon-based connector configuration
- Laravel service provider and facade/manager access
- typed input DTOs
- action-specific response DTOs
- Paystack webhook endpoints with signature validation, stored webhook calls, queued processing, and typed payload resolution for selected events

## Not yet implemented

- full typed coverage for every Paystack webhook event
- subscription update-link helpers
- transfers
- transfer control
- transfer recipients
- dedicated virtual accounts
- disputes
- refunds
- bulk charges

## Maintainer reference

For the maintainer-facing matrix of exact actions, DTOs, and status notes, see [`SDK_SUPPORT.md`](https://github.com/Maxiviper117/laravel-paystack-sdk/blob/main/SDK_SUPPORT.md).

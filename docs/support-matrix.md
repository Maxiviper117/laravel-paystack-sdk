# Support Matrix

This page summarizes the currently implemented Paystack surface in the package.

## Supported now

### Transactions

- initialize transaction
- verify transaction
- fetch transaction
- list transactions
- initialize transaction input DTO covers the documented body parameters directly and still accepts `extra` for forward-compatible fields
- fetch transaction input DTO maps to the documented numeric transaction id path parameter
- list transactions input DTO covers the documented query filters including `terminalid`, `amount`, and `reference`

### Customers

- create customer
- fetch customer
- update customer
- validate customer
- set customer risk action
- list customers

### Plans

- create plan
- update plan
- fetch plan
- list plans
- update plan input DTO covers the documented body parameters directly and now includes `update_existing_subscriptions` for refreshing existing subscriptions

### Subscriptions

- create subscription
- fetch subscription
- list subscriptions
- enable subscription
- disable subscription
- generate subscription update link
- send subscription update link

### Shared capabilities

- Saloon-based connector configuration
- Laravel service provider and facade/manager access
- typed input DTOs
- action-specific response DTOs
- optional package-owned billing tables with a Billable Eloquent trait for stored Paystack customers and subscriptions
- Paystack webhook endpoints with signature validation, stored webhook calls, queued processing, and typed payload resolution for selected events

## Not yet implemented

- full typed coverage for every Paystack webhook event
- transfers
- transfer control
- transfer recipients
- dedicated virtual accounts
- disputes
- refunds
- bulk charges

## Maintainer reference

For the maintainer-facing matrix of exact actions, DTOs, and status notes, see [`SDK_SUPPORT.md`](https://github.com/Maxiviper117/laravel-paystack-sdk/blob/main/SDK_SUPPORT.md).

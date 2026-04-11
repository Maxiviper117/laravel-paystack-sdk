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

### Disputes

- list disputes
- fetch dispute
- list disputes for a transaction
- update dispute
- add dispute evidence
- get dispute upload URL
- resolve dispute
- export disputes
- dispute input DTOs cover the documented list, fetch, transaction lookup, update, evidence, upload URL, and resolution payloads directly
- export uses the same list filters so the export and list surfaces stay aligned

### Refunds

- create refund
- retry refund with customer details
- fetch refund
- list refunds
- refund input DTOs cover the documented create, retry, fetch, and list payloads directly

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
- Paystack webhook endpoints with signature validation, source IP whitelisting, stored webhook calls, queued processing, and typed payload resolution for supported charge, dispute, customer identification, dedicated account assignment, invoice, payment request, refund, subscription, and transfer events
- implemented and fully covered typed support for every webhook event sample currently included in `reference/webhook_events`

## Not yet implemented

- transfers
- transfer control
- transfer recipients
- dedicated virtual accounts
- bulk charges

## Maintainer reference

For the maintainer-facing matrix of exact actions, DTOs, and status notes, see [`SDK_SUPPORT.md`](https://github.com/Maxiviper117/laravel-paystack-sdk/blob/main/SDK_SUPPORT.md).

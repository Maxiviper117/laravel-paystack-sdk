# Billing Layer Architecture Notes

This note tracks the billing-layer boundary after the lifecycle refactor.

## Current billing layer shape

The optional billing layer covers:

- model-centric `Billable` trait concerns:
  - Eloquent relations and lookups for mirrored records
  - local sync helpers for plans, subscriptions, transactions, refunds, and disputes
  - customer sync entrypoint (`syncPaystackCustomer`)
- billable lifecycle orchestration services:
  - customer lifecycle service (create, update, sync)
  - subscription lifecycle service (create, fetch, enable, disable)
- manager/facade pass-through methods for lifecycle orchestration
- webhook reconciliation into mirrored tables after validated delivery

## Intentional boundaries

The billing layer intentionally does not expose wrappers for all supported SDK endpoints.

General Paystack endpoint operations stay in action classes and DTO-first manager/facade methods.

This is deliberate: trait remains thin, orchestration lives in `src/Billing`, and all endpoint breadth remains in the core SDK surface.

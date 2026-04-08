# SDK Support Matrix

This document tracks the Paystack endpoints and SDK features currently supported by this package.

Use it as the maintainer reference for:

- what is implemented now
- what public SDK surface exists for each supported feature
- what is planned but not yet implemented

Keep this file aligned with the actual code in `src/`, tests, and package docs.

## Current status

### Transactions

| Paystack area | Endpoint / capability | Status | Public SDK surface |
| --- | --- | --- | --- |
| Transactions | Initialize transaction | Supported | `InitializeTransactionAction`, `InitializeTransactionInputData`, `InitializeTransactionResponseData` |
| Transactions | Verify transaction | Supported | `VerifyTransactionAction`, `VerifyTransactionInputData`, `VerifyTransactionResponseData` |
| Transactions | Fetch transaction | Supported | `FetchTransactionAction`, `FetchTransactionInputData`, `FetchTransactionResponseData` |
| Transactions | List transactions | Supported | `ListTransactionsAction`, `ListTransactionsInputData`, `ListTransactionsResponseData` |

### Customers

| Paystack area | Endpoint / capability | Status | Public SDK surface |
| --- | --- | --- | --- |
| Customers | Create customer | Supported | `CreateCustomerAction`, `CreateCustomerInputData`, `CreateCustomerResponseData` |
| Customers | Update customer | Supported | `UpdateCustomerAction`, `UpdateCustomerInputData`, `UpdateCustomerResponseData` |
| Customers | List customers | Supported | `ListCustomersAction`, `ListCustomersInputData`, `ListCustomersResponseData` |

### Plans

| Paystack area | Endpoint / capability | Status | Public SDK surface |
| --- | --- | --- | --- |
| Plans | Create plan | Supported | `CreatePlanAction`, `CreatePlanInputData`, `CreatePlanResponseData` |
| Plans | Update plan | Supported | `UpdatePlanAction`, `UpdatePlanInputData`, `UpdatePlanResponseData` |
| Plans | Fetch plan | Supported | `FetchPlanAction`, `FetchPlanInputData`, `FetchPlanResponseData` |
| Plans | List plans | Supported | `ListPlansAction`, `ListPlansInputData`, `ListPlansResponseData` |

### Subscriptions

| Paystack area | Endpoint / capability | Status | Public SDK surface |
| --- | --- | --- | --- |
| Subscriptions | Create subscription | Supported | `CreateSubscriptionAction`, `CreateSubscriptionInputData`, `CreateSubscriptionResponseData` |
| Subscriptions | Fetch subscription | Supported | `FetchSubscriptionAction`, `FetchSubscriptionInputData`, `FetchSubscriptionResponseData` |
| Subscriptions | List subscriptions | Supported | `ListSubscriptionsAction`, `ListSubscriptionsInputData`, `ListSubscriptionsResponseData` |
| Subscriptions | Enable subscription | Supported | `EnableSubscriptionAction`, `EnableSubscriptionInputData`, `EnableSubscriptionResponseData` |
| Subscriptions | Disable subscription | Supported | `DisableSubscriptionAction`, `DisableSubscriptionInputData`, `DisableSubscriptionResponseData` |

## Shared SDK capabilities

| Capability | Status | Notes |
| --- | --- | --- |
| Saloon-based connector | Supported | `PaystackConnector` handles base URL, auth, timeouts, retries, and API error behavior. |
| Laravel service provider | Supported | Auto-discovered package provider with config publishing. |
| Facade / manager API | Supported | `Paystack` facade resolves `PaystackManager`, now with DTO-first method signatures. |
| Optional billing layer | Supported | `Maxiviper117\Paystack\Concerns\Billable` plus `PaystackCustomer` and `PaystackSubscription` provide package-owned local persistence for apps that choose to publish the billing migrations. |
| Typed input DTOs | Supported | Input DTOs live under `src/Data/Input`. |
| Action-specific response DTOs | Supported | Response DTOs live under `src/Data/Output`. |
| Webhook intake and processing | Supported | Uses `spatie/laravel-webhook-client` with Paystack-specific signature validation, stored webhook calls, queued event dispatch, and typed payload resolution for selected events. |
| Live test workbench | Supported | `workbench/` tracks the current package integration style for manual Paystack test-mode checks. |

## Not yet implemented

These areas are planned or likely future work, but they are not currently supported by the SDK.

### Paystack resources

| Paystack area | Endpoint / capability | Status | Notes |
| --- | --- | --- | --- |
| Webhooks | Typed event-specific DTO mapping | Partially supported | `PaystackWebhookEventData` now exposes typed DTO resolution for `charge.success`, `invoice.create`, `invoice.update`, `invoice.payment_failed`, `subscription.create`, `subscription.not_renew`, and `subscription.disable`. Unsupported events still use the generic envelope. |
| Subscriptions | Update link generation and sending helpers | Not started | Core subscription lifecycle is supported; update-link endpoints are still missing. |
| Transfers | Initiate / finalize / verify / list / fetch / bulk transfer | Not started | No actions, DTOs, or requests yet. |
| Transfer control | Check balance / resend OTP / disable OTP / finalize disable OTP / enable OTP | Not started | No actions, DTOs, or requests yet. |
| Transfer recipients | Create / bulk create / update / delete / list / fetch | Not started | No actions, DTOs, or requests yet. |
| Dedicated virtual accounts | Account lifecycle and split-management endpoints | Not started | No actions, DTOs, or requests yet. |
| Disputes | List / fetch / update / evidence flows | Not started | No actions, DTOs, or requests yet. |
| Refunds | Create / list / fetch / retry | Not started | No actions, DTOs, or requests yet. |
| Bulk charges | Create / list / fetch / pause / resume | Not started | No actions, DTOs, or requests yet. |

## Update rules

Update this document whenever any of the following changes:

- a Paystack endpoint is added or removed
- a supported feature changes status
- an action, input DTO, output DTO, request, or manager/facade method is renamed or replaced
- the workbench live-test coverage changes
- roadmap items move into implemented support

This file should describe the package as it actually exists, not as intended future architecture.

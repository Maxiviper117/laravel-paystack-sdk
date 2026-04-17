# Examples

This cookbook shows realistic Laravel integration flows for the package. Use these pages when you want to see how the SDK fits into controllers, services, listeners, and billing workflows instead of reading feature-by-feature reference docs.

## Choose a workflow

- [One-Time Checkout](/examples/checkout): initialize a Paystack transaction from your app, return the authorization URL, and redirect the customer safely.
- [Verify a Transaction](/examples/verify-transaction): verify a Paystack reference after callback or during an internal reconciliation flow.
- [Manage Customers](/examples/customers): create and update Paystack customers while keeping your local customer records in sync.
- [Optional Billing Layer](/examples/billing-layer): use the package-owned billing tables and `Billable` trait when you want Cashier-style local persistence without giving up the Laravel-first SDK.
- [Subscription Billing Flow](/examples/subscriptions): create plans, create subscriptions, and understand where webhooks fit into recurring billing.
- [Webhook Processing](/examples/webhooks): register the endpoint, process incoming Paystack events, and use typed webhook payloads.
- [Manager and Facade Usage](/examples/manager-and-facade): use the `Paystack` facade or `PaystackManager` first, with actions available for custom use.

## Laravel-specific examples

- [API Resources](/examples/api-resources): transform Paystack data into consistent JSON responses with amount formatting and conditional fields.
- [Artisan Commands](/examples/artisan-commands): CLI commands for syncing customers, verifying transactions, and managing subscriptions.
- [Attribute Casting](/examples/attribute-casting): custom casts for kobo-to-currency conversion, money value objects, and immutable amounts.
- [Blade Components](/examples/blade-components): reusable UI components for payment status, transaction history, and checkout buttons.
- [Broadcasting and Events](/examples/broadcasting): real-time payment status updates via Laravel Echo and WebSocket broadcasting.
- [Caching Paystack Data](/examples/caching): reduce API calls by caching plans, customers, and verification results with appropriate TTLs.
- [Custom Validation Rules](/examples/custom-validation-rules): reusable rules for Paystack references, plan codes, amounts, and currencies.
- [Database Transactions](/examples/database-transactions): wrap payment operations and local DB changes in transactions for consistency.
- [Eloquent Observers](/examples/eloquent-observers): auto-sync to Paystack when models change (email updates, profile changes, soft deletes).
- [Error Handling](/examples/error-handling): graceful degradation, retry logic, circuit breakers, and global exception handling.
- [Event Listeners](/examples/event-listeners): respond to Paystack webhook events with typed callbacks for charges, subscriptions, refunds, and disputes.
- [Export and Reports](/examples/export-reports): CSV/Excel generation for accounting, reconciliation, and business intelligence.
- [Form Request Validation](/examples/form-requests): validate payment, subscription, and refund forms with business rules.
- [Middleware](/examples/middleware): protect payment routes, rate-limit checkout, verify reference formats, and audit-log access.
- [Payment Notifications](/examples/notifications): send email and Slack alerts for payment events and subscription reminders.
- [Policies and Authorization](/examples/policies): control who can initiate payments, process refunds, manage subscriptions, and view transactions.
- [Query Scopes](/examples/query-scopes): reusable filters for payments, subscriptions, and refunds (status, date ranges, amounts).
- [Queued Jobs](/examples/queued-jobs): process payments asynchronously, handle bulk operations, and retry failed requests.
- [Scheduled Tasks](/examples/scheduled-tasks): automated reconciliation, cleanup, renewal reminders, and daily reports.
- [Service Container Binding](/examples/service-container): multi-tenant configurations, feature flags, and custom connector bindings.
- [Testing Paystack Integrations](/examples/testing): fake the Paystack facade, assert method calls, test webhook handlers, and verify authorization rules.

## How the examples are written

- primary examples prefer the `Paystack` facade or `PaystackManager` inside small Laravel services and listeners
- action-based alternatives are included when they help with explicit custom composition
- webhook examples reflect the current endpoint-first Spatie integration and typed webhook payload support

## Related reference pages

- [Transactions](/transactions)
- [Customers](/customers)
- [Plans](/plans)
- [Billing Layer](/billing-layer)
- [Subscriptions](/subscriptions)
- [Webhooks](/webhooks)

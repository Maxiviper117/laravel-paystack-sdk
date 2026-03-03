# Examples

This cookbook shows realistic Laravel integration flows for the package. Use these pages when you want to see how the SDK fits into controllers, services, listeners, and billing workflows instead of reading feature-by-feature reference docs.

## Choose a workflow

- [One-Time Checkout](/examples/checkout): initialize a Paystack transaction from your app, return the authorization URL, and redirect the customer safely.
- [Verify a Transaction](/examples/verify-transaction): verify a Paystack reference after callback or during an internal reconciliation flow.
- [Manage Customers](/examples/customers): create and update Paystack customers while keeping your local customer records in sync.
- [Subscription Billing Flow](/examples/subscriptions): create plans, create subscriptions, and understand where webhooks fit into recurring billing.
- [Webhook Processing](/examples/webhooks): register the endpoint, process incoming Paystack events, and use typed webhook payloads.
- [Manager and Facade Usage](/examples/manager-and-facade): choose between injected actions, `PaystackManager`, and the `Paystack` facade.

## How the examples are written

- primary examples prefer injected actions inside small Laravel services and listeners
- facade-based alternatives are included when they help with concise adoption
- webhook examples reflect the current endpoint-first Spatie integration and typed webhook payload support

## Related reference pages

- [Transactions](/transactions)
- [Customers](/customers)
- [Plans](/plans)
- [Subscriptions](/subscriptions)
- [Webhooks](/webhooks)

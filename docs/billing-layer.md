# Billing Layer

The package now includes an optional local billing mirror for Laravel apps that want package-owned tables for stored Paystack customers, plans, subscriptions, transactions, refunds, and disputes.

This layer is opt-in:

- if you only want the SDK, keep using the facade, manager, DTOs, and actions for custom work
- if you want local persistence similar to a lightweight Cashier-style workflow, publish the package migrations and use the `Billable` trait on your Eloquent model
- if you want customer and subscription lifecycle helpers for a billable model, use `PaystackManager` or the `Paystack` facade

## When To Use

Use the billing layer when your app needs both:

- a remote Paystack customer or subscription lifecycle
- a local mirror of the Paystack records that drive app-side billing state

Do not use it as a replacement for the full SDK. Other Paystack endpoints still live in the facade, manager, DTOs, and actions for custom work.

## What it provides

- a `Maxiviper117\Paystack\Concerns\Billable` trait for app models such as `User` or `Account`
- local tables and models for `paystack_customers`, `paystack_plans`, `paystack_subscriptions`, `paystack_transactions`, `paystack_refunds`, and `paystack_disputes`
- lifecycle convenience methods on `PaystackManager` / `Paystack` for billable customer and subscription flows
- webhook reconciliation that upserts mirrored rows after Paystack signature and source IP validation

## How It Fits

The billing layer is split across three responsibilities:

- `Billable` gives your model local relations, local lookups, and local mirror sync helpers
- `PaystackManager` and `Paystack` expose customer and subscription lifecycle orchestration
- the webhook listener keeps mirrored rows current after validated Paystack delivery

That split keeps the trait thin and keeps remote API logic in the service layer.

## Install the optional tables

```bash
php artisan vendor:publish --tag="paystack-migrations"
php artisan migrate
```

## Add the trait to your model

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Maxiviper117\Paystack\Concerns\Billable;

class User extends Authenticatable
{
    use Billable;
}
```

By default, billable customer lifecycle methods read `email`, `first_name`, `last_name`, and `phone` from the model. If your model uses different fields, pass an explicit customer DTO to `PaystackManager` or the `Paystack` facade.

## Lifecycle API

Customer lifecycle:

- `Paystack::createBillableCustomer($user, ?CreateCustomerInputData $input = null)`: creates and mirrors a remote customer
- `Paystack::updateBillableCustomer($user, ?UpdateCustomerInputData $input = null)`: updates and mirrors the stored remote customer
- `Paystack::syncBillableCustomer($user)`: creates or updates based on existing mirrored state

Subscription lifecycle:

- `Paystack::createBillableSubscription($user, $planCode, $name = 'default')`: creates and mirrors a remote subscription
- `Paystack::fetchBillableSubscription($user, $idOrCode, $name = 'default')`: fetches and refreshes a mirrored subscription
- `Paystack::enableBillableSubscription($user, $name = 'default')`: enables a stored mirrored subscription using local code and token
- `Paystack::disableBillableSubscription($user, $name = 'default')`: disables a stored mirrored subscription using local code and token

Local mirror helpers:

- `syncPaystackCustomer()`: syncs local mirrored customer state for the current model through the billing lifecycle service
- `syncPaystackPlan(PlanData $plan)`: persists or refreshes a local plan record
- `syncPaystackSubscription(SubscriptionData $subscription, string $name = 'default')`: persists or refreshes a local subscription mirror
- `syncPaystackTransaction(TransactionData $transaction)`: persists or refreshes a local transaction mirror and links the current billable model
- `syncPaystackRefund(RefundData $refund)`: persists or refreshes a local refund mirror and links the current billable model
- `syncPaystackDispute(DisputeData $dispute)`: persists or refreshes a local dispute mirror and links the current billable model

The package also registers a webhook reconciliation listener that keeps the mirrored tables in sync after validated webhook events are stored and processed.

## Example

```php
use App\Models\User;
use Maxiviper117\Paystack\Facades\Paystack;

$user = User::query()->findOrFail($id);

$customer = Paystack::syncBillableCustomer($user);

$subscription = Paystack::createBillableSubscription(
    billable: $user,
    planCode: 'PLN_growth',
    name: 'default',
);
```

If you prefer injected services, use `PaystackManager` the same way:

```php
use App\Models\User;
use Maxiviper117\Paystack\PaystackManager;

class StartBillingFlow
{
    public function __construct(
        private PaystackManager $paystack,
    ) {}

    public function handle(User $user): void
    {
        $this->paystack->syncBillableCustomer($user);
        $this->paystack->createBillableSubscription($user, 'PLN_growth');
    }
}
```

## Important boundaries

- this is a convenience layer, not a replacement for the Laravel-first SDK
- webhook handling still matters for authoritative recurring billing lifecycle updates
- local tables store identifiers, lifecycle fields, and snapshots; your app still owns entitlement rules, access control, and domain-specific billing state
- `Billable` stays model-centric; general Paystack operations stay in actions and DTO-first manager methods

## Related pages

- [Manage Customers](/examples/customers)
- [Optional Billing Layer](/examples/billing-layer)
- [Subscription Billing Flow](/examples/subscriptions)
- [Webhooks](/webhooks)

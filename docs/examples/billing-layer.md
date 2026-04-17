# Optional Billing Layer

Use this flow when you want the package to keep local Paystack customer, plan, subscription, transaction, refund, and dispute records for an Eloquent model while still using the Laravel-first SDK under the hood.

## Why This Flow

Use the billing layer when you want:

- an application-owned local mirror of Paystack billing records
- a model-friendly way to start or sync customer and subscription lifecycle flows
- webhook reconciliation to keep the local mirror aligned with Paystack

Keep using the regular facade, manager, or actions for everything outside that billing lifecycle. The billing layer is narrow by design.

## Typical flow

1. Publish and run the package billing migrations.
2. Add `Maxiviper117\Paystack\Concerns\Billable` to your model.
3. Sync the remote Paystack customer from the local model through `PaystackManager` / `Paystack` lifecycle methods.
4. Create or fetch subscriptions through the billing lifecycle methods.
5. Use the mirrored transaction, refund, and dispute helpers when you want local operational records in addition to the remote Paystack state.
6. Use webhooks to keep your own application billing state correct over time.

## Relationship Between Layers

- `Billable` handles local relations and local mirror helpers
- `PaystackManager` and the `Paystack` facade handle customer and subscription lifecycle orchestration
- actions handle the rest of the Paystack API surface when you need custom composition
- webhooks keep mirrored records current after validated delivery

## Setup

```bash
php artisan vendor:publish --tag="paystack-migrations"
php artisan migrate
```

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Maxiviper117\Paystack\Concerns\Billable;

class User extends Authenticatable
{
    use Billable;
}
```

## Customer sync example

```php
namespace App\Services\Billing;

use App\Models\User;
use Maxiviper117\Paystack\Facades\Paystack;

class SyncBillableCustomer
{
    public function handle(User $user): string
    {
        $customer = Paystack::syncBillableCustomer($user);

        return (string) $customer->customer_code;
    }
}
```

If your model uses non-default attribute names, pass a customer DTO into the lifecycle method:

```php
use App\Models\User;
use Maxiviper117\Paystack\Data\Input\Customer\CreateCustomerInputData;
use Maxiviper117\Paystack\Facades\Paystack;

$user = User::query()->findOrFail($id);

$response = Paystack::createBillableCustomer(
    billable: $user,
    input: CreateCustomerInputData::from([
        'email' => $user->billing_email,
        'firstName' => $user->given_name,
        'lastName' => $user->family_name,
    ]),
);
```

## Subscription example

```php
namespace App\Services\Billing;

use App\Models\User;
use Maxiviper117\Paystack\Facades\Paystack;

class StartStoredSubscription
{
    public function handle(User $user): string
    {
        $response = Paystack::createBillableSubscription(
            billable: $user,
            planCode: 'PLN_growth',
            name: 'primary',
        );

        return $response->subscription->subscriptionCode;
    }
}
```

## What gets stored locally

- one `paystack_customers` row per billable model
- one named `paystack_subscriptions` row per billable subscription slot
- mirrored `paystack_plans`, `paystack_transactions`, `paystack_refunds`, and `paystack_disputes` rows as the app creates or receives Paystack activity
- Paystack identifiers such as `customer_code`, `subscription_code`, `plan_code`, `reference`, and snapshot payload data
- webhook-sourced updates for the mirrored rows after Paystack validates the event source and signature

## Notes

- if your model does not use the default `email`, `first_name`, `last_name`, or `phone` attributes, pass explicit customer DTOs to the lifecycle methods
- enabling or disabling a stored subscription requires an `email_token`, so create or fetch the subscription before trying to toggle it later
- the package webhook listener can reconcile mirrored records after valid webhook delivery, but you can still call the sync helpers directly when you want tighter control
- use the facade or manager for lifecycle orchestration; keep the trait for model relations and local mirror helpers

## Related pages

- [Billing Layer](/billing-layer)
- [Subscription Billing Flow](/examples/subscriptions)
- [Webhook Processing](/examples/webhooks)

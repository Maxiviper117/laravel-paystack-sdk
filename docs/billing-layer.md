# Billing Layer

The package now includes an optional local billing layer for Laravel apps that want package-owned tables for stored Paystack customers and subscriptions.

This layer is opt-in:

- if you only want the SDK, keep using actions, DTOs, the manager, and the facade
- if you want local persistence similar to a lightweight Cashier-style workflow, publish the package migrations and use the `Billable` trait on your Eloquent model

## What it provides

- a `Maxiviper117\Paystack\Concerns\Billable` trait for app models such as `User` or `Account`
- a `paystack_customers` table backed by `Maxiviper117\Paystack\Models\PaystackCustomer`
- a `paystack_subscriptions` table backed by `Maxiviper117\Paystack\Models\PaystackSubscription`
- convenience methods that still delegate to `PaystackManager` and the existing action classes under the hood

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

By default, the trait reads the local `email`, `first_name`, `last_name`, and `phone` attributes when it needs to create or update the remote Paystack customer. If your model uses different field names, override the protected helper methods exposed by the trait.

## Common methods

- `createAsPaystackCustomer()`: creates the remote Paystack customer and persists a local `paystack_customers` row
- `updateAsPaystackCustomer()`: updates the remote customer using the stored customer code
- `syncAsPaystackCustomer()`: creates or updates the customer depending on whether a local record already exists
- `createPaystackSubscription($planCode, $name = 'default')`: creates the remote subscription and persists a local `paystack_subscriptions` row
- `fetchPaystackSubscription($idOrCode, $name = 'default')`: fetches a remote subscription and refreshes the local stored record
- `enablePaystackSubscription($name = 'default')`: enables a stored subscription using its local code and email token
- `disablePaystackSubscription($name = 'default')`: disables a stored subscription using its local code and email token

## Example

```php
use App\Models\User;

$user = User::query()->findOrFail($id);

$customer = $user->syncAsPaystackCustomer();

$subscription = $user->createPaystackSubscription(
    planCode: 'PLN_growth',
    name: 'default',
);
```

## Important boundaries

- this is a convenience layer, not a replacement for the action-first SDK
- webhook handling still matters for authoritative recurring billing lifecycle updates
- local tables store identifiers and snapshots; your app still owns entitlement rules, access control, and domain-specific billing state

## Related pages

- [Manage Customers](/examples/customers)
- [Optional Billing Layer](/examples/billing-layer)
- [Subscription Billing Flow](/examples/subscriptions)
- [Webhooks](/webhooks)

# Optional Billing Layer

Use this flow when you want the package to keep local Paystack customer and subscription records for an Eloquent model while still using the existing action-first SDK under the hood.

## Typical flow

1. Publish and run the package billing migrations.
2. Add `Maxiviper117\Paystack\Concerns\Billable` to your model.
3. Sync the remote Paystack customer from the local model.
4. Create or fetch subscriptions through the Billable helper methods.
5. Use webhooks to keep your own application billing state correct over time.

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

class SyncBillableCustomer
{
    public function handle(User $user): string
    {
        $customer = $user->syncAsPaystackCustomer();

        return (string) $customer->customer_code;
    }
}
```

## Subscription example

```php
namespace App\Services\Billing;

use App\Models\User;

class StartStoredSubscription
{
    public function handle(User $user): string
    {
        $response = $user->createPaystackSubscription(
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
- Paystack identifiers such as `customer_code`, `subscription_code`, `email_token`, `plan_code`, and snapshot payload data

## Notes

- if your model does not use the default `email`, `first_name`, `last_name`, or `phone` attributes, override the trait helper methods
- enabling or disabling a stored subscription requires an `email_token`, so create or fetch the subscription before trying to toggle it later
- keep using webhooks for long-running subscription lifecycle changes

## Related pages

- [Billing Layer](/billing-layer)
- [Subscription Billing Flow](/examples/subscriptions)
- [Webhook Processing](/examples/webhooks)

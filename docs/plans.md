# Plans

Plans currently support create, update, fetch, and list operations.

The update plan action now also supports Paystack's `update_existing_subscriptions` body parameter.

## Create a plan

```php
use Maxiviper117\Paystack\Data\Input\Plan\CreatePlanInputData;
use Maxiviper117\Paystack\Facades\Paystack;

$response = Paystack::createPlan(
    CreatePlanInputData::from([
        'name' => 'Starter',
        'amount' => 5000,
        'interval' => 'monthly',
    ])
);
```

## Other supported plan actions

- `UpdatePlanAction`
- `FetchPlanAction`
- `ListPlansAction`

Matching DTOs:

- `UpdatePlanInputData`
- `FetchPlanInputData`
- `ListPlansInputData`
- `UpdatePlanResponseData`
- `FetchPlanResponseData`
- `ListPlansResponseData`

## When to use plans

Plans are the billing definition used by Paystack subscriptions. Create or fetch a plan first, then use its plan code when creating a subscription.

## Need a workflow example?

- [Subscription Billing Flow](/examples/subscriptions)

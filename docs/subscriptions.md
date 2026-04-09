# Subscriptions

Subscriptions currently support create, fetch, list, enable, disable, and subscription update-link operations.

## Create a subscription

```php
use Maxiviper117\Paystack\Data\Input\Subscription\CreateSubscriptionInputData;
use Maxiviper117\Paystack\Facades\Paystack;

$response = Paystack::createSubscription(
    new CreateSubscriptionInputData(
        customer: 'CUS_123',
        plan: 'PLN_123',
    )
);
```

## Other supported subscription actions

- `FetchSubscriptionAction`
- `ListSubscriptionsAction`
- `EnableSubscriptionAction`
- `DisableSubscriptionAction`
- `GenerateSubscriptionUpdateLinkAction`
- `SendSubscriptionUpdateLinkAction`

Matching DTOs:

- `FetchSubscriptionInputData`
- `ListSubscriptionsInputData`
- `EnableSubscriptionInputData`
- `DisableSubscriptionInputData`
- `GenerateSubscriptionUpdateLinkInputData`
- `SendSubscriptionUpdateLinkInputData`
- `FetchSubscriptionResponseData`
- `ListSubscriptionsResponseData`
- `EnableSubscriptionResponseData`
- `DisableSubscriptionResponseData`
- `GenerateSubscriptionUpdateLinkResponseData`
- `SendSubscriptionUpdateLinkResponseData`

Subscription response DTOs use the backed `SubscriptionStatus` enum for the documented values `active`, `non-renewing`, `attention`, `completed`, and `cancelled`.

## Relationship to plans

Subscriptions depend on an existing Paystack plan. A common flow is:

1. Create or fetch a plan.
2. Use the plan code in `CreateSubscriptionInputData`.
3. Manage the subscription lifecycle through the subscription actions.
4. Generate or email a card-update link when the customer needs to refresh their subscription authorization.

## Need a workflow example?

- [Subscription Billing Flow](/examples/subscriptions)
- [Webhook Processing](/examples/webhooks)

# Subscription Billing Flow

Use this flow when your app needs to create or reuse a Paystack plan, attach a Paystack customer, and create a recurring subscription.

## Typical application flow

1. Create or fetch the billing plan you want to sell.
2. Create the Paystack customer if your app has not synced one yet.
3. Create the subscription using the Paystack customer code and plan code.
4. Use webhooks to track asynchronous subscription lifecycle changes.

## Preferred service-based example

```php
namespace App\Services\Billing;

use App\Models\Account;
use Maxiviper117\Paystack\Actions\Plan\CreatePlanAction;
use Maxiviper117\Paystack\Actions\Subscription\CreateSubscriptionAction;
use Maxiviper117\Paystack\Actions\Subscription\GenerateSubscriptionUpdateLinkAction;
use Maxiviper117\Paystack\Data\Input\Plan\CreatePlanInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\CreateSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\GenerateSubscriptionUpdateLinkInputData;

class StartSubscriptionBilling
{
    public function __construct(
    private SyncPaystackCustomer $syncPaystackCustomer,
    private CreatePlanAction $createPlan,
    private CreateSubscriptionAction $createSubscription,
    private GenerateSubscriptionUpdateLinkAction $generateSubscriptionUpdateLink,
) {}

    public function handle(Account $account): string
    {
        $customerCode = $account->paystack_customer_code
            ?? $this->syncPaystackCustomer->create($account);

        $planResponse = ($this->createPlan)(
            new CreatePlanInputData(
                name: 'Starter Monthly',
                amount: 49.99,
                interval: 'monthly',
                description: 'Starter plan billed monthly',
            )
        );

        $subscriptionResponse = ($this->createSubscription)(
            new CreateSubscriptionInputData(
                customer: $customerCode,
                plan: $planResponse->plan->planCode,
            )
        );

        $updateLink = ($this->generateSubscriptionUpdateLink)(
            new GenerateSubscriptionUpdateLinkInputData(
                code: $subscriptionResponse->subscription->subscriptionCode,
            )
        );

        return $updateLink->link;
    }
}
```

## Fetch an existing plan instead of creating one

If your plans are long-lived and already exist in Paystack, fetch them by ID or plan code before creating the subscription:

```php
use Maxiviper117\Paystack\Actions\Plan\FetchPlanAction;
use Maxiviper117\Paystack\Data\Input\Plan\FetchPlanInputData;

$fetchPlan = app(FetchPlanAction::class);

$plan = $fetchPlan(
    new FetchPlanInputData(idOrCode: 'PLN_123')
)->plan;
```

## Facade alternative

```php
use Maxiviper117\Paystack\Data\Input\Plan\CreatePlanInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\CreateSubscriptionInputData;
use Maxiviper117\Paystack\Facades\Paystack;

$plan = Paystack::createPlan(
    new CreatePlanInputData(
        name: 'Starter Monthly',
        amount: 49.99,
        interval: 'monthly',
    )
)->plan;

$subscription = Paystack::createSubscription(
    new CreateSubscriptionInputData(
        customer: $customerCode,
        plan: $plan->planCode,
    )
)->subscription;
```

## Notes

- plans define billing rules; they are not subscriptions themselves
- store the Paystack `planCode` and `subscriptionCode` separately
- recurring lifecycle updates should be driven by webhooks, not by synchronous assumptions after creation
- use the subscription update-link helpers when a customer needs to refresh their card details

## Next steps

- [Webhook Processing](/examples/webhooks)
- [Plans](/plans)
- [Subscriptions](/subscriptions)

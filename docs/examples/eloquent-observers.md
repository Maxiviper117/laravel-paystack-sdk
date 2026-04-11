# Eloquent Observers

Use this flow when you need to automatically sync local model changes to Paystack. Laravel's Eloquent observers let you react to model lifecycle events and keep Paystack in sync without manual intervention.

## Why Use Observers for Paystack Sync

- **Automatic sync** — Changes propagate to Paystack without explicit calls
- **Consistency** — Local and remote state stay aligned
- **DRY** — Sync logic lives in one place, not scattered through controllers
- **Background processing** — Queue heavy sync operations automatically
- **Audit trail** — Track what was synced and when

## Typical Observer Scenarios

1. **Customer email updates** — Sync new email to Paystack when user changes it
2. **Profile changes** — Update customer metadata when profile is modified
3. **Soft deletes** — Disable subscriptions when user is soft deleted
4. **Plan changes** — Update Paystack plan when local plan config changes
5. **Address updates** — Sync billing address changes to customer record

## Customer Email Sync Observer

When a user changes their email, update their Paystack customer record:

```php
<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Maxiviper117\Paystack\Data\Input\Customer\UpdateCustomerInputData;
use Maxiviper117\Paystack\Facades\Paystack;

class UserObserver
{
    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Check if email actually changed
        if (! $user->wasChanged('email')) {
            return;
        }

        // Check if user has a Paystack customer record
        if ($user->paystackCustomer === null) {
            return;
        }

        try {
            Paystack::updateCustomer(
                UpdateCustomerInputData::from([
                    'codeOrEmailOrId' => $user->paystackCustomer->customer_code,
                    'email' => $user->email,
                ])
            );

            Log::info('Synced user email change to Paystack', [
                'user_id' => $user->id,
                'customer_code' => $user->paystackCustomer->customer_code,
                'new_email' => $user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to sync email change to Paystack', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            // Optionally queue for retry
            \App\Jobs\SyncCustomerToPaystack::dispatch($user)
                ->delay(now()->addMinutes(5));
        }
    }

    /**
     * Handle the User "deleted" event (soft delete).
     */
    public function deleted(User $user): void
    {
        // Cancel active subscriptions when user is deleted
        if ($user->paystackCustomer === null) {
            return;
        }

        $activeSubscriptions = $user->paystackSubscriptions()
            ->where('status', 'active')
            ->get();

        foreach ($activeSubscriptions as $subscription) {
            try {
                Paystack::disableSubscription(
                    \Maxiviper117\Paystack\Data\Input\Subscription\DisableSubscriptionInputData::from([
                        'code' => $subscription->subscription_code,
                        'token' => $subscription->email_token,
                    ])
                );

                $subscription->update(['status' => 'cancelled']);
            } catch (\Exception $e) {
                Log::error('Failed to cancel subscription on user deletion', [
                    'user_id' => $user->id,
                    'subscription_code' => $subscription->subscription_code,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
```

**Register the observer in a service provider:**

```php
<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        User::observe(UserObserver::class);
    }
}
```

## Plan Configuration Observer

When plan settings change locally, update the Paystack plan:

```php
<?php

namespace App\Observers;

use App\Models\Plan;
use Illuminate\Support\Facades\Log;
use Maxiviper117\Paystack\Data\Input\Plan\UpdatePlanInputData;
use Maxiviper117\Paystack\Facades\Paystack;

class PlanObserver
{
    /**
     * Handle the Plan "updated" event.
     */
    public function updated(Plan $plan): void
    {
        // Only sync if Paystack-related fields changed
        $syncFields = ['name', 'amount', 'interval', 'description'];

        if (! $plan->wasChanged($syncFields)) {
            return;
        }

        if ($plan->paystack_plan_code === null) {
            return;
        }

        try {
            $updateData = [
                'idOrCode' => $plan->paystack_plan_code,
            ];

            if ($plan->wasChanged('name')) {
                $updateData['name'] = $plan->name;
            }

            if ($plan->wasChanged('amount')) {
                $updateData['amount'] = $plan->amount;
            }

            if ($plan->wasChanged('description')) {
                $updateData['description'] = $plan->description;
            }

            Paystack::updatePlan(UpdatePlanInputData::from($updateData));

            Log::info('Synced plan changes to Paystack', [
                'plan_id' => $plan->id,
                'paystack_plan_code' => $plan->paystack_plan_code,
                'changed_fields' => array_keys($plan->getChanges()),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to sync plan changes to Paystack', [
                'plan_id' => $plan->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Plan "created" event.
     */
    public function created(Plan $plan): void
    {
        // Optionally auto-create plan in Paystack
        if (! config('paystack.auto_create_plans')) {
            return;
        }

        try {
            $response = Paystack::createPlan(
                \Maxiviper117\Paystack\Data\Input\Plan\CreatePlanInputData::from([
                    'name' => $plan->name,
                    'amount' => $plan->amount,
                    'interval' => $plan->interval,
                    'description' => $plan->description,
                ])
            );

            $plan->update([
                'paystack_plan_code' => $response->planCode,
            ]);

            Log::info('Auto-created plan in Paystack', [
                'plan_id' => $plan->id,
                'paystack_plan_code' => $response->planCode,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to auto-create plan in Paystack', [
                'plan_id' => $plan->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
```

## Payment Status Observer

React to payment status changes to trigger side effects:

```php
<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class PaymentObserver
{
    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        if (! $payment->wasChanged('status')) {
            return;
        }

        $oldStatus = $payment->getOriginal('status');
        $newStatus = $payment->status;

        Log::info('Payment status changed', [
            'payment_id' => $payment->id,
            'reference' => $payment->reference,
            'from' => $oldStatus,
            'to' => $newStatus,
        ]);

        // Handle specific status transitions
        match ($newStatus) {
            'success' => $this->handlePaymentSuccess($payment),
            'failed' => $this->handlePaymentFailure($payment),
            'refunded' => $this->handlePaymentRefund($payment),
            default => null,
        };
    }

    private function handlePaymentSuccess(Payment $payment): void
    {
        // Update related order
        if ($payment->order !== null) {
            $payment->order->update([
                'status' => 'paid',
                'paid_at' => $payment->paid_at,
            ]);
        }

        // Grant access to purchased content
        $this->grantAccess($payment);

        // Send confirmation email
        $payment->user->notify(new \App\Notifications\PaymentConfirmed($payment));

        // Update inventory
        foreach ($payment->order?->items ?? [] as $item) {
            $item->product->decrement('reserved_stock', $item->quantity);
        }
    }

    private function handlePaymentFailure(Payment $payment): void
    {
        // Release reserved inventory
        foreach ($payment->order?->items ?? [] as $item) {
            $item->product->increment('stock', $item->quantity);
        }

        // Notify user of failure
        $payment->user->notify(new \App\Notifications\PaymentFailed($payment));
    }

    private function handlePaymentRefund(Payment $payment): void
    {
        // Update order status
        if ($payment->order !== null) {
            $payment->order->update(['status' => 'refunded']);
        }

        // Revoke access if applicable
        $this->revokeAccess($payment);

        // Notify user
        $payment->user->notify(new \App\Notifications\PaymentRefunded($payment));
    }

    private function grantAccess(Payment $payment): void
    {
        // Implementation depends on your product type
        // Could be digital downloads, course access, subscription activation, etc.
    }

    private function revokeAccess(Payment $payment): void
    {
        // Reverse of grantAccess
    }
}
```

## Subscription Lifecycle Observer

Handle subscription state changes and notify relevant parties:

```php
<?php

namespace App\Observers;

use App\Models\Subscription;
use Illuminate\Support\Facades\Log;

class SubscriptionObserver
{
    /**
     * Handle the Subscription "updated" event.
     */
    public function updated(Subscription $subscription): void
    {
        if (! $subscription->wasChanged('status')) {
            return;
        }

        $oldStatus = $subscription->getOriginal('status');
        $newStatus = $subscription->status;

        Log::info('Subscription status changed', [
            'subscription_code' => $subscription->subscription_code,
            'from' => $oldStatus,
            'to' => $newStatus,
        ]);

        match ($newStatus) {
            'active' => $this->handleActivation($subscription, $oldStatus),
            'cancelled' => $this->handleCancellation($subscription),
            'expired' => $this->handleExpiration($subscription),
            default => null,
        };
    }

    private function handleActivation(Subscription $subscription, ?string $oldStatus): void
    {
        // New subscription or reactivation
        if ($oldStatus === null || $oldStatus === 'expired') {
            $subscription->user->notify(
                new \App\Notifications\SubscriptionActivated($subscription)
            );
        }

        // Grant plan features
        $subscription->user->syncPlanFeatures($subscription->plan_code);
    }

    private function handleCancellation(Subscription $subscription): void
    {
        $subscription->user->notify(
            new \App\Notifications\SubscriptionCancelled($subscription)
        );

        // Schedule feature revocation at period end
        \App\Jobs\RevokeSubscriptionFeatures::dispatch($subscription)
            ->delay($subscription->next_payment_date);
    }

    private function handleExpiration(Subscription $subscription): void
    {
        $subscription->user->notify(
            new \App\Notifications\SubscriptionExpired($subscription)
        );

        // Revoke features immediately
        $subscription->user->revokePlanFeatures($subscription->plan_code);
    }

    /**
     * Handle the Subscription "created" event.
     */
    public function created(Subscription $subscription): void
    {
        // Log new subscription for analytics
        Log::info('New subscription created', [
            'subscription_code' => $subscription->subscription_code,
            'user_id' => $subscription->user_id,
            'plan_code' => $subscription->plan_code,
        ]);

        // Update user metrics
        $subscription->user->increment('total_subscriptions_count');
    }
}
```
## Conditional Sync with Dirty Checking

Only sync when specific fields actually changed:

```php
<?php

namespace App\Observers;

use App\Models\User;

class EfficientUserObserver
{
    public function updated(User $user): void
    {
        // Check multiple fields efficiently
        $paystackFields = ['email', 'first_name', 'last_name', 'phone'];
        $changedPaystackFields = array_intersect(
            $paystackFields,
            array_keys($user->getChanges())
        );

        if (empty($changedPaystackFields)) {
            return;
        }

        // Build update payload only for changed fields
        $payload = ['codeOrEmailOrId' => $user->paystack_customer_code];

        foreach ($changedPaystackFields as $field) {
            $payload[$field] = $user->getAttribute($field);
        }

        // Queue the sync
        \App\Jobs\SyncCustomerToPaystack::dispatch($payload);
    }
}
```

## Testing Observers

Test that observers trigger the expected side effects:

```php
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Maxiviper117\Paystack\Facades\Paystack;

test('payment success triggers order update and notification', function (): void {
    Notification::fake();

    $order = \App\Models\Order::factory()->create(['status' => 'pending']);
    $payment = Payment::factory()->create([
        'order_id' => $order->id,
        'status' => 'pending',
    ]);

    $payment->update(['status' => 'success', 'paid_at' => now()]);

    expect($order->fresh()->status)->toBe('paid');
    Notification::assertSentTo($payment->user, \App\Notifications\PaymentConfirmed::class);
});

test('user email change syncs to paystack', function (): void {
    Paystack::shouldReceive('updateCustomer')
        ->once()
        ->with(\Mockery::on(function ($data) {
            return $data->email === 'newemail@example.com';
        }));

    $user = User::factory()->create([
        'email' => 'old@example.com',
        'paystack_customer_code' => 'CUS_123',
    ]);

    $user->update(['email' => 'newemail@example.com']);
});

test('observer does not sync when unrelated fields change', function (): void {
    Paystack::shouldReceive('updateCustomer')->never();

    $user = User::factory()->create([
        'paystack_customer_code' => 'CUS_123',
    ]);

    $user->update(['last_login_at' => now()]);
});
```

## Disabling Observers

Sometimes you need to disable observers (e.g., during bulk imports):

```php
use App\Models\User;
use App\Observers\UserObserver;

// Disable observer
User::withoutEvents(function () {
    User::query()->update(['email' => 'bulk@example.com']);
});

// Or unregister temporarily
User::unsetEventDispatcher();
// ... do work ...
User::setEventDispatcher(app('events'));
```

## Related pages

- [Database Transactions](/examples/database-transactions) — Wrap observer actions in transactions
- [Queued Jobs](/examples/queued-jobs) — Queue heavy sync operations from observers
- [Payment Notifications](/examples/notifications) — Send notifications from observers
- [Optional Billing Layer](/examples/billing-layer) — Local persistence that observers can sync from
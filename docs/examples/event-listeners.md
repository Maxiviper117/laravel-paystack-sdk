# Event Listeners

Use this flow when you need to respond to Paystack webhook events in your application. The package dispatches `PaystackWebhookReceived` events for every validated webhook, and the `PaystackWebhookHandler` provides a fluent API for routing typed event payloads to your application logic.

## Why Use Event Listeners for Paystack Events

- **Decoupled logic** — Keep webhook handling separate from business logic
- **Multiple responders** — Several listeners can react to the same event independently
- **Testability** — Test each listener in isolation without HTTP requests
- **Queueable** — Listeners can implement `ShouldQueue` for async processing
- **Maintainable** — Add new reactions to events without modifying existing code

## Typical Event Listener Scenarios

1. **Charge success** — Update payment status, send confirmation, fulfill order
2. **Subscription changes** — Update local subscription state, notify user
3. **Refund events** — Update refund status, adjust customer balance
4. **Dispute events** — Alert support team, freeze related funds
5. **Customer identification** — Update KYC status, unlock features

## Registering Listeners

Register your listener in `App\Providers\EventServiceProvider` or in Laravel 11/12's `bootstrap/app.php`:

```php
// In App\Providers\EventServiceProvider
protected $listen = [
    \Maxiviper117\Paystack\Events\PaystackWebhookReceived::class => [
        \App\Listeners\UpdatePaymentStatus::class,
        \App\Listeners\SendPaymentNotification::class,
        \App\Listeners\UpdateSubscriptionStatus::class,
        \App\Listeners\SyncBillingLayer::class,
    ],
];
```

Or in Laravel 11/12 using `Event::listen`:

```php
// In App\Providers\AppServiceProvider::boot
use Illuminate\Support\Facades\Event;
use Maxiviper117\Paystack\Events\PaystackWebhookReceived;

Event::listen(PaystackWebhookReceived::class, \App\Listeners\UpdatePaymentStatus::class);
Event::listen(PaystackWebhookReceived::class, \App\Listeners\SendPaymentNotification::class);
```

## Payment Status Listener

This listener updates the local payment record when a charge succeeds. It uses the `PaystackWebhookHandler` fluent API to route the typed event.

```php
namespace App\Listeners;

use App\Models\Payment;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\ChargeSuccessWebhookData;
use Maxiviper117\Paystack\Listeners\PaystackWebhookHandler;
use Maxiviper117\Paystack\Events\PaystackWebhookReceived;

class UpdatePaymentStatus
{
    public function handle(PaystackWebhookReceived $event): void
    {
        (new PaystackWebhookHandler)
            ->onChargeSuccess(function (ChargeSuccessWebhookData $typed): void {
                $payment = Payment::query()
                    ->where('reference', $typed->reference)
                    ->first();

                if ($payment === null) {
                    return;
                }

                // Idempotency: skip if already processed
                if ($payment->status === 'paid') {
                    return;
                }

                $payment->update([
                    'status' => 'paid',
                    'paid_at' => $typed->paidAt,
                    'channel' => $typed->channel,
                    'amount' => $typed->amount,
                    'currency' => $typed->currency,
                ]);
            })
            ->handle($event);
    }
}
```

**Key patterns:**

- **Idempotency check** — Always check if you've already processed the event. Paystack may send webhooks multiple times
- **Null check** — The payment might not exist in your system (e.g., test webhooks)
- **Typed payload** — `ChargeSuccessWebhookData` provides typed properties instead of raw arrays

## Subscription Status Listener

Handle subscription lifecycle events to keep local state in sync with Paystack.

```php
namespace App\Listeners;

use App\Models\Subscription;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionCreatedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionDisabledWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionNotRenewingWebhookData;
use Maxiviper117\Paystack\Listeners\PaystackWebhookHandler;
use Maxiviper117\Paystack\Events\PaystackWebhookReceived;

class UpdateSubscriptionStatus
{
    public function handle(PaystackWebhookReceived $event): void
    {
        (new PaystackWebhookHandler)
            ->onSubscriptionCreated(function (SubscriptionCreatedWebhookData $typed): void {
                Subscription::query()->updateOrCreate(
                    ['paystack_subscription_code' => $typed->subscriptionCode],
                    [
                        'status' => $typed->status,
                        'plan_code' => $typed->plan?->planCode,
                        'next_payment_date' => $typed->nextPaymentDate,
                        'email_token' => $typed->emailToken,
                    ]
                );
            })
            ->onSubscriptionDisabled(function (SubscriptionDisabledWebhookData $typed): void {
                $subscription = Subscription::query()
                    ->where('paystack_subscription_code', $typed->subscriptionCode)
                    ->first();

                if ($subscription === null) {
                    return;
                }

                $subscription->update([
                    'status' => 'disabled',
                    'cancelled_at' => now(),
                ]);
            })
            ->onSubscriptionNotRenewing(function (SubscriptionNotRenewingWebhookData $typed): void {
                $subscription = Subscription::query()
                    ->where('paystack_subscription_code', $typed->subscriptionCode)
                    ->first();

                if ($subscription === null) {
                    return;
                }

                $subscription->update([
                    'status' => 'not_renewing',
                    'expires_at' => $typed->nextPaymentDate,
                ]);
            })
            ->handle($event);
    }
}
```

## Refund Status Listener

Track refund progress through its lifecycle: pending → processing → processed (or failed).

```php
namespace App\Listeners;

use App\Models\Refund;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\RefundPendingWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\RefundProcessedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\RefundFailedWebhookData;
use Maxiviper117\Paystack\Listeners\PaystackWebhookHandler;
use Maxiviper117\Paystack\Events\PaystackWebhookReceived;

class UpdateRefundStatus
{
    public function handle(PaystackWebhookReceived $event): void
    {
        (new PaystackWebhookHandler)
            ->onRefundPending(function (RefundPendingWebhookData $typed): void {
                Refund::query()
                    ->where('refund_reference', $typed->refundReference)
                    ->update(['status' => 'pending']);
            })
            ->onRefundProcessed(function (RefundProcessedWebhookData $typed): void {
                Refund::query()
                    ->where('refund_reference', $typed->refundReference)
                    ->update([
                        'status' => 'processed',
                        'processed_at' => now(),
                    ]);
            })
            ->onRefundFailed(function (RefundFailedWebhookData $typed): void {
                Refund::query()
                    ->where('refund_reference', $typed->refundReference)
                    ->update(['status' => 'failed']);
            })
            ->handle($event);
    }
}
```

## Dispute Alert Listener

Dispute events require immediate attention. This listener sends notifications to the support team.

```php
namespace App\Listeners;

use App\Notifications\DisputeCreatedAlert;
use App\Notifications\DisputeResolvedAlert;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\ChargeDisputeCreatedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\ChargeDisputeResolvedWebhookData;
use Maxiviper117\Paystack\Listeners\PaystackWebhookHandler;
use Maxiviper117\Paystack\Events\PaystackWebhookReceived;

class HandleDisputeEvents
{
    public function handle(PaystackWebhookReceived $event): void
    {
        (new PaystackWebhookHandler)
            ->onChargeDisputeCreated(function (ChargeDisputeCreatedWebhookData $typed): void {
                // Notify support team about new dispute
                $admin = \App\Models\User::query()
                    ->where('is_admin', true)
                    ->first();

                $admin?->notify(new DisputeCreatedAlert(
                    disputeId: $typed->dispute->id,
                    transactionReference: $typed->dispute->transactionReference,
                    amount: $typed->dispute->amount,
                    reason: $typed->dispute->reason,
                ));

                // Optionally freeze the related payment
                \App\Models\Payment::query()
                    ->where('reference', $typed->dispute->transactionReference)
                    ->update(['disputed' => true]);
            })
            ->onChargeDisputeResolved(function (ChargeDisputeResolvedWebhookData $typed): void {
                // Notify support team about resolution
                $admin = \App\Models\User::query()
                    ->where('is_admin', true)
                    ->first();

                $admin?->notify(new DisputeResolvedAlert(
                    disputeId: $typed->dispute->id,
                    resolution: $typed->dispute->resolution,
                ));

                // Unfreeze the payment
                \App\Models\Payment::query()
                    ->where('reference', $typed->dispute->transactionReference)
                    ->update(['disputed' => false]);
            })
            ->handle($event);
    }
}
```

## Queued Listener for Heavy Processing

When your listener does heavy work (sending emails, syncing to external services, processing large data), make it queued:

```php
namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\ChargeSuccessWebhookData;
use Maxiviper117\Paystack\Listeners\PaystackWebhookHandler;
use Maxiviper117\Paystack\Events\PaystackWebhookReceived;

class FulfillOrder implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public int $backoff = [30, 60, 120];

    public function handle(PaystackWebhookReceived $event): void
    {
        (new PaystackWebhookHandler)
            ->onChargeSuccess(function (ChargeSuccessWebhookData $typed): void {
                $order = \App\Models\Order::query()
                    ->where('payment_reference', $typed->reference)
                    ->first();

                if ($order === null) {
                    Log::warning('Order not found for webhook reference', [
                        'reference' => $typed->reference,
                    ]);

                    return;
                }

                if ($order->isFulfilled()) {
                    return; // Idempotency: already fulfilled
                }

                // Heavy processing: send emails, update inventory, etc.
                $order->fulfill();
            })
            ->handle($event);
    }
}
```

**Why queue order fulfillment:**

- Webhooks should return a 200 response quickly; Paystack retries on timeouts
- Order fulfillment may involve external API calls (inventory, shipping)
- Failed fulfillment can be retried independently of the webhook processing
- The `tries` and `backoff` properties control retry behavior

## Using the Billing Layer Sync Listener

The package includes `SyncPaystackBillingLayer` which automatically syncs webhook data into the local mirror tables. Register it alongside your application listeners:

```php
use Maxiviper117\Paystack\Events\PaystackWebhookReceived;
use Maxiviper117\Paystack\Listeners\SyncPaystackBillingLayer;

protected $listen = [
    PaystackWebhookReceived::class => [
        SyncPaystackBillingLayer::class,
        \App\Listeners\UpdatePaymentStatus::class,
        \App\Listeners\SendPaymentNotification::class,
    ],
];
```

The billing sync listener only processes events that have corresponding local tables. If you haven't published the billing migrations, it safely does nothing.

## Available Webhook Event Callbacks

The `PaystackWebhookHandler` provides typed callbacks for all supported Paystack events:

| Method                              | Typed Data Class                           | Event                             |
| ----------------------------------- | ------------------------------------------ | --------------------------------- |
| `onChargeSuccess`                   | `ChargeSuccessWebhookData`                 | `charge.success`                  |
| `onChargeDisputeCreated`            | `ChargeDisputeCreatedWebhookData`          | `charge.dispute.create`           |
| `onChargeDisputeReminded`           | `ChargeDisputeRemindedWebhookData`         | `charge.dispute.remind`           |
| `onChargeDisputeResolved`           | `ChargeDisputeResolvedWebhookData`         | `charge.dispute.resolve`          |
| `onCustomerIdentificationSucceeded` | `CustomerIdentificationSuccessWebhookData` | `customeridentification.success`  |
| `onCustomerIdentificationFailed`    | `CustomerIdentificationFailedWebhookData`  | `customeridentification.failed`   |
| `onDedicatedAccountAssigned`        | `DedicatedAccountAssignSuccessWebhookData` | `dedicatedaccount.assign.success` |
| `onDedicatedAccountAssignFailed`    | `DedicatedAccountAssignFailedWebhookData`  | `dedicatedaccount.assign.failed`  |
| `onInvoiceCreated`                  | `InvoiceCreatedWebhookData`                | `invoice.create`                  |
| `onInvoiceUpdated`                  | `InvoiceUpdatedWebhookData`                | `invoice.update`                  |
| `onInvoicePaymentFailed`            | `InvoicePaymentFailedWebhookData`          | `invoice.payment_failed`          |
| `onPaymentRequestPending`           | `PaymentRequestPendingWebhookData`         | `paymentrequest.pending`          |
| `onPaymentRequestSuccess`           | `PaymentRequestSuccessWebhookData`         | `paymentrequest.success`          |
| `onRefundPending`                   | `RefundPendingWebhookData`                 | `refund.pending`                  |
| `onRefundProcessing`                | `RefundProcessingWebhookData`              | `refund.processing`               |
| `onRefundProcessed`                 | `RefundProcessedWebhookData`               | `refund.processed`                |
| `onRefundFailed`                    | `RefundFailedWebhookData`                  | `refund.failed`                   |
| `onSubscriptionCreated`             | `SubscriptionCreatedWebhookData`           | `subscription.create`             |
| `onSubscriptionNotRenewing`         | `SubscriptionNotRenewingWebhookData`       | `subscription.not_renew`          |
| `onSubscriptionDisabled`            | `SubscriptionDisabledWebhookData`          | `subscription.disable`            |
| `onSubscriptionExpiringCards`       | `SubscriptionExpiringCardsWebhookData`     | `subscription.expiring_cards`     |
| `onTransferSuccess`                 | `TransferSuccessWebhookData`               | `transfer.success`                |
| `onTransferFailed`                  | `TransferFailedWebhookData`                | `transfer.failed`                 |
| `onTransferReversed`                | `TransferReversedWebhookData`              | `transfer.reversed`               |

## Related pages

- [Webhook Processing](/examples/webhooks) — Setting up the webhook endpoint and basic handling
- [Payment Notifications](/examples/notifications) — Sending user notifications from event listeners
- [Queued Jobs](/examples/queued-jobs) — Processing heavy work asynchronously
- [Optional Billing Layer](/examples/billing-layer) — Automatic billing mirror sync
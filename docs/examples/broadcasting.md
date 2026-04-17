# Broadcasting and Real-Time Events

Use this flow when you need to push payment status updates to your frontend in real-time. Laravel's broadcasting system, combined with Laravel Echo, enables live payment status updates without polling.

## Why Use Broadcasting for Payments

- **Real-time feedback** — Users see payment confirmation immediately
- **Reduced server load** — No polling means fewer HTTP requests
- **Better UX** — Instant updates during checkout and verification
- **Multi-device sync** — Payment status updates across all user devices
- **Admin dashboards** — Live transaction feeds for support staff

## Typical Broadcasting Scenarios

1. **Checkout status** — Update checkout page when payment is confirmed
2. **Payment verification** — Notify user when background verification completes
3. **Admin transaction feed** — Real-time transaction list for support team
4. **Subscription events** — Notify when subscription status changes
5. **Refund status** — Update refund progress in real-time

## Setting Up Broadcasting

Configure your broadcasting driver in `.env`:

```env
BROADCAST_DRIVER=pusher
# or BROADCAST_DRIVER=redis for self-hosted

PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=mt1
```

Install Laravel Echo and Pusher JS:

```bash
npm install --save-dev laravel-echo pusher-js
```

Initialize Echo in your JavaScript:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
});
```

## Payment Status Event

Create an event that broadcasts when a payment status changes:

```php
<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentStatusUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Payment $payment,
        public string $previousStatus,
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            // Private channel for the payment owner
            new PrivateChannel('user.' . $this->payment->user_id),

            // Private channel for this specific payment
            new PrivateChannel('payment.' . $this->payment->reference),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'payment.status.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'reference' => $this->payment->reference,
            'status' => $this->payment->status,
            'previous_status' => $this->previousStatus,
            'amount' => $this->payment->amount,
            'amount_display' => $this->formatAmount($this->payment->amount),
            'paid_at' => $this->payment->paid_at?->toIso8601String(),
            'updated_at' => $this->payment->updated_at->toIso8601String(),
        ];
    }

    private function formatAmount(int $amountInKobo): string
    {
        return '₦' . number_format($amountInKobo / 100, 2);
    }
}
```

## Broadcasting from Webhook Handler

Broadcast events when processing Paystack webhooks:

```php
<?php

namespace App\Listeners;

use App\Events\PaymentStatusUpdated;
use App\Models\Payment;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\ChargeSuccessWebhookData;
use Maxiviper117\Paystack\Listeners\PaystackWebhookHandler;
use Maxiviper117\Paystack\Events\PaystackWebhookReceived;

class BroadcastPaymentUpdates
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

                $previousStatus = $payment->status;

                $payment->update([
                    'status' => 'success',
                    'paid_at' => $typed->paidAt,
                ]);

                // Broadcast the status change
                event(new PaymentStatusUpdated($payment, $previousStatus));
            })
            ->handle($event);
    }
}
```

## Frontend Listener

Listen for broadcast events in your JavaScript:

```javascript
// Listen on user channel for all their payment updates
Echo.private(`user.${userId}`)
    .listen('.payment.status.updated', (event) => {
        console.log('Payment updated:', event);

        // Update UI based on status
        if (event.status === 'success') {
            showPaymentSuccess(event);
        } else if (event.status === 'failed') {
            showPaymentFailure(event);
        }
    });

// Or listen on specific payment channel
Echo.private(`payment.${reference}`)
    .listen('.payment.status.updated', (event) => {
        updateCheckoutPage(event);
    });

function showPaymentSuccess(event) {
    // Update the checkout page
    document.getElementById('payment-status').innerHTML = `
        <div class="alert alert-success">
            <h4>Payment Successful!</h4>
            <p>Amount: ${event.amount_display}</p>
            <p>Reference: ${event.reference}</p>
            <a href="/receipt/${event.reference}" class="btn btn-primary">
                View Receipt
            </a>
        </div>
    `;

    // Redirect after a delay
    setTimeout(() => {
        window.location.href = `/order/confirmation/${event.reference}`;
    }, 3000);
}
```

## Real-Time Transaction Feed

Create a live transaction feed for admin dashboards:

```php
<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewTransaction implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Payment $payment,
    ) {}

    public function broadcastOn(): array
    {
        // Presence channel for admin dashboard
        return [
            new PresenceChannel('admin.transactions'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'transaction.new';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->payment->id,
            'reference' => $this->payment->reference,
            'amount' => $this->payment->amount,
            'amount_display' => '₦' . number_format($this->payment->amount / 100, 2),
            'status' => $this->payment->status,
            'channel' => $this->payment->channel,
            'customer_email' => $this->payment->user->email,
            'created_at' => $this->payment->created_at->toIso8601String(),
        ];
    }
}
```

**Admin dashboard JavaScript:**

```javascript
Echo.join('admin.transactions')
    .here((users) => {
        console.log('Admins online:', users.length);
    })
    .joining((user) => {
        console.log('Admin joined:', user.name);
    })
    .leaving((user) => {
        console.log('Admin left:', user.name);
    })
    .listen('.transaction.new', (event) => {
        // Add new transaction to the live feed
        prependTransactionToTable(event);

        // Play notification sound
        playNotificationSound();

        // Show browser notification
        if (Notification.permission === 'granted') {
            new Notification('New Payment', {
                body: `${event.amount_display} - ${event.customer_email}`,
            });
        }
    });

function prependTransactionToTable(event) {
    const table = document.getElementById('transaction-feed');
    const row = document.createElement('tr');
    row.className = 'highlight-new';
    row.innerHTML = `
        <td>${event.reference}</td>
        <td>${event.amount_display}</td>
        <td><span class="badge bg-${getStatusColor(event.status)}">${event.status}</span></td>
        <td>${event.channel}</td>
        <td>${event.customer_email}</td>
        <td>${timeAgo(event.created_at)}</td>
    `;
    table.insertBefore(row, table.firstChild);

    // Remove highlight after animation
    setTimeout(() => row.classList.remove('highlight-new'), 2000);
}
```

## Broadcasting Verification Progress

For long-running verifications, broadcast progress updates:

```php
<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VerificationProgress implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public string $reference,
        public string $status, // 'pending', 'processing', 'completed', 'failed'
        public ?string $message = null,
        public ?int $progress = null, // 0-100
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('payment.' . $this->reference),
        ];
    }

    public function broadcastAs(): string
    {
        return 'verification.progress';
    }
}
```

**Dispatching progress updates from a job:**

```php
<?php

namespace App\Jobs;

use App\Events\VerificationProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;
use Maxiviper117\Paystack\Facades\Paystack;

class VerifyTransactionWithProgress implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $reference,
    ) {}

    public function handle(): void
    {
        // Notify frontend that verification started
        event(new VerificationProgress(
            reference: $this->reference,
            status: 'processing',
            message: 'Verifying payment with Paystack...',
            progress: 25,
        ));

        try {
            $response = Paystack::verifyTransaction(
                VerifyTransactionInputData::from(['reference' => $this->reference])
            );

            // Success
            event(new VerificationProgress(
                reference: $this->reference,
                status: 'completed',
                message: 'Payment verified successfully!',
                progress: 100,
            ));
        } catch (\Exception $e) {
            // Failure
            event(new VerificationProgress(
                reference: $this->reference,
                status: 'failed',
                message: 'Verification failed: ' . $e->getMessage(),
                progress: 100,
            ));
        }
    }
}
```

**Frontend progress indicator:**

```javascript
Echo.private(`payment.${reference}`)
    .listen('.verification.progress', (event) => {
        updateProgressBar(event.progress);
        updateStatusMessage(event.message);

        if (event.status === 'completed') {
            showSuccessState();
        } else if (event.status === 'failed') {
            showFailureState(event.message);
        }
    });

function updateProgressBar(progress) {
    const bar = document.getElementById('verification-progress');
    bar.style.width = `${progress}%`;
    bar.setAttribute('aria-valuenow', progress);
}
```

## Subscription Event Broadcasting

Broadcast subscription lifecycle events:

```php
<?php

namespace App\Events;

use App\Models\Subscription;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Subscription $subscription,
        public string $event, // 'created', 'renewed', 'cancelled', 'expired'
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->subscription->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'subscription.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'subscription_code' => $this->subscription->subscription_code,
            'plan_name' => $this->subscription->plan_name,
            'status' => $this->subscription->status,
            'event' => $this->event,
            'next_payment_date' => $this->subscription->next_payment_date?->toIso8601String(),
        ];
    }
}
```

## Channel Authorization

Define authorization logic for private channels in `routes/channels.php`:

```php
<?php

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

// User can listen to their own payment updates
Broadcast::channel('user.{userId}', function (User $user, int $userId): bool {
    return $user->id === $userId;
});

// User can listen to specific payment updates they own
Broadcast::channel('payment.{reference}', function (User $user, string $reference): bool {
    return Payment::query()
        ->where('reference', $reference)
        ->where('user_id', $user->id)
        ->exists();
});

// Only admins can join the admin transactions channel
Broadcast::channel('admin.transactions', function (User $user): bool {
    return $user->is_admin;
});
```

## Testing Broadcast Events

Test that events are broadcast with correct data:

```php
use App\Events\PaymentStatusUpdated;
use App\Models\Payment;
use Illuminate\Support\Facades\Broadcast;

test('payment status update is broadcast', function (): void {
    Broadcast::fake();

    $payment = Payment::factory()->create([
        'status' => 'pending',
    ]);

    $previousStatus = $payment->status;
    $payment->update(['status' => 'success']);

    event(new PaymentStatusUpdated($payment, $previousStatus));

    Broadcast::assertPrivateChannel('user.' . $payment->user_id);
    Broadcast::assertPrivateChannel('payment.' . $payment->reference);
});
```

## Related pages

- [Event Listeners](/examples/event-listeners) — Handle webhook events before broadcasting
- [Webhook Processing](/examples/webhooks) — Receive events from Paystack to broadcast
- [Payment Notifications](/examples/notifications) — Email alerts complement real-time updates
- [Queued Jobs](/examples/queued-jobs) — Queue broadcast events for async processing
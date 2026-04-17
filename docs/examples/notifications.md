# Payment Notifications

Use this flow when you need to notify users about payment events via email, SMS, or in-app channels. Laravel's notification system provides a flexible, channel-agnostic way to alert customers and administrators about important payment events.

## Why Use Notifications for Payment Events

Payment notifications serve multiple purposes in your application:

- **Customer confidence** — Immediate confirmation emails reassure customers their payment was received
- **Transparency** — Keep customers informed about subscription renewals, refunds, and failures
- **Support efficiency** — Proactive notifications reduce support tickets asking about payment status
- **Compliance** — Many jurisdictions require payment confirmations for financial transparency
- **Retention** — Reminder notifications can prevent involuntary churn from expired cards

## Typical Notification Scenarios

1. **Payment confirmations** — Send after successful transaction verification
2. **Failed payment alerts** — Notify admins of issues requiring investigation
3. **Subscription reminders** — Warn customers before renewals or expirations
4. **Refund updates** — Keep customers informed about refund processing status
5. **Dispute notifications** — Alert admins when chargebacks are initiated

## Payment Confirmation Notification

This notification is sent to customers after their payment is successfully verified. It includes multiple channels (email and database) and formats the amount for human readability.

```php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $reference,
        public int $amount,
        public string $currency,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $formattedAmount = number_format($this->amount / 100, 2);

        return (new MailMessage)
            ->subject('Payment Confirmation - ' . $this->reference)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your payment has been successfully processed.')
            ->line('Amount: ' . $this->currency . ' ' . $formattedAmount)
            ->line('Reference: ' . $this->reference)
            ->action('View Receipt', url('/receipts/' . $this->reference))
            ->line('Thank you for your business!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'payment_confirmed',
            'reference' => $this->reference,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'message' => 'Payment of ' . $this->currency . ' ' . number_format($this->amount / 100, 2) . ' confirmed',
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'reference' => $this->reference,
            'amount' => $this->amount,
            'currency' => $this->currency,
        ];
    }
}
```

**Understanding the notification structure:**

- **`ShouldQueue`** — Notifications are sent asynchronously via your queue, preventing email delays from slowing down web responses
- **`via()` method** — Defines which channels to use. This example sends email and stores in the database for in-app notifications
- **`toMail()`** — Returns a `MailMessage` with a clean, professional email format
- **`toDatabase()`** — Stores notification data for displaying in your application's notification center
- **Amount formatting** — Paystack stores amounts in kobo/cents. Divide by 100 to display in standard currency format

**When to send this notification:**

Send payment confirmations only after you've verified the transaction status with Paystack, not just when receiving the callback. This ensures the notification accurately reflects the payment state.

## Sending Notification After Webhook

Webhooks are the most reliable trigger for sending payment notifications. Here's how to integrate with the Paystack webhook handler:

```php
namespace App\Listeners;

use App\Notifications\PaymentConfirmed;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\ChargeSuccessWebhookData;
use Maxiviper117\Paystack\Events\PaystackWebhookReceived;
use Maxiviper117\Paystack\Listeners\PaystackWebhookHandler;

class HandlePaystackWebhook
{
    public function handle(PaystackWebhookReceived $event): void
    {
        (new PaystackWebhookHandler)
            ->onChargeSuccess(function (ChargeSuccessWebhookData $typed) use ($event): void {
                $payment = \App\Models\Payment::query()
                    ->where('reference', $typed->reference)
                    ->with('customer')
                    ->first();

                if ($payment === null || $payment->status === 'paid') {
                    return;
                }

                $payment->update([
                    'status' => 'paid',
                    'paid_at' => $typed->paidAt,
                ]);

                // Notify customer
                $payment->customer->notify(
                    new PaymentConfirmed(
                        $typed->reference,
                        $typed->amount,
                        $typed->currency,
                    )
                );
            })
            ->handle($event);
    }
}
```

**How this works:**

1. **Webhook arrives** — Paystack sends a `charge.success` event to your endpoint
2. **Event is fired** — The package fires `PaystackWebhookReceived` with the typed payload
3. **Handler processes** — Your listener uses `PaystackWebhookHandler` to route by event type
4. **Idempotency check** — Verify we haven't already processed this payment (`status === 'paid'`)
5. **Database update** — Mark payment as paid with the timestamp from Paystack
6. **Notification sent** — Customer receives confirmation email

**Idempotency is critical:**

Paystack may send webhooks multiple times for the same event. Always check if you've already processed the event before sending notifications to avoid spamming customers.

## Admin Alert Notification

Some payment events require immediate admin attention. This notification supports multiple channels (email and Slack) and provides actionable information.

```php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class PaymentFailedAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $reference,
        public string $reason,
        public ?string $customerEmail = null,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['mail'];

        if (config('services.slack.webhook_url')) {
            $channels[] = 'slack';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('Payment Failed Alert')
            ->line('A payment has failed and requires attention.')
            ->line('Reference: ' . $this->reference)
            ->line('Customer: ' . ($this->customerEmail ?? 'Unknown'))
            ->line('Reason: ' . $this->reason)
            ->action('Investigate', url('/admin/payments/' . $this->reference));
    }

    public function toSlack(object $notifiable): SlackMessage
    {
        return (new SlackMessage)
            ->error()
            ->content('Payment Failed Alert')
            ->attachment(function ($attachment) {
                $attachment->fields([
                    'Reference' => $this->reference,
                    'Customer' => $this->customerEmail ?? 'Unknown',
                    'Reason' => $this->reason,
                ]);
            });
    }
}
```

**Multi-channel strategy:**

- **Email** — Detailed message with full context and action buttons
- **Slack** — Quick alert for immediate awareness (optional, only if configured)
- **Conditional channels** — Slack is only added if the webhook URL is configured

**When to alert admins:**

- High-value payment failures (potential lost revenue)
- Multiple failed attempts from the same customer (possible fraud)
- Gateway errors (indicating service issues)
- Refund failures (customer satisfaction risk)

## Subscription Reminder Notification

Prevent involuntary churn by notifying customers before their subscription renews or expires. This notification adapts its message based on urgency.

```php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionRenewalReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $subscriptionCode,
        public string $planName,
        public int $daysUntilRenewal,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Your Subscription Renews in ' . $this->daysUntilRenewal . ' Days')
            ->greeting('Hello ' . $notifiable->name . ',');

        if ($this->daysUntilRenewal <= 1) {
            $message->line('Your ' . $this->planName . ' subscription renews tomorrow.');
        } else {
            $message->line('Your ' . $this->planName . ' subscription renews in ' . $this->daysUntilRenewal . ' days.');
        }

        return $message
            ->line('Ensure your payment method is up to date to avoid interruption.')
            ->action('Update Payment Method', url('/billing/subscriptions/' . $this->subscriptionCode))
            ->line('Thank you for being a valued customer!');
    }
}
```

**Message adaptation:**

The notification changes its tone based on urgency:
- **7+ days**: Standard reminder with helpful tone
- **1-3 days**: More urgent language
- **Tomorrow**: Immediate action required

**Scheduling reminders:**

Send multiple reminders at different intervals:
- 7 days before renewal (gentle reminder)
- 3 days before (urgent reminder)
- 1 day before (final reminder)

## Scheduled Reminder Command

To send renewal reminders automatically, create a scheduled command that checks for upcoming renewals and dispatches notifications.

```php
namespace App\Console\Commands;

use App\Notifications\SubscriptionRenewalReminder;
use Illuminate\Console\Command;
use Maxiviper117\Paystack\Data\Input\Subscription\ListSubscriptionsInputData;
use Maxiviper117\Paystack\Facades\Paystack;

class SendRenewalReminders extends Command
{
    protected $signature = 'subscriptions:send-reminders
                            {--days=3 : Days before renewal to send reminder}';

    protected $description = 'Send subscription renewal reminders';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $targetDate = now()->addDays($days)->format('Y-m-d');

        $response = Paystack::listSubscriptions(
            ListSubscriptionsInputData::from([
                'perPage' => 100,
            ])
        );

        $remindersSent = 0;

        foreach ($response->subscriptions as $subscription) {
            // Check if subscription renews on target date
            if ($subscription->nextPaymentDate?->format('Y-m-d') !== $targetDate) {
                continue;
            }

            // Find local customer
            $customer = \App\Models\Customer::query()
                ->where('paystack_customer_code', $subscription->customer->customerCode)
                ->first();

            if ($customer === null) {
                continue;
            }

            $customer->notify(new SubscriptionRenewalReminder(
                $subscription->subscriptionCode,
                $subscription->plan->name,
                $days,
            ));

            $remindersSent++;
        }

        $this->info("Sent {$remindersSent} renewal reminder(s).");

        return self::SUCCESS;
    }
}
```

**How the command works:**

1. **Calculate target date** — Based on the `--days` option, determine which subscriptions renew on that date
2. **Fetch subscriptions** — List active subscriptions from Paystack
3. **Filter by date** — Only process subscriptions renewing on the target date
4. **Find local customer** — Match Paystack customer code to your database
5. **Send notification** — Dispatch the reminder to the customer

**Scheduling in Laravel:**

```php
// routes/console.php
Schedule::command('subscriptions:send-reminders --days=7')->daily();
Schedule::command('subscriptions:send-reminders --days=3')->daily();
Schedule::command('subscriptions:send-reminders --days=1')->daily();
```

## Refund Status Notification

Keep customers informed throughout the refund process. This notification adapts based on the refund status.

```php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefundProcessed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $originalReference,
        public string $refundReference,
        public int $amount,
        public string $status,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $formattedAmount = number_format($this->amount / 100, 2);

        $message = (new MailMessage)
            ->subject('Refund Update - ' . $this->refundReference)
            ->greeting('Hello ' . $notifiable->name . ',');

        if ($this->status === 'processed') {
            $message
                ->line('Your refund has been processed.')
                ->line('Refund Amount: ₦' . $formattedAmount)
                ->line('The funds should appear in your account within 5-10 business days.');
        } else {
            $message
                ->line('Your refund status has been updated.')
                ->line('Current Status: ' . ucfirst($this->status))
                ->line('Refund Amount: ₦' . $formattedAmount);
        }

        return $message
            ->line('Original Reference: ' . $this->originalReference)
            ->action('View Details', url('/refunds/' . $this->refundReference));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'refund_update',
            'original_reference' => $this->originalReference,
            'refund_reference' => $this->refundReference,
            'amount' => $this->amount,
            'status' => $this->status,
        ];
    }
}
```

**Status-specific messaging:**

Different refund statuses warrant different messages:
- **`processed`** — Funds are on their way, provide timeline expectations
- **`pending`** — Refund is being reviewed
- **`failed`** — Explain the issue and next steps

**Timeline communication:**

Always set clear expectations about when customers will see refunded funds. This reduces support inquiries asking about refund timing.

## Notification in Response to Webhook

Paystack sends webhook events for refund status changes. Listen for these events to automatically notify customers.

```php
namespace App\Listeners;

use App\Notifications\RefundProcessed;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\RefundProcessedWebhookData;
use Maxiviper117\Paystack\Events\PaystackWebhookReceived;
use Maxiviper117\Paystack\Listeners\PaystackWebhookHandler;

class HandleRefundWebhook
{
    public function handle(PaystackWebhookReceived $event): void
    {
        (new PaystackWebhookHandler)
            ->onRefundProcessed(function (RefundProcessedWebhookData $typed): void {
                $payment = \App\Models\Payment::query()
                    ->where('reference', $typed->transactionReference)
                    ->with('customer')
                    ->first();

                if ($payment === null) {
                    return;
                }

                // Update local refund record
                \App\Models\Refund::query()->updateOrCreate(
                    ['reference' => $typed->refundReference],
                    [
                        'payment_id' => $payment->getKey(),
                        'amount' => $typed->refundAmount,
                        'status' => $typed->status,
                        'processed_at' => now(),
                    ]
                );

                // Notify customer
                $payment->customer->notify(new RefundProcessed(
                    $typed->transactionReference,
                    $typed->refundReference,
                    $typed->refundAmount,
                    $typed->status,
                ));
            })
            ->handle($event);
    }
}
```

**Webhook-driven notifications ensure:**

- **Real-time updates** — Customers know immediately when refund status changes
- **Accuracy** — Notification matches Paystack's actual status
- **Automation** — No manual intervention required

## Testing Notifications

Test notifications to ensure they contain correct data and are sent to the right channels.

```php
namespace Tests\Feature\Notifications;

use App\Models\Customer;
use App\Notifications\PaymentConfirmed;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PaymentConfirmedTest extends TestCase
{
    public function test_notification_sent_on_successful_payment(): void
    {
        Notification::fake();

        $customer = Customer::factory()->create();

        $customer->notify(new PaymentConfirmed(
            'ref_123',
            500000, // 5000 NGN in kobo
            'NGN',
        ));

        Notification::assertSentTo(
            $customer,
            PaymentConfirmed::class,
            function ($notification, $channels) {
                return in_array('mail', $channels)
                    && in_array('database', $channels);
            }
        );
    }

    public function test_notification_contains_correct_data(): void
    {
        $customer = Customer::factory()->create();

        $notification = new PaymentConfirmed('ref_123', 500000, 'NGN');
        $mail = $notification->toMail($customer);

        $this->assertStringContainsString('ref_123', $mail->subject);
        $this->assertStringContainsString('5,000.00', implode('\n', $mail->introLines));
    }

    public function test_notification_formats_amount_correctly(): void
    {
        $notification = new PaymentConfirmed('ref_123', 500000, 'NGN');
        $array = $notification->toArray(new Customer);

        $this->assertEquals(500000, $array['amount']);
    }
}
```

**Testing strategies:**

- **Mock notifications** to verify they're dispatched without sending real emails
- **Assert channel selection** to ensure email, database, or Slack are used appropriately
- **Verify content** to check that amounts are formatted and references are included
- **Test edge cases** like zero amounts or missing customer data

## Notification Best Practices

1. **Queue notifications** — Always implement `ShouldQueue` to prevent email delays from slowing web responses
2. **Be concise** — Customers scan emails quickly; get to the point
3. **Include action buttons** — Make it easy for customers to view details or take action
4. **Set expectations** — For refunds, tell customers when they'll see funds
5. **Use database channel** — Store notifications for in-app notification centers
6. **Respect preferences** — Allow customers to opt out of non-essential notifications
7. **Localize content** — Support multiple languages for international customers

## Related Pages

- [Webhook Processing](/examples/webhooks) — Learn how to trigger notifications from webhooks
- [Queued Jobs](/examples/queued-jobs) — Background processing for notifications
- [One-Time Checkout](/examples/checkout) — Payment flows that trigger confirmations
- [Refunds](/refunds) — Reference for refund operations

# Webhook Processing

Use this flow when your app needs to receive Paystack webhooks, validate signatures, process stored webhook calls, and update local payment or subscription state safely.

## Typical application flow

1. Register the webhook endpoint in your Laravel app.
2. Exclude that endpoint from CSRF validation.
3. Publish the webhook migration, then run it and start a queue worker.
4. Keep the documented Paystack webhook IP allowlist enabled unless you explicitly need to replay from another source.
5. Listen for `PaystackWebhookReceived`.
6. Resolve typed webhook payloads where the package supports them.
7. Update local state idempotently.

## Register the route

```php
use Illuminate\Support\Facades\Route;

Route::webhooks('paystack/webhook', 'paystack');
```

## Exclude the route from CSRF validation

Laravel 11/12 `bootstrap/app.php` example:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->validateCsrfTokens(except: [
        'paystack/webhook',
    ]);
})
```

## Local setup commands

```bash
php artisan vendor:publish --provider="Spatie\WebhookClient\WebhookClientServiceProvider" --tag="webhook-client-migrations"
php artisan migrate
php artisan queue:work
```

The package filters incoming webhook requests by Paystack's documented source IP addresses before the webhook call is stored. If you need to replay a payload locally from another source, temporarily set `PAYSTACK_WEBHOOK_ALLOWED_IPS=` in your app environment.

## Preferred listener-based example

```php
namespace App\Listeners;

use App\Models\Payment;
use App\Models\Subscription;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\ChargeSuccessWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionDisabledWebhookData;
use Maxiviper117\Paystack\Enums\Webhook\PaystackWebhookEvent;
use Maxiviper117\Paystack\Events\PaystackWebhookReceived;
use Maxiviper117\Paystack\Listeners\PaystackWebhookHandler;

class HandlePaystackWebhook
{
    public function handle(PaystackWebhookReceived $event): void
    {
        if ($event->event->is(PaystackWebhookEvent::ChargeSuccess)) {
            // You can branch on the enum directly if you want.
        }

        (new PaystackWebhookHandler)
            ->onChargeSuccess(function (ChargeSuccessWebhookData $typed): void {
                $payment = Payment::query()
                    ->where('payment_reference', $typed->reference)
                    ->first();

                if ($payment === null || $payment->status === 'paid') {
                    return;
                }

                $payment->status = 'paid';
                $payment->paid_at = $typed->paidAt;
                $payment->save();
            })
            ->onSubscriptionDisabled(function (SubscriptionDisabledWebhookData $typed): void {
                $subscription = Subscription::query()
                    ->where('paystack_subscription_code', $typed->subscriptionCode)
                    ->first();

                if ($subscription === null) {
                    return;
                }

                $subscription->status = $typed->status;
                $subscription->save();
            })
            ->handle($event);
    }
}
```

Typed webhook timestamp fields such as `$typed->paidAt` are exposed as `CarbonImmutable|null` in PHP.

If you want a starting point rather than a blank listener, configure `Maxiviper117\Paystack\Listeners\PaystackWebhookHandler` with only the callbacks you need and pass it to Laravel's event system.

The webhook event DTO also accepts `Maxiviper117\Paystack\Enums\Webhook\PaystackWebhookEvent` in its `is()` helper, so you can branch on enum cases instead of raw strings.

## Queue the listener too

```php
namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;

class HandlePaystackWebhook implements ShouldQueue
{
    // handle(...) from the previous example
}
```

## Inspect stored webhook calls

```php
use Maxiviper117\Paystack\Models\PaystackWebhookCall;

$latestWebhook = PaystackWebhookCall::query()->latest()->first();

$rawBody = $latestWebhook?->rawBody();
$inputPayload = $latestWebhook?->inputPayload();
```

## Important notes

- webhook intake is endpoint-first and asynchronous
- invalid signatures are rejected before the webhook is stored
- valid but malformed payloads are stored and then fail during processing, which preserves an audit trail
- use idempotent local updates because webhook delivery can be retried
- typed webhook payloads currently exist for selected charge, invoice, and subscription events only

## Related pages

- [Webhooks](/webhooks)
- [Verify a Transaction](/examples/verify-transaction)
- [Subscription Billing Flow](/examples/subscriptions)

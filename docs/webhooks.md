# Webhooks

Webhook support is local package logic built on top of `spatie/laravel-webhook-client`. It does not go through the outbound Saloon connector layer.

Looking for an end-to-end Laravel integration flow? Start with [Webhook Processing](/examples/webhooks).

## Typical flow

1. Register a webhook endpoint in your app.
2. Exclude that endpoint from CSRF protection.
3. Publish and run the webhook client migration so valid calls can be stored.
4. Run a queue worker so the stored call can be processed.
5. Listen for `PaystackWebhookReceived` and handle the normalized event data.

## What is supported

- Paystack signature verification using the configured secret key
- webhook source IP allowlisting for the documented Paystack webhook origin addresses
- persisted webhook calls in the `webhook_calls` table
- queued processing through a package-provided webhook job
- dispatch of a generic parsed Paystack webhook event object
- typed webhook payload mapping for high-value charge, invoice, and subscription events

## Register the webhook endpoint

```php
use Illuminate\Support\Facades\Route;

Route::webhooks('paystack/webhook', 'paystack');
```

If you prefer to avoid the route macro, the equivalent explicit route is:

```php
use Illuminate\Support\Facades\Route;
use Spatie\WebhookClient\Http\Controllers\WebhookController;

Route::post('paystack/webhook', WebhookController::class)
    ->name('webhook-client-paystack');
```

## Local setup example

```bash
php artisan vendor:publish --provider="Spatie\WebhookClient\WebhookClientServiceProvider" --tag="webhook-client-migrations"
php artisan migrate
php artisan queue:work
```

## Restrict webhook origins

The package checks the incoming request IP before it stores or processes a webhook. By default it allows the Paystack webhook IP addresses documented by Paystack. You can override the list or disable it in your app config:

```php
return [
    'webhooks' => [
        'allowed_ips' => [
            '52.31.139.75',
            '52.49.173.169',
            '52.214.14.220',
        ],
    ],
];
```

If you prefer environment-based configuration, set `PAYSTACK_WEBHOOK_ALLOWED_IPS` to a comma-separated list. Leave it empty to disable the allowlist for local replay or non-Paystack webhook sources.

If your app sits behind a proxy or load balancer, make sure Laravel is resolving the real client IP before the webhook route reaches the profile check.

## Listen for processed Paystack webhooks

The package emits `PaystackWebhookReceived` after a valid webhook has been:

- signature-validated
- stored in `webhook_calls`
- processed by the queue job

### Simple closure listener

```php
use Illuminate\Support\Facades\Event;
use Maxiviper117\Paystack\Events\PaystackWebhookReceived;

Event::listen(PaystackWebhookReceived::class, function (PaystackWebhookReceived $event) {
    $paystackEvent = $event->event;

    if ($paystackEvent->event === 'charge.success') {
        $reference = $paystackEvent->data['reference'] ?? null;
        $status = $paystackEvent->data['status'] ?? null;

        // Update your order, mark a payment as settled, etc.
    }
});
```

### Typed payload listener

The generic envelope stays available for every webhook, but supported events can now be resolved into typed payload DTOs.

```php
use Illuminate\Support\Facades\Event;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\ChargeSuccessWebhookData;
use Maxiviper117\Paystack\Events\PaystackWebhookReceived;

Event::listen(PaystackWebhookReceived::class, function (PaystackWebhookReceived $event) {
    $webhook = $event->event;

    if (! $webhook->is('charge.success')) {
        return;
    }

    $typed = $webhook->typedData();

    if (! $typed instanceof ChargeSuccessWebhookData) {
        return;
    }

    $reference = $typed->reference;
    $customerCode = $typed->customer?->customerCode;
    $amount = $typed->amount;

    // Update your order and record the settled transaction.
});
```

### Fluent callback handler

```php
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Maxiviper117\Paystack\Data\Output\Webhook\PaystackWebhookEventData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\ChargeSuccessWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionCreatedWebhookData;
use Maxiviper117\Paystack\Enums\Webhook\PaystackWebhookEvent;
use Maxiviper117\Paystack\Listeners\PaystackWebhookHandler;

$handler = (new PaystackWebhookHandler)
    ->onChargeSuccess(function (ChargeSuccessWebhookData $webhook): void {
        Log::info('Charge succeeded', [
            'reference' => $webhook->reference,
            'amount' => $webhook->amount,
        ]);
    })
    ->onSubscriptionCreated(function (SubscriptionCreatedWebhookData $webhook): void {
        Log::info('Subscription created', [
            'subscription_code' => $webhook->subscriptionCode,
        ]);
    })
    ->onUnhandled(function (PaystackWebhookEventData $webhook): void {
        Log::info('Unhandled webhook received', [
            'event' => $webhook->event,
        ]);
    });

Event::listen(\Maxiviper117\Paystack\Events\PaystackWebhookReceived::class, [$handler, 'handle']);
```

The package ships the reusable fluent helper at `Maxiviper117\Paystack\Listeners\PaystackWebhookHandler`. Configure only the callbacks you care about, then pass the handler to Laravel's event system.

Available callback setters:

- `onChargeSuccess(callable $callback)`
- `onInvoiceCreated(callable $callback)`
- `onInvoiceUpdated(callable $callback)`
- `onInvoicePaymentFailed(callable $callback)`
- `onSubscriptionCreated(callable $callback)`
- `onSubscriptionNotRenewing(callable $callback)`
- `onSubscriptionDisabled(callable $callback)`
- `onUnhandled(callable $callback)`

If you prefer a dedicated Laravel listener class, instantiate and configure the same helper inside your listener's `handle()` method and delegate to `$handler->handle($event)`.

The supported event enum lives at `Maxiviper117\Paystack\Enums\Webhook\PaystackWebhookEvent`, and `PaystackWebhookEventData::is()` accepts either the enum or a raw string.

### Queue the listener itself

If your app-side webhook handling is expensive, make your listener queueable too:

```php
namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maxiviper117\Paystack\Events\PaystackWebhookReceived;

class HandlePaystackWebhook implements ShouldQueue
{
    public function handle(PaystackWebhookReceived $event): void
    {
        // Long-running app logic here.
    }
}
```

## What you receive in the event

`PaystackWebhookReceived` gives you:

- `$event->webhookCall`: the stored webhook call model
- `$event->event`: a normalized `PaystackWebhookEventData` object

Useful properties on `$event->event`:

- `event`: full Paystack event name such as `charge.success`
- `resourceType`: inferred resource prefix such as `charge`
- `id`: resource ID when present
- `domain`: Paystack domain when present
- `occurredAt`: `CarbonImmutable|null` resolved from `paid_at`, `created_at`, or payload fallback when available
- `data`: the nested Paystack `data` object
- `payload`: the full decoded webhook payload

Useful methods on `$event->event`:

- `is('charge.success')`: exact event-name match helper
- `supportsTypedData()`: tells you whether the package has a typed DTO for that event
- `typedData()`: returns a typed webhook DTO for supported events, otherwise `null`

Example:

```php
Event::listen(PaystackWebhookReceived::class, function (PaystackWebhookReceived $event) {
    if ($event->event->is(PaystackWebhookEvent::InvoiceCreate)) {
        // Handle the invoice create event.
    }

    logger()->info('Paystack webhook received', [
        'event' => $event->event->event,
        'resource_type' => $event->event->resourceType,
        'id' => $event->event->id,
        'occurred_at' => $event->event->occurredAt?->toAtomString(),
        'reference' => $event->event->data['reference'] ?? null,
    ]);
});
```

## Typed webhook events currently supported

Typed DTOs are currently available for:

- `charge.success`
- `invoice.create`
- `invoice.update`
- `invoice.payment_failed`
- `subscription.create`
- `subscription.not_renew`
- `subscription.disable`

Example for invoice handling:

```php
use Illuminate\Support\Facades\Event;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\InvoiceCreatedWebhookData;
use Maxiviper117\Paystack\Events\PaystackWebhookReceived;

Event::listen(PaystackWebhookReceived::class, function (PaystackWebhookReceived $event) {
    if (! $event->event->is('invoice.create')) {
        return;
    }

    $typed = $event->event->typedData();

    if (! $typed instanceof InvoiceCreatedWebhookData) {
        return;
    }

    logger()->info('Invoice created', [
        'invoice_code' => $typed->invoiceCode,
        'subscription_code' => $typed->subscriptionCode,
        'customer_code' => $typed->customerCode,
        'paid' => $typed->paid,
    ]);
});
```

Example for subscription lifecycle handling:

```php
use Illuminate\Support\Facades\Event;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionCreatedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionDisabledWebhookData;
use Maxiviper117\Paystack\Events\PaystackWebhookReceived;

Event::listen(PaystackWebhookReceived::class, function (PaystackWebhookReceived $event) {
    $typed = $event->event->typedData();

    if ($typed instanceof SubscriptionCreatedWebhookData) {
        // Provision or mark the subscription active in your app.
        return;
    }

    if ($typed instanceof SubscriptionDisabledWebhookData) {
        // Mark the subscription inactive or complete in your app.
    }
});
```

## Inspect stored webhook calls

You can inspect stored payloads through the custom webhook call model:

```php
use Maxiviper117\Paystack\Models\PaystackWebhookCall;

$latestWebhook = PaystackWebhookCall::query()->latest()->first();

$rawBody = $latestWebhook?->rawBody();
$decodedInput = $latestWebhook?->inputPayload();
```

## Setup requirements

- keep `PAYSTACK_SECRET_KEY` configured so signature validation can succeed
- publish the webhook client migration with `php artisan vendor:publish --provider="Spatie\WebhookClient\WebhookClientServiceProvider" --tag="webhook-client-migrations"`
- run `php artisan migrate` so the `webhook_calls` table exists
- run a queue worker for webhook processing
- exclude your webhook route from CSRF protection in Laravel 11 or 12

Example CSRF exclusion in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->validateCsrfTokens(except: [
        'paystack/webhook',
    ]);
})
```

## Returned event type

Processed webhooks dispatch `PaystackWebhookReceived`, which contains `PaystackWebhookEventData`.

## Security notes

- invalid signatures are rejected before the webhook is stored
- valid but malformed payloads are stored and then fail during processing, which gives you an audit trail
- the package validates the raw request body exactly as Paystack sent it
- typed payload mapping only resolves exact supported event names and rejects missing required fields or malformed timestamps instead of silently coercing them
- typed DTOs help with ergonomics, but your application still owns idempotency, authorization, and side-effect safety

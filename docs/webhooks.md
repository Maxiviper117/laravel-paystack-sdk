# Webhooks

Webhook support is local package logic built on top of `spatie/laravel-webhook-client`. It does not go through the outbound Saloon connector layer.

## What is supported

- Paystack signature verification using the configured secret key
- persisted webhook calls in the `webhook_calls` table
- queued processing through a package-provided webhook job
- dispatch of a generic parsed Paystack webhook event object

## Register the webhook endpoint

```php
use Illuminate\Support\Facades\Route;

Route::webhooks('paystack/webhook', 'paystack');
```

## Listen for processed Paystack webhooks

```php
use Illuminate\Support\Facades\Event;
use Maxiviper117\Paystack\Events\PaystackWebhookReceived;

Event::listen(PaystackWebhookReceived::class, function (PaystackWebhookReceived $event) {
    $paystackEvent = $event->event;

    // $paystackEvent->event
    // $paystackEvent->resourceType
    // $paystackEvent->data
});
```

## Setup requirements

- keep `PAYSTACK_SECRET_KEY` configured so signature validation can succeed
- run the webhook client migration so the `webhook_calls` table exists
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

## Current boundary

The package currently dispatches a generic parsed event DTO. Typed event-specific DTO mapping is not yet implemented.

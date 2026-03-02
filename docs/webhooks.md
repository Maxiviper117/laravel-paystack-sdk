# Webhooks

Webhook support is local package logic. It does not go through the outbound Saloon connector layer.

## What is supported

- Paystack signature verification using the configured secret key
- payload parsing into a generic typed event response DTO

## Verify a webhook signature

```php
use Illuminate\Http\Request;
use Maxiviper117\Paystack\Data\Input\Webhook\VerifyWebhookSignatureInputData;
use Maxiviper117\Paystack\Facades\Paystack;

Route::post('/paystack/webhook', function (Request $request) {
    $event = Paystack::verifyWebhookSignature(
        new VerifyWebhookSignatureInputData(
            payload: $request->getContent(),
            signature: (string) $request->header('x-paystack-signature', ''),
        )
    );

    return response()->json([
        'event' => $event->event,
        'resource_type' => $event->resourceType,
    ]);
});
```

## Returned type

Webhook verification returns `VerifyWebhookSignatureResponseData`.

## Current boundary

The package currently returns a generic parsed event DTO. Typed event-specific DTO mapping and dispatch helpers are not yet implemented.

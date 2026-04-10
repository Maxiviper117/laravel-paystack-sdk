# Webhooks — developer overview

This file explains how the package receives, validates, stores, processes, and exposes Paystack webhooks for application code. It's a short, developer-oriented reference (not a user-facing VitePress page).

## Summary

- Inbound HTTP requests hit a route that uses the Spatie Webhook Client. The client verifies the Paystack signature, stores the call, and enqueues a processing job.
- The package provides a custom `PaystackWebhookCall` model and a `ProcessPaystackWebhookJob` which normalizes the payload into `PaystackWebhookEventData` and dispatches the `PaystackWebhookReceived` event.
- Consumers listen for `PaystackWebhookReceived` and handle the normalized event or a typed DTO (`typedData()`).

## Runtime flow (short)

1. Route: register an endpoint (examples in `docs/webhooks.md`). Typical: `Route::webhooks('paystack/webhook', 'paystack')` (Spatie controller).
2. Spatie client validates signature using your `PAYSTACK_SECRET_KEY`. Invalid signatures are rejected before persisting.
3. The request is stored in `webhook_calls` by `src/Models/PaystackWebhookCall.php` (overrides `storeWebhook` to save raw body and decoded input).
4. Spatie enqueues a job to process the stored call. The package uses `src/Jobs/ProcessPaystackWebhookJob.php` which sets connection/queue from `config('paystack.webhooks')` and dispatches `PaystackWebhookReceived`.
5. `PaystackWebhookEventData::fromPayload()` (in `src/Data/Output/Webhook/PaystackWebhookEventData.php`) normalizes the payload and resolves `occurredAt`, `resourceType`, `data`, `payload`, etc.
6. Consumers listen for `Maxiviper117\Paystack\Events\PaystackWebhookReceived` and react. Use `$event->event->typedData()` to get typed DTOs for supported events.

## Key files to inspect

- `docs/webhooks.md` — integration examples and consumer-facing docs.
- `src/Models/PaystackWebhookCall.php` — stored-call model and helpers (`rawBody()`, `inputPayload()` in docs).
- `src/Jobs/ProcessPaystackWebhookJob.php` — queued job, connection/queue assignment, event dispatch.
- `src/Events/PaystackWebhookReceived.php` — event dispatched after processing.
- `src/Data/Output/Webhook/PaystackWebhookEventData.php` — normalized webhook envelope.
- `src/Support/Webhooks/PaystackTypedWebhookDataResolver.php` — resolves supported typed DTOs.
- `src/Support/Webhooks/Mappers/*` — mappers for `charge.success`, `invoice.*`, `subscription.*` typed payloads.
- `src/Webhooks/PaystackWebhookResponse.php` — response returned for valid webhooks.
- `src/Webhooks/PaystackWebhookProfile.php` — Spatie profile (here: always processes).

## Supported typed events

Typed DTOs are supported for:

- `charge.success`
- `invoice.create`, `invoice.update`, `invoice.payment_failed`
- `subscription.create`, `subscription.not_renew`, `subscription.disable`

See `src/Support/Webhooks/PaystackTypedWebhookDataResolver.php` and the `Mappers` folder.

## Setup & local testing

1. Ensure `PAYSTACK_SECRET_KEY` is set in your environment (used for signature validation).
2. Publish Spatie migrations and migrate:

```bash
php artisan vendor:publish --provider="Spatie\WebhookClient\WebhookClientServiceProvider" --tag="webhook-client-migrations"
php artisan migrate
```

3. Configure route and CSRF exclusion (Laravel 11/12). Example CSRF exclusion is shown in `docs/webhooks.md`.
4. Run a queue worker so processing jobs are handled:

```bash
php artisan queue:work
```

5. (Local testing) To simulate a webhook, send a POST with the Paystack JSON body and the signature header the Spatie package expects. Example minimal curl (replace headers/signature with a valid one for testing):

```bash
curl -X POST \
  -H "Content-Type: application/json" \
  -H "x-paystack-signature: <signature>" \
  --data '{"event":"charge.success","data":{...}}' \
  http://localhost/paystack/webhook
```

If you need to bypass signature validation for quick local debug, change the webhook client config temporarily, but do not commit that change.

## Extending and consuming

- Listen for `Maxiviper117\Paystack\Events\PaystackWebhookReceived` in your application (closure, listener class, or queued listener).
- Use `$event->event->is('charge.success')` or `$event->event->typedData()` to branch on event types.
- If you need to persist or map typed payloads into your own models, use the typed DTO mappers as examples.

## Troubleshooting

- If events are not dispatched: check queue worker is running, inspect `webhook_calls` table for stored records.
- If payload parsing fails: `MalformedWebhookPayloadException` is thrown — check raw body saved by `PaystackWebhookCall::rawBody()`.
- If signatures keep failing: verify `PAYSTACK_SECRET_KEY` and that the incoming header name matches your Spatie config.

## Quick pointers

- Config: see `config/paystack.php` for `webhooks.connection` and `webhooks.queue` used by `ProcessPaystackWebhookJob`.
- Event class: `src/Events/PaystackWebhookReceived.php` (dispatched with the normalized `PaystackWebhookEventData`).
- Documentation: `docs/webhooks.md` contains example code for listeners, typed payload usage, and setup steps.

---

If you'd like, I can also add a short example listener file in `workbench` or generate a PHPUnit/Pest test that exercises `PaystackWebhookCall::storeWebhook` and `ProcessPaystackWebhookJob` with a fixture payload.

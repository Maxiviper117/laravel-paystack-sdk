<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Maxiviper117\Paystack\Models\PaystackWebhookCall;

// Real Paystack webhook intake handled by Spatie's webhook controller.
Route::post('/paystack/test/webhook', 'Spatie\WebhookClient\Http\Controllers\WebhookController')
    ->name('webhook-client-paystack');

// Workbench-only inspection route for the latest parsed webhook event.
Route::get('/paystack/test/webhook/latest-event', function () {
    return response()->json([
        'event' => Cache::get('paystack:last-webhook-event'),
    ]);
});

// Workbench-only inspection route for the latest stored webhook call.
Route::get('/paystack/test/webhook/latest-call', function () {
    $webhookCall = PaystackWebhookCall::query()->latest()->first();

    return response()->json([
        'webhook_call' => $webhookCall?->only(['id', 'name', 'url', 'headers', 'payload', 'exception', 'created_at']),
    ]);
});

// Webhook endpoint instructions returned by the workbench UI.
Route::get('/paystack/test/webhook', function () {
    return response()->json([
        'message' => 'POST a signed Paystack payload to this route. Valid calls are stored in webhook_calls and processed asynchronously.',
        'endpoint' => url('/paystack/test/webhook'),
        'latest_event_endpoint' => url('/paystack/test/webhook/latest-event'),
        'latest_call_endpoint' => url('/paystack/test/webhook/latest-call'),
    ]);
});

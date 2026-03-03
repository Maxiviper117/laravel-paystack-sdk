<?php

use Illuminate\Http\Request;
use Maxiviper117\Paystack\Jobs\ProcessPaystackWebhookJob;
use Maxiviper117\Paystack\Models\PaystackWebhookCall;
use Maxiviper117\Paystack\Webhooks\PaystackSignatureValidator;
use Maxiviper117\Paystack\Webhooks\PaystackWebhookProfile;
use Maxiviper117\Paystack\Webhooks\PaystackWebhookResponse;
use Spatie\WebhookClient\WebhookConfig;

function paystackWebhookConfig(string $signingSecret = 'sk_test_123'): WebhookConfig
{
    return new WebhookConfig([
        'name' => 'paystack',
        'signing_secret' => $signingSecret,
        'signature_header_name' => 'x-paystack-signature',
        'signature_validator' => PaystackSignatureValidator::class,
        'webhook_profile' => PaystackWebhookProfile::class,
        'webhook_response' => PaystackWebhookResponse::class,
        'webhook_model' => PaystackWebhookCall::class,
        'store_headers' => ['x-paystack-signature'],
        'process_webhook_job' => ProcessPaystackWebhookJob::class,
    ]);
}

function paystackWebhookRequest(string $payload, string $signature): Request
{
    $request = Request::create('/paystack/webhook', 'POST', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_PAYSTACK_SIGNATURE' => $signature,
    ], $payload);

    $request->headers->set('x-paystack-signature', $signature);

    return $request;
}

it('accepts a valid paystack webhook signature', function () {
    $payload = '{"event":"charge.success","data":{"id":1}}';
    $signature = hash_hmac('sha512', $payload, 'sk_test_123');

    $validator = new PaystackSignatureValidator();

    expect($validator->isValid(
        paystackWebhookRequest($payload, $signature),
        paystackWebhookConfig(),
    ))->toBeTrue();
});

it('rejects an invalid paystack webhook signature', function () {
    $validator = new PaystackSignatureValidator();

    expect($validator->isValid(
        paystackWebhookRequest('{"event":"charge.success","data":{"id":1}}', 'invalid'),
        paystackWebhookConfig(),
    ))->toBeFalse();
});

it('rejects a tampered paystack payload after signing', function () {
    $signedPayload = '{"event":"charge.success","data":{"id":1}}';
    $signature = hash_hmac('sha512', $signedPayload, 'sk_test_123');

    $validator = new PaystackSignatureValidator();

    expect($validator->isValid(
        paystackWebhookRequest('{"event":"charge.success","data":{"id":2}}', $signature),
        paystackWebhookConfig(),
    ))->toBeFalse();
});

it('rejects missing paystack webhook signatures', function () {
    $request = Request::create('/paystack/webhook', 'POST', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
    ], '{"event":"charge.success","data":{"id":1}}');

    $validator = new PaystackSignatureValidator();

    expect($validator->isValid($request, paystackWebhookConfig()))->toBeFalse();
});

it('rejects validation when the signing secret is blank', function () {
    $payload = '{"event":"charge.success","data":{"id":1}}';
    $signature = hash_hmac('sha512', $payload, 'sk_test_123');
    config()->set('paystack.secret_key', '');
    config()->set('paystack.webhooks.signing_secret', '');

    $validator = new PaystackSignatureValidator();

    expect($validator->isValid(
        paystackWebhookRequest($payload, $signature),
        paystackWebhookConfig(''),
    ))->toBeFalse();
});

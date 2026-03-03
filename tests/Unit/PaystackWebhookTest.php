<?php

use Maxiviper117\Paystack\Exceptions\InvalidWebhookSignatureException;
use Maxiviper117\Paystack\Exceptions\MalformedWebhookPayloadException;
use Maxiviper117\Paystack\Support\Webhooks\PaystackWebhook;

it('verifies a valid webhook signature', function () {
    $payload = '{"event":"charge.success","data":{"id":1}}';
    $signature = hash_hmac('sha512', $payload, 'sk_test_123');

    PaystackWebhook::verifySignature($payload, 'sk_test_123', $signature);

    expect(true)->toBeTrue();
});

it('rejects a tampered webhook signature', function () {
    $payload = '{"event":"charge.success","data":{"id":1}}';
    $signature = hash_hmac('sha512', $payload, 'sk_test_123');

    PaystackWebhook::verifySignature(
        '{"event":"charge.success","data":{"id":2}}',
        'sk_test_123',
        $signature,
    );
})->throws(InvalidWebhookSignatureException::class);

it('decodes a valid webhook object payload', function () {
    $decoded = PaystackWebhook::decodePayload('{"event":"transfer.success","data":{"id":99}}');

    expect($decoded)->toBe([
        'event' => 'transfer.success',
        'data' => [
            'id' => 99,
        ],
    ]);
});

it('rejects malformed webhook json payloads', function () {
    PaystackWebhook::decodePayload('{"event":"charge.success"');
})->throws(MalformedWebhookPayloadException::class);

it('rejects list-shaped webhook payloads', function () {
    PaystackWebhook::decodePayload('["charge.success"]');
})->throws(MalformedWebhookPayloadException::class);

it('infers webhook resource types from event names', function () {
    expect(PaystackWebhook::inferResourceType('charge.success'))->toBe('charge')
        ->and(PaystackWebhook::inferResourceType('transfer'))->toBe('transfer')
        ->and(PaystackWebhook::inferResourceType(''))->toBe('');
});

<?php

use Maxiviper117\Paystack\Actions\Webhook\VerifyWebhookSignatureAction;
use Maxiviper117\Paystack\Data\Input\Webhook\VerifyWebhookSignatureInputData;
use Maxiviper117\Paystack\Data\Output\Webhook\VerifyWebhookSignatureResponseData;
use Maxiviper117\Paystack\Exceptions\InvalidWebhookSignatureException;
use Maxiviper117\Paystack\Exceptions\MalformedWebhookPayloadException;
use Maxiviper117\Paystack\Exceptions\PaystackConfigurationException;
use Maxiviper117\Paystack\Facades\Paystack;
use Maxiviper117\Paystack\PaystackManager;

it('verifies a valid paystack webhook and returns a parsed event dto', function () {
    $payload = json_encode([
        'event' => 'charge.success',
        'data' => [
            'id' => 123,
            'domain' => 'test',
            'reference' => 'ref_123',
            'paid_at' => '2026-03-01T10:00:00+00:00',
        ],
    ], JSON_THROW_ON_ERROR);

    $signature = hash_hmac('sha512', $payload, 'sk_test_123');

    $result = app(VerifyWebhookSignatureAction::class)->execute(
        new VerifyWebhookSignatureInputData(
            payload: $payload,
            signature: $signature,
        )
    );

    expect($result)->toBeInstanceOf(VerifyWebhookSignatureResponseData::class)
        ->and($result->event)->toBe('charge.success')
        ->and($result->resourceType)->toBe('charge')
        ->and($result->domain)->toBe('test')
        ->and($result->id)->toBe(123)
        ->and($result->occurredAt)->toBe('2026-03-01T10:00:00+00:00');
});

it('supports invoking the webhook action directly', function () {
    $payload = json_encode([
        'event' => 'transfer.success',
        'data' => [
            'id' => 99,
        ],
    ], JSON_THROW_ON_ERROR);

    $signature = hash_hmac('sha512', $payload, 'sk_test_123');
    $action = app(VerifyWebhookSignatureAction::class);

    $result = $action(new VerifyWebhookSignatureInputData(
        payload: $payload,
        signature: $signature,
    ));

    expect($result->resourceType)->toBe('transfer');
});

it('handles event names without dots and falls back to created_at timestamps', function () {
    $payload = json_encode([
        'event' => 'transfer',
        'data' => [
            'id' => 99,
            'created_at' => '2026-03-02T10:00:00+00:00',
        ],
    ], JSON_THROW_ON_ERROR);

    $signature = hash_hmac('sha512', $payload, 'sk_test_123');

    $result = app(VerifyWebhookSignatureAction::class)->execute(new VerifyWebhookSignatureInputData(
        payload: $payload,
        signature: $signature,
    ));

    expect($result->resourceType)->toBe('transfer')
        ->and($result->occurredAt)->toBe('2026-03-02T10:00:00+00:00');
});

it('exposes webhook verification through the manager and facade', function () {
    $payload = json_encode([
        'event' => 'invoice.create',
        'data' => [
            'id' => 44,
        ],
    ], JSON_THROW_ON_ERROR);

    $signature = hash_hmac('sha512', $payload, 'sk_test_123');
    $input = new VerifyWebhookSignatureInputData(payload: $payload, signature: $signature);

    $managerResult = app(PaystackManager::class)->verifyWebhookSignature($input);
    $facadeResult = Paystack::verifyWebhookSignature($input);

    expect($managerResult->event)->toBe('invoice.create')
        ->and($facadeResult->event)->toBe('invoice.create');
});

it('throws for invalid webhook signatures', function () {
    $payload = json_encode([
        'event' => 'charge.success',
        'data' => [
            'id' => 1,
        ],
    ], JSON_THROW_ON_ERROR);

    app(VerifyWebhookSignatureAction::class)->execute(
        new VerifyWebhookSignatureInputData(
            payload: $payload,
            signature: 'invalid-signature',
        )
    );
})->throws(InvalidWebhookSignatureException::class);

it('throws when a signed webhook payload is tampered with after signing', function () {
    $originalPayload = json_encode([
        'event' => 'charge.success',
        'data' => [
            'id' => 1,
            'status' => 'success',
        ],
    ], JSON_THROW_ON_ERROR);

    $signature = hash_hmac('sha512', $originalPayload, 'sk_test_123');

    $tamperedPayload = json_encode([
        'event' => 'charge.success',
        'data' => [
            'id' => 1,
            'status' => 'failed',
        ],
    ], JSON_THROW_ON_ERROR);

    app(VerifyWebhookSignatureAction::class)->execute(
        new VerifyWebhookSignatureInputData(
            payload: $tamperedPayload,
            signature: $signature,
        )
    );
})->throws(InvalidWebhookSignatureException::class);

it('throws for malformed webhook payloads', function () {
    $payload = '{"event":"charge.success","data":';
    $signature = hash_hmac('sha512', $payload, 'sk_test_123');

    app(VerifyWebhookSignatureAction::class)->execute(
        new VerifyWebhookSignatureInputData(
            payload: $payload,
            signature: $signature,
        )
    );
})->throws(MalformedWebhookPayloadException::class);

it('throws for non-object webhook payloads', function () {
    $payload = json_encode(['charge.success'], JSON_THROW_ON_ERROR);
    $signature = hash_hmac('sha512', $payload, 'sk_test_123');

    app(VerifyWebhookSignatureAction::class)->execute(
        new VerifyWebhookSignatureInputData(
            payload: $payload,
            signature: $signature,
        )
    );
})->throws(MalformedWebhookPayloadException::class);

it('throws when the webhook payload is missing object data', function () {
    $payload = json_encode([
        'event' => 'charge.success',
    ], JSON_THROW_ON_ERROR);
    $signature = hash_hmac('sha512', $payload, 'sk_test_123');

    app(VerifyWebhookSignatureAction::class)->execute(
        new VerifyWebhookSignatureInputData(
            payload: $payload,
            signature: $signature,
        )
    );
})->throws(MalformedWebhookPayloadException::class);

it('throws when the webhook data payload is a list instead of an object', function () {
    $payload = json_encode([
        'event' => 'charge.success',
        'data' => ['unexpected'],
    ], JSON_THROW_ON_ERROR);
    $signature = hash_hmac('sha512', $payload, 'sk_test_123');

    app(VerifyWebhookSignatureAction::class)->execute(
        new VerifyWebhookSignatureInputData(
            payload: $payload,
            signature: $signature,
        )
    );
})->throws(MalformedWebhookPayloadException::class);

it('throws when the webhook secret key is missing', function () {
    config()->set('paystack.secret_key', '');

    $payload = json_encode([
        'event' => 'charge.success',
        'data' => [
            'id' => 1,
        ],
    ], JSON_THROW_ON_ERROR);

    $signature = hash_hmac('sha512', $payload, 'sk_test_123');

    app(VerifyWebhookSignatureAction::class)->execute(
        new VerifyWebhookSignatureInputData(
            payload: $payload,
            signature: $signature,
        )
    );
})->throws(PaystackConfigurationException::class);

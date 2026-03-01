<?php

use Maxiviper117\Paystack\Actions\Webhook\VerifyWebhookSignatureAction;
use Maxiviper117\Paystack\Data\Input\Webhook\VerifyWebhookSignatureInputData;
use Maxiviper117\Paystack\Data\Output\Webhook\VerifyWebhookSignatureResponseData;
use Maxiviper117\Paystack\Exceptions\InvalidWebhookSignatureException;
use Maxiviper117\Paystack\Exceptions\MalformedWebhookPayloadException;
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

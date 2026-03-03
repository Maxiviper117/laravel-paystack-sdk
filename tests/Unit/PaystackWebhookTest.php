<?php

use Carbon\CarbonImmutable;
use Maxiviper117\Paystack\Data\Output\Webhook\PaystackWebhookEventData;
use Maxiviper117\Paystack\Exceptions\MalformedWebhookPayloadException;
use Maxiviper117\Paystack\Support\Webhooks\PaystackWebhook;

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

it('maps a valid payload into a paystack webhook event dto', function () {
    $event = PaystackWebhookEventData::fromPayload([
        'event' => 'charge.success',
        'created_at' => '2026-03-02T10:00:00+00:00',
        'data' => [
            'id' => 99,
            'domain' => 'live',
        ],
    ]);

    expect($event->event)->toBe('charge.success')
        ->and($event->resourceType)->toBe('charge')
        ->and($event->id)->toBe(99)
        ->and($event->domain)->toBe('live')
        ->and($event->occurredAt)->toBeInstanceOf(CarbonImmutable::class)
        ->and($event->occurredAt?->toAtomString())->toBe('2026-03-02T10:00:00+00:00');
});

it('rejects payloads missing an event name when mapping event data', function () {
    PaystackWebhookEventData::fromPayload([
        'data' => [
            'id' => 1,
        ],
    ]);
})->throws(MalformedWebhookPayloadException::class);

it('rejects malformed webhook event timestamps', function () {
    PaystackWebhookEventData::fromPayload([
        'event' => 'charge.success',
        'data' => [
            'id' => 1,
            'created_at' => 'not-a-date',
        ],
    ]);
})->throws(InvalidArgumentException::class);

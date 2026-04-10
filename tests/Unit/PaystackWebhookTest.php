<?php

use Carbon\CarbonImmutable;
use Maxiviper117\Paystack\Data\Output\Webhook\PaystackWebhookEventData;
use Maxiviper117\Paystack\Enums\Webhook\PaystackWebhookEvent;
use Maxiviper117\Paystack\Exceptions\MalformedWebhookPayloadException;
use Maxiviper117\Paystack\Support\Webhooks\PaystackWebhook;

/**
 * @return array<string, mixed>
 */
function paystackWebhookFixture(string $name): array
{
    $path = __DIR__.'/../../reference/webhook_events/'.$name.'.json';
    $contents = file_get_contents($path);

    expect($contents)->not->toBeFalse();

    /** @var array<string, mixed> $decoded */
    $decoded = json_decode((string) $contents, true, 512, JSON_THROW_ON_ERROR);

    return $decoded;
}

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

it('maps list-shaped webhook payloads into the generic event dto', function () {
    $event = PaystackWebhookEventData::fromPayload(paystackWebhookFixture('subscription_expiring_cards'));

    expect($event->event)->toBe('subscription.expiring_cards')
        ->and($event->resourceType)->toBe('subscription')
        ->and($event->id)->toBeNull()
        ->and($event->domain)->toBeNull()
        ->and($event->data)->toHaveCount(1);
});

it('accepts webhook event enums when matching event data', function () {
    $event = PaystackWebhookEventData::fromPayload([
        'event' => 'invoice.create',
        'data' => [
            'id' => 101,
            'invoice_code' => 'INV_123',
            'status' => 'pending',
            'paid' => false,
            'amount' => 150000,
        ],
    ]);

    expect($event->is(PaystackWebhookEvent::InvoiceCreate))->toBeTrue()
        ->and($event->is(PaystackWebhookEvent::ChargeSuccess))->toBeFalse();
});

it('exposes the supported paystack webhook event enum values', function () {
    expect(PaystackWebhookEvent::ChargeSuccess->value)->toBe('charge.success')
        ->and(PaystackWebhookEvent::ChargeDisputeCreate->value)->toBe('charge.dispute.create')
        ->and(PaystackWebhookEvent::CustomerIdentificationSuccess->value)->toBe('customeridentification.success')
        ->and(PaystackWebhookEvent::DedicatedAccountAssignSuccess->value)->toBe('dedicatedaccount.assign.success')
        ->and(PaystackWebhookEvent::InvoiceCreate->value)->toBe('invoice.create')
        ->and(PaystackWebhookEvent::InvoiceUpdate->value)->toBe('invoice.update')
        ->and(PaystackWebhookEvent::InvoicePaymentFailed->value)->toBe('invoice.payment_failed')
        ->and(PaystackWebhookEvent::PaymentRequestPending->value)->toBe('paymentrequest.pending')
        ->and(PaystackWebhookEvent::RefundProcessing->value)->toBe('refund.processing')
        ->and(PaystackWebhookEvent::SubscriptionCreate->value)->toBe('subscription.create')
        ->and(PaystackWebhookEvent::SubscriptionNotRenew->value)->toBe('subscription.not_renew')
        ->and(PaystackWebhookEvent::SubscriptionDisable->value)->toBe('subscription.disable')
        ->and(PaystackWebhookEvent::SubscriptionExpiringCards->value)->toBe('subscription.expiring_cards')
        ->and(PaystackWebhookEvent::TransferSuccess->value)->toBe('transfer.success');
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

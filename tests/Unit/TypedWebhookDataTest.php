<?php

use Carbon\CarbonImmutable;
use Maxiviper117\Paystack\Data\Output\Webhook\PaystackWebhookEventData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\ChargeDisputeCreatedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\ChargeSuccessWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\CustomerIdentificationFailedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\CustomerIdentificationSuccessWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\DedicatedAccountAssignFailedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\DedicatedAccountAssignSuccessWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\InvoiceCreatedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\InvoicePaymentFailedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\InvoiceUpdatedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\PaymentRequestPendingWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\PaymentRequestSuccessWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\RefundFailedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\RefundProcessedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\RefundProcessingWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionCreatedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionDisabledWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionExpiringCardsWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionNotRenewingWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\TransferFailedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\TransferReversedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\TransferSuccessWebhookData;
use Maxiviper117\Paystack\Data\Subscription\SubscriptionStatus;
use Maxiviper117\Paystack\Exceptions\MalformedWebhookPayloadException;

/**
 * @return array<string, mixed>
 */
function webhookFixture(string $name): array
{
    $path = __DIR__.'/../../reference/webhook_events/'.$name.'.json';
    $contents = file_get_contents($path);

    expect($contents)->not->toBeFalse();

    /** @var array<string, mixed> $decoded */
    $decoded = json_decode((string) $contents, true, 512, JSON_THROW_ON_ERROR);

    return $decoded;
}

it('resolves typed data for charge success webhooks', function () {
    $event = PaystackWebhookEventData::fromPayload([
        'event' => 'charge.success',
        'data' => [
            'id' => 2001,
            'domain' => 'live',
            'status' => 'success',
            'reference' => 'txn_123',
            'amount' => 450000,
            'currency' => 'NGN',
            'paid_at' => '2026-03-03T08:00:00+00:00',
            'channel' => 'card',
            'gateway_response' => 'Successful',
            'customer' => [
                'email' => 'customer@example.com',
                'customer_code' => 'CUS_123',
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
            ],
        ],
    ]);

    $typedData = $event->typedData();

    expect($event->is('charge.success'))->toBeTrue();
    expect($event->supportsTypedData())->toBeTrue();
    expect($typedData)->toBeInstanceOf(ChargeSuccessWebhookData::class);

    /** @var ChargeSuccessWebhookData $typedData */
    expect($typedData->transaction->reference)->toBe('txn_123');
    expect($typedData->customer?->customerCode)->toBe('CUS_123');
    expect($typedData->gatewayResponse)->toBe('Successful');
    expect($typedData->paidAt)->toBeInstanceOf(CarbonImmutable::class);
    expect($typedData->paidAt?->toAtomString())->toBe('2026-03-03T08:00:00+00:00');
});

it('resolves typed data for invoice create webhooks', function () {
    $event = PaystackWebhookEventData::fromPayload([
        'event' => 'invoice.create',
        'data' => [
            'id' => 101,
            'domain' => 'test',
            'invoice_code' => 'INV_123',
            'status' => 'pending',
            'paid' => false,
            'amount' => 150000,
            'description' => 'March invoice',
            'period_start' => '2026-03-01T00:00:00+00:00',
            'period_end' => '2026-03-31T23:59:59+00:00',
            'next_payment_date' => '2026-04-01T00:00:00+00:00',
            'subscription' => [
                'id' => 50,
                'subscription_code' => 'SUB_123',
                'status' => 'active',
                'email_token' => 'token_123',
                'next_payment_date' => '2026-04-01T00:00:00+00:00',
                'open_invoice' => 'INV_123',
            ],
            'customer' => [
                'email' => 'billing@example.com',
                'customer_code' => 'CUS_123',
            ],
            'authorization' => [
                'authorization_code' => 'AUTH_123',
            ],
            'transaction' => [
                'id' => 901,
                'status' => 'pending',
                'reference' => 'txn_invoice_123',
                'amount' => 150000,
                'currency' => 'NGN',
            ],
        ],
    ]);

    $typedData = $event->typedData();

    expect($typedData)->toBeInstanceOf(InvoiceCreatedWebhookData::class);

    /** @var InvoiceCreatedWebhookData $typedData */
    expect($typedData->invoiceCode)->toBe('INV_123');
    expect($typedData->subscriptionCode)->toBe('SUB_123');
    expect($typedData->authorizationCode)->toBe('AUTH_123');
    expect($typedData->transaction?->reference)->toBe('txn_invoice_123');
    expect($typedData->nextPaymentDate)->toBeInstanceOf(CarbonImmutable::class);
    expect($typedData->nextPaymentDate?->toAtomString())->toBe('2026-04-01T00:00:00+00:00');
});

it('resolves typed data for invoice update webhooks', function () {
    $event = PaystackWebhookEventData::fromPayload([
        'event' => 'invoice.update',
        'data' => [
            'id' => 102,
            'domain' => 'test',
            'invoice_code' => 'INV_124',
            'status' => 'success',
            'paid' => true,
            'amount' => 150000,
        ],
    ]);

    $typedData = $event->typedData();

    expect($typedData)->toBeInstanceOf(InvoiceUpdatedWebhookData::class);

    /** @var InvoiceUpdatedWebhookData $typedData */
    expect($typedData->paid)->toBeTrue();
});

it('resolves typed data for invoice payment failed webhooks', function () {
    $event = PaystackWebhookEventData::fromPayload([
        'event' => 'invoice.payment_failed',
        'data' => [
            'id' => 103,
            'domain' => 'live',
            'invoice_code' => 'INV_125',
            'status' => 'failed',
            'paid' => 0,
            'amount' => 175000,
        ],
    ]);

    $typedData = $event->typedData();

    expect($typedData)->toBeInstanceOf(InvoicePaymentFailedWebhookData::class);

    /** @var InvoicePaymentFailedWebhookData $typedData */
    expect($typedData->paid)->toBeFalse();
});

it('resolves typed data for subscription create webhooks', function () {
    $event = PaystackWebhookEventData::fromPayload([
        'event' => 'subscription.create',
        'data' => [
            'id' => 200,
            'domain' => 'test',
            'subscription_code' => 'SUB_200',
            'status' => 'active',
            'email_token' => 'email_token_200',
            'amount' => 125000,
            'next_payment_date' => '2026-04-05T00:00:00+00:00',
            'open_invoice' => 'INV_200',
            'plan' => [
                'id' => 20,
                'name' => 'Pro',
                'plan_code' => 'PLAN_PRO',
                'amount' => 125000,
                'interval' => 'monthly',
            ],
            'customer' => [
                'email' => 'subscriber@example.com',
                'customer_code' => 'CUS_200',
            ],
        ],
    ]);

    $typedData = $event->typedData();

    expect($typedData)->toBeInstanceOf(SubscriptionCreatedWebhookData::class);

    /** @var SubscriptionCreatedWebhookData $typedData */
    expect($typedData->plan?->planCode)->toBe('PLAN_PRO');
    expect($typedData->customer?->customerCode)->toBe('CUS_200');
    expect($typedData->nextPaymentDate)->toBeInstanceOf(CarbonImmutable::class);
    expect($typedData->nextPaymentDate?->toAtomString())->toBe('2026-04-05T00:00:00+00:00');
});

it('resolves typed data for subscription not renew webhooks', function () {
    $event = PaystackWebhookEventData::fromPayload([
        'event' => 'subscription.not_renew',
        'data' => [
            'id' => 201,
            'subscription_code' => 'SUB_201',
            'status' => 'non-renewing',
        ],
    ]);

    $typedData = $event->typedData();

    expect($typedData)->toBeInstanceOf(SubscriptionNotRenewingWebhookData::class);

    /** @var SubscriptionNotRenewingWebhookData $typedData */
    expect($typedData->status)->toBe(SubscriptionStatus::NonRenewing);
});

it('resolves typed data for subscription disable webhooks', function () {
    $event = PaystackWebhookEventData::fromPayload([
        'event' => 'subscription.disable',
        'data' => [
            'id' => 202,
            'subscription_code' => 'SUB_202',
            'status' => 'complete',
        ],
    ]);

    $typedData = $event->typedData();

    expect($typedData)->toBeInstanceOf(SubscriptionDisabledWebhookData::class);

    /** @var SubscriptionDisabledWebhookData $typedData */
    expect($typedData->subscriptionCode)->toBe('SUB_202');
});

it('resolves typed data for charge dispute create webhooks from extracted samples', function () {
    $event = PaystackWebhookEventData::fromPayload(webhookFixture('charge_dispute_create'));

    $typedData = $event->typedData();

    expect($typedData)->toBeInstanceOf(ChargeDisputeCreatedWebhookData::class);

    /** @var ChargeDisputeCreatedWebhookData $typedData */
    expect($typedData->disputeId)->toBe(358950)
        ->and($typedData->status)->toBe('awaiting-merchant-feedback')
        ->and($typedData->dispute->transaction?->reference)->toBe('v3mjfgbnc19v97x');
});

it('resolves typed data for customer identification success webhooks from extracted samples', function () {
    $event = PaystackWebhookEventData::fromPayload(webhookFixture('customeridentification_success'));

    $typedData = $event->typedData();

    expect($typedData)->toBeInstanceOf(CustomerIdentificationSuccessWebhookData::class);

    /** @var CustomerIdentificationSuccessWebhookData $typedData */
    expect($typedData->customerCode)->toBe('CUS_xnxdt6s1zg1f4nx')
        ->and($typedData->customerId)->toBe('9387490384')
        ->and($typedData->identification->type)->toBe('bvn');
});

it('resolves typed data for customer identification failed webhooks from extracted samples', function () {
    $event = PaystackWebhookEventData::fromPayload(webhookFixture('customeridentification_failed'));

    $typedData = $event->typedData();

    expect($typedData)->toBeInstanceOf(CustomerIdentificationFailedWebhookData::class);

    /** @var CustomerIdentificationFailedWebhookData $typedData */
    expect($typedData->reason)->toBe('Account number or BVN is incorrect')
        ->and($typedData->customerId)->toBe('82796315')
        ->and($typedData->identification->accountNumber)->toBe('012****345');
});

it('resolves typed data for dedicated account assignment webhooks from extracted samples', function () {
    $successEvent = PaystackWebhookEventData::fromPayload(webhookFixture('dedicatedaccount_assign_success'));
    $failedEvent = PaystackWebhookEventData::fromPayload(webhookFixture('dedicatedaccount_assign_failed'));

    $successTyped = $successEvent->typedData();
    $failedTyped = $failedEvent->typedData();

    expect($successTyped)->toBeInstanceOf(DedicatedAccountAssignSuccessWebhookData::class)
        ->and($failedTyped)->toBeInstanceOf(DedicatedAccountAssignFailedWebhookData::class);

    /** @var DedicatedAccountAssignSuccessWebhookData $successTyped */
    /** @var DedicatedAccountAssignFailedWebhookData $failedTyped */
    expect($successTyped->dedicatedAccount?->accountNumber)->toBe('1234567890')
        ->and($failedTyped->dedicatedAccount)->toBeNull();
});

it('resolves typed data for payment request webhooks from extracted samples', function () {
    $pendingEvent = PaystackWebhookEventData::fromPayload(webhookFixture('paymentrequest_pending'));
    $successEvent = PaystackWebhookEventData::fromPayload(webhookFixture('paymentrequest_success'));

    $pendingTyped = $pendingEvent->typedData();
    $successTyped = $successEvent->typedData();

    expect($pendingTyped)->toBeInstanceOf(PaymentRequestPendingWebhookData::class)
        ->and($successTyped)->toBeInstanceOf(PaymentRequestSuccessWebhookData::class);

    /** @var PaymentRequestPendingWebhookData $pendingTyped */
    /** @var PaymentRequestSuccessWebhookData $successTyped */
    expect($pendingTyped->paid)->toBeFalse()
        ->and($successTyped->paid)->toBeTrue()
        ->and($successTyped->requestCode)->toBe('PRQ_y0paeo93jh99mho');
});

it('resolves typed data for refund webhooks from extracted samples', function () {
    $processingEvent = PaystackWebhookEventData::fromPayload(webhookFixture('refund_processing'));
    $processedEvent = PaystackWebhookEventData::fromPayload(webhookFixture('refund_processed'));
    $failedEvent = PaystackWebhookEventData::fromPayload(webhookFixture('refund_failed'));

    $processingTyped = $processingEvent->typedData();
    $processedTyped = $processedEvent->typedData();
    $failedTyped = $failedEvent->typedData();

    expect($processingTyped)->toBeInstanceOf(RefundProcessingWebhookData::class)
        ->and($processedTyped)->toBeInstanceOf(RefundProcessedWebhookData::class)
        ->and($failedTyped)->toBeInstanceOf(RefundFailedWebhookData::class);

    /** @var RefundProcessingWebhookData $processingTyped */
    /** @var RefundProcessedWebhookData $processedTyped */
    /** @var RefundFailedWebhookData $failedTyped */
    expect($processingTyped->status)->toBe('processing')
        ->and($processedTyped->status)->toBe('processed')
        ->and($failedTyped->status)->toBe('failed');
});

it('resolves typed data for subscription expiring cards webhooks from extracted samples', function () {
    $event = PaystackWebhookEventData::fromPayload(webhookFixture('subscription_expiring_cards'));

    $typedData = $event->typedData();

    expect($typedData)->toBeInstanceOf(SubscriptionExpiringCardsWebhookData::class);

    /** @var SubscriptionExpiringCardsWebhookData $typedData */
    expect($typedData->cards)->toHaveCount(1)
        ->and($typedData->cards[0]->subscription->subscriptionCode)->toBe('SUB_lejj927x2kxciw1')
        ->and($typedData->cards[0]->customer->customerCode)->toBe('CUS_8v6g420rc16spqw');
});

it('resolves typed data for transfer webhooks from extracted samples', function () {
    $successEvent = PaystackWebhookEventData::fromPayload(webhookFixture('transfer_success'));
    $failedEvent = PaystackWebhookEventData::fromPayload(webhookFixture('transfer_failed'));
    $reversedEvent = PaystackWebhookEventData::fromPayload(webhookFixture('transfer_reversed'));

    $successTyped = $successEvent->typedData();
    $failedTyped = $failedEvent->typedData();
    $reversedTyped = $reversedEvent->typedData();

    expect($successTyped)->toBeInstanceOf(TransferSuccessWebhookData::class)
        ->and($failedTyped)->toBeInstanceOf(TransferFailedWebhookData::class)
        ->and($reversedTyped)->toBeInstanceOf(TransferReversedWebhookData::class);

    /** @var TransferSuccessWebhookData $successTyped */
    /** @var TransferFailedWebhookData $failedTyped */
    /** @var TransferReversedWebhookData $reversedTyped */
    expect($successTyped->reference)->toBe('acv_9ee55786-2323-4760-98e2-6380c9cb3f68')
        ->and($failedTyped->reference)->toBe('1976435206')
        ->and($reversedTyped->reference)->toBe('jvrjckwenm');
});

it('returns null typed data for unsupported webhook events', function () {
    $event = PaystackWebhookEventData::fromPayload([
        'event' => 'customer.create',
        'data' => [
            'id' => 300,
        ],
    ]);

    expect($event->supportsTypedData())->toBeFalse()
        ->and($event->typedData())->toBeNull();
});

it('rejects supported invoice events with missing invoice identifiers', function () {
    $event = PaystackWebhookEventData::fromPayload([
        'event' => 'invoice.create',
        'data' => [
            'status' => 'pending',
            'paid' => false,
            'amount' => 10000,
        ],
    ]);

    $event->typedData();
})->throws(MalformedWebhookPayloadException::class);

it('rejects supported charge events with missing numeric amounts', function () {
    $event = PaystackWebhookEventData::fromPayload([
        'event' => 'charge.success',
        'data' => [
            'reference' => 'txn_bad',
            'status' => 'success',
        ],
    ]);

    $event->typedData();
})->throws(MalformedWebhookPayloadException::class);

it('rejects malformed typed webhook timestamps', function () {
    $event = PaystackWebhookEventData::fromPayload([
        'event' => 'subscription.create',
        'data' => [
            'subscription_code' => 'SUB_BAD',
            'status' => 'active',
            'next_payment_date' => 'not-a-date',
        ],
    ]);

    $event->typedData();
})->throws(InvalidArgumentException::class);

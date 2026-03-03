<?php

use Maxiviper117\Paystack\Data\Output\Webhook\PaystackWebhookEventData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\ChargeSuccessWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\InvoiceCreatedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\InvoicePaymentFailedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\InvoiceUpdatedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionCreatedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionDisabledWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionNotRenewingWebhookData;
use Maxiviper117\Paystack\Exceptions\MalformedWebhookPayloadException;

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
    expect($typedData->status)->toBe('non-renewing');
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

it('returns null typed data for unsupported webhook events', function () {
    $event = PaystackWebhookEventData::fromPayload([
        'event' => 'transfer.success',
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

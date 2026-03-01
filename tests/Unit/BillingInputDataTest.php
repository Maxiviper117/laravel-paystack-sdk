<?php

use Maxiviper117\Paystack\Data\Input\Plan\CreatePlanInputData;
use Maxiviper117\Paystack\Data\Input\Plan\ListPlansInputData;
use Maxiviper117\Paystack\Data\Input\Plan\UpdatePlanInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\CreateSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\DisableSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\EnableSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\ListSubscriptionsInputData;
use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;

it('serializes plan input data for create and update requests', function () {
    $create = new CreatePlanInputData(
        name: 'Starter',
        amount: 5000,
        interval: 'monthly',
        invoiceLimit: 12,
        sendInvoices: true,
    );

    $update = new UpdatePlanInputData(
        planCode: 'PLN_start',
        amount: 7500,
        sendSms: false,
    );

    expect($create->toRequestBody())->toBe([
        'name' => 'Starter',
        'amount' => '500000',
        'interval' => 'monthly',
        'invoice_limit' => 12,
        'send_invoices' => true,
    ])->and($update->toRequestBody())->toBe([
        'amount' => '750000',
        'send_sms' => false,
    ]);
});

it('serializes plan and subscription list filters', function () {
    $plans = new ListPlansInputData(perPage: 50, status: 'active');
    $subscriptions = new ListSubscriptionsInputData(perPage: 25, customer: 'CUS_123');

    expect($plans->toRequestQuery())->toBe([
        'perPage' => 50,
        'status' => 'active',
    ])->and($subscriptions->toRequestQuery())->toBe([
        'perPage' => 25,
        'customer' => 'CUS_123',
    ]);
});

it('serializes subscription body inputs', function () {
    $create = new CreateSubscriptionInputData(
        customer: 'CUS_123',
        plan: 'PLN_start',
        authorization: 'AUTH_123',
        startDate: '2026-04-01',
    );

    $enable = new EnableSubscriptionInputData(code: 'SUB_123', token: 'TOKEN_123');
    $disable = new DisableSubscriptionInputData(code: 'SUB_123', token: 'TOKEN_123');

    expect($create->toRequestBody())->toBe([
        'customer' => 'CUS_123',
        'plan' => 'PLN_start',
        'authorization' => 'AUTH_123',
        'start_date' => '2026-04-01',
    ])->and($enable->toRequestBody())->toBe([
        'code' => 'SUB_123',
        'token' => 'TOKEN_123',
    ])->and($disable->toRequestBody())->toBe([
        'code' => 'SUB_123',
        'token' => 'TOKEN_123',
    ]);
});

it('rejects invalid billing input at construction time', function () {
    new EnableSubscriptionInputData(code: '', token: '');
})->throws(InvalidPaystackInputException::class);

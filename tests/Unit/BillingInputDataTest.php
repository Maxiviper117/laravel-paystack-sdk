<?php

use Maxiviper117\Paystack\Data\Input\Plan\CreatePlanInputData;
use Maxiviper117\Paystack\Data\Input\Plan\FetchPlanInputData;
use Maxiviper117\Paystack\Data\Input\Plan\ListPlansInputData;
use Maxiviper117\Paystack\Data\Input\Plan\UpdatePlanInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\CreateSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\DisableSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\EnableSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\FetchSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\ListSubscriptionsInputData;
use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;

it('serializes plan input data for create and update requests', function () {
    $create = new CreatePlanInputData(
        name: 'Starter',
        amount: 5000,
        interval: 'monthly',
        description: 'Starter plan',
        currency: 'NGN',
        invoiceLimit: 12,
        sendInvoices: true,
        extra: ['send_sms' => true],
    );

    $update = new UpdatePlanInputData(
        planCode: 'PLN_start',
        amount: 7500,
        sendSms: false,
        extra: ['description' => 'Updated'],
    );

    expect($create->toRequestBody())->toBe([
        'send_sms' => true,
        'name' => 'Starter',
        'amount' => '500000',
        'interval' => 'monthly',
        'description' => 'Starter plan',
        'currency' => 'NGN',
        'invoice_limit' => 12,
        'send_invoices' => true,
    ])->and($update->toRequestBody())->toBe([
        'description' => 'Updated',
        'amount' => '750000',
        'send_sms' => false,
    ]);
});

it('serializes plan and subscription list filters', function () {
    $plans = new ListPlansInputData(perPage: 50, status: 'active', extra: ['amount' => 500000]);
    $subscriptions = new ListSubscriptionsInputData(perPage: 25, customer: 'CUS_123', extra: ['plan' => 'PLN_start']);

    expect($plans->toRequestQuery())->toBe([
        'amount' => 500000,
        'perPage' => 50,
        'status' => 'active',
    ])->and($subscriptions->toRequestQuery())->toBe([
        'plan' => 'PLN_start',
        'perPage' => 25,
        'customer' => 'CUS_123',
    ]);
});

it('serializes subscription body inputs', function () {
    $create = new CreateSubscriptionInputData(
        customer: 'CUS_123',
        plan: 'PLN_start',
        startDate: '2026-04-01',
        extra: ['invoice_limit' => 3],
    );

    $enable = new EnableSubscriptionInputData(code: 'SUB_123', token: 'TOKEN_123');
    $disable = new DisableSubscriptionInputData(code: 'SUB_123', token: 'TOKEN_123');

    expect($create->toRequestBody())->toBe([
        'invoice_limit' => 3,
        'customer' => 'CUS_123',
        'plan' => 'PLN_start',
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

it('rejects invalid billing identifiers at construction time', function () {
    new FetchPlanInputData('   ');
})->throws(InvalidPaystackInputException::class);

it('rejects invalid billing list or create input at construction time', function (callable $factory) {
    $factory();
})->with([
    'negative invoice limit' => fn () => new CreatePlanInputData(
        name: 'Starter',
        amount: 5000,
        interval: 'monthly',
        invoiceLimit: -1,
    ),
    'empty plan name' => fn () => new CreatePlanInputData(
        name: ' ',
        amount: 5000,
        interval: 'monthly',
    ),
    'empty plan interval' => fn () => new CreatePlanInputData(
        name: 'Starter',
        amount: 5000,
        interval: ' ',
    ),
    'empty subscription customer' => fn () => new CreateSubscriptionInputData(
        customer: ' ',
        plan: 'PLN_start',
    ),
    'empty subscription plan' => fn () => new CreateSubscriptionInputData(
        customer: 'CUS_123',
        plan: ' ',
    ),
    'empty fetch subscription identifier' => fn () => new FetchSubscriptionInputData('  '),
    'invalid subscriptions per page' => fn () => new ListSubscriptionsInputData(perPage: 0),
    'invalid plans page' => fn () => new ListPlansInputData(page: 0),
])->throws(InvalidPaystackInputException::class);

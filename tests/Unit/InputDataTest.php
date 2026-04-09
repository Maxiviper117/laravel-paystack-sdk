<?php

use Maxiviper117\Paystack\Data\Input\Customer\CreateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\FetchCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\ListCustomersInputData;
use Maxiviper117\Paystack\Data\Input\Customer\SetCustomerRiskActionInputData;
use Maxiviper117\Paystack\Data\Input\Customer\UpdateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\ValidateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Plan\UpdatePlanInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\GenerateSubscriptionUpdateLinkInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\SendSubscriptionUpdateLinkInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\FetchTransactionInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\ListTransactionsInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;
use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;

it('serializes initialize transaction input data for the request body', function () {
    $input = new InitializeTransactionInputData(
        email: 'jane@example.com',
        amount: 15.5,
        callbackUrl: 'https://example.com/callback',
        metadata: ['order_id' => 'ORD-123'],
        currency: 'NGN',
        extra: ['channels' => ['card']],
        channels: ['card', 'bank_transfer'],
        reference: 'ref_123',
        plan: 'PLN_123',
        invoiceLimit: 3,
        splitCode: 'SPL_123',
        subaccount: 'ACCT_123',
        transactionCharge: 250,
        bearer: 'subaccount',
    );

    expect($input->toRequestBody())->toBe([
        'channels' => ['card', 'bank_transfer'],
        'callback_url' => 'https://example.com/callback',
        'reference' => 'ref_123',
        'plan' => 'PLN_123',
        'invoice_limit' => 3,
        'currency' => 'NGN',
        'metadata' => '{"order_id":"ORD-123"}',
        'split_code' => 'SPL_123',
        'subaccount' => 'ACCT_123',
        'transaction_charge' => 250,
        'bearer' => 'subaccount',
        'email' => 'jane@example.com',
        'amount' => '1550',
    ]);
});

it('serializes customer input data for request bodies and omits identifiers from updates', function () {
    $create = new CreateCustomerInputData(
        email: 'jane@example.com',
        firstName: 'Jane',
        metadata: ['crm_id' => 'CRM-123'],
    );

    $update = new UpdateCustomerInputData(
        customerCode: 'CUS_123',
        firstName: 'Janet',
        metadata: ['crm_id' => 'CRM-456'],
    );

    expect($create->toRequestBody())->toBe([
        'email' => 'jane@example.com',
        'first_name' => 'Jane',
        'metadata' => ['crm_id' => 'CRM-123'],
    ])->and($update->toRequestBody())->toBe([
        'first_name' => 'Janet',
        'metadata' => ['crm_id' => 'CRM-456'],
    ]);
});

it('serializes fetch customer input data and risk validation bodies', function () {
    $validate = new ValidateCustomerInputData(
        customerCode: 'CUS_123',
        country: 'NG',
        type: 'bank_account',
        firstName: 'Asta',
        lastName: 'Lavista',
        bvn: '200123456677',
        bankCode: '007',
        accountNumber: '0123456789',
        extra: ['middle_name' => 'Jane'],
    );

    $riskAction = new SetCustomerRiskActionInputData(
        customer: 'CUS_123',
        riskAction: 'deny',
        extra: ['note' => 'manual review'],
    );

    expect((new FetchCustomerInputData('CUS_123'))->emailOrCode)->toBe('CUS_123')
        ->and($validate->toRequestBody())->toBe([
            'middle_name' => 'Jane',
            'country' => 'NG',
            'type' => 'bank_account',
            'first_name' => 'Asta',
            'last_name' => 'Lavista',
            'bvn' => '200123456677',
            'bank_code' => '007',
            'account_number' => '0123456789',
        ])
        ->and($riskAction->toRequestBody())->toBe([
            'note' => 'manual review',
            'customer' => 'CUS_123',
            'risk_action' => 'deny',
        ]);
});

it('serializes list filters to request queries', function () {
    $transactions = new ListTransactionsInputData(perPage: 50, page: 2, customer: 'CUS_123', status: 'success', extra: ['amount' => 5000], terminalId: 'TAL_123');
    $customers = new ListCustomersInputData(perPage: 25, email: 'jane@example.com', extra: ['from' => '2026-01-01']);

    expect($transactions->toRequestQuery())->toBe([
        'amount' => 5000,
        'perPage' => 50,
        'page' => 2,
        'customer' => 'CUS_123',
        'terminalid' => 'TAL_123',
        'status' => 'success',
    ])->and($customers->toRequestQuery())->toBe([
        'from' => '2026-01-01',
        'perPage' => 25,
        'email' => 'jane@example.com',
    ]);
});

it('serializes update plan input data including existing subscription updates', function () {
    $input = new UpdatePlanInputData(
        planCode: 'PLN_123',
        name: 'Starter',
        amount: 49.99,
        interval: 'monthly',
        updateExistingSubscriptions: true,
    );

    expect($input->toRequestBody())->toBe([
        'name' => 'Starter',
        'amount' => '4999',
        'interval' => 'monthly',
        'update_existing_subscriptions' => true,
    ]);
});

it('rejects invalid customer dto input at construction time', function () {
    new CreateCustomerInputData(email: 'not-an-email');
})->throws(InvalidPaystackInputException::class);

it('rejects invalid transaction dto input at construction time', function () {
    new InitializeTransactionInputData(email: 'jane@example.com', amount: -1);
})->throws(InvalidPaystackInputException::class);

it('rejects invalid fetch customer identifiers at construction time', function () {
    new FetchCustomerInputData('   ');
})->throws(InvalidPaystackInputException::class);

it('rejects invalid subscription update link identifiers at construction time', function () {
    new GenerateSubscriptionUpdateLinkInputData(' ');
})->throws(InvalidPaystackInputException::class);

it('rejects invalid subscription email link identifiers at construction time', function () {
    new SendSubscriptionUpdateLinkInputData('');
})->throws(InvalidPaystackInputException::class);

it('rejects invalid validate customer input at construction time', function () {
    new ValidateCustomerInputData(
        customerCode: 'CUS_123',
        country: 'NG',
        type: 'bank_account',
        bvn: '200123456677',
    );
})->throws(InvalidPaystackInputException::class);

it('rejects invalid customer risk actions at construction time', function () {
    new SetCustomerRiskActionInputData(customer: 'CUS_123', riskAction: 'block');
})->throws(InvalidPaystackInputException::class);

it('rejects empty initialize transaction references at construction time', function () {
    new InitializeTransactionInputData(email: 'jane@example.com', amount: 10, reference: '   ');
})->throws(InvalidPaystackInputException::class);

it('rejects negative initialize transaction invoice limits at construction time', function () {
    new InitializeTransactionInputData(email: 'jane@example.com', amount: 10, invoiceLimit: -1);
})->throws(InvalidPaystackInputException::class);

it('rejects empty transaction references at construction time', function () {
    new VerifyTransactionInputData(reference: '   ');
})->throws(InvalidPaystackInputException::class);

it('rejects invalid fetch transaction identifiers at construction time', function () {
    new FetchTransactionInputData(0);
})->throws(InvalidPaystackInputException::class);

it('rejects invalid customer update identifiers at construction time', function () {
    new UpdateCustomerInputData(customerCode: '   ');
})->throws(InvalidPaystackInputException::class);

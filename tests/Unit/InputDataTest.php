<?php

use Maxiviper117\Paystack\Data\Input\Customer\CreateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\ListCustomersInputData;
use Maxiviper117\Paystack\Data\Input\Customer\UpdateCustomerInputData;
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
        extra: ['channels' => ['card']],
    );

    expect($input->toRequestBody())->toBe([
        'channels' => ['card'],
        'callback_url' => 'https://example.com/callback',
        'metadata' => '{"order_id":"ORD-123"}',
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

it('serializes list filters to request queries', function () {
    $transactions = new ListTransactionsInputData(perPage: 50, page: 2, status: 'success', extra: ['customer' => 'CUS_123']);
    $customers = new ListCustomersInputData(perPage: 25, email: 'jane@example.com', extra: ['from' => '2026-01-01']);

    expect($transactions->toRequestQuery())->toBe([
        'customer' => 'CUS_123',
        'perPage' => 50,
        'page' => 2,
        'status' => 'success',
    ])->and($customers->toRequestQuery())->toBe([
        'from' => '2026-01-01',
        'perPage' => 25,
        'email' => 'jane@example.com',
    ]);
});

it('rejects invalid customer dto input at construction time', function () {
    new CreateCustomerInputData(email: 'not-an-email');
})->throws(InvalidPaystackInputException::class);

it('rejects invalid transaction dto input at construction time', function () {
    new InitializeTransactionInputData(email: 'jane@example.com', amount: -1);
})->throws(InvalidPaystackInputException::class);

it('rejects empty transaction references at construction time', function () {
    new VerifyTransactionInputData(reference: '   ');
})->throws(InvalidPaystackInputException::class);

it('rejects invalid customer update identifiers at construction time', function () {
    new UpdateCustomerInputData(customerCode: '   ');
})->throws(InvalidPaystackInputException::class);

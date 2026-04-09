<?php

use Maxiviper117\Paystack\Data\Input\Refund\CreateRefundInputData;
use Maxiviper117\Paystack\Data\Input\Refund\FetchRefundInputData;
use Maxiviper117\Paystack\Data\Input\Refund\ListRefundsInputData;
use Maxiviper117\Paystack\Data\Input\Refund\RefundAccountDetailsInputData;
use Maxiviper117\Paystack\Data\Input\Refund\RetryRefundInputData;
use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;

it('serializes refund input data for create, list, and retry requests', function () {
    $create = new CreateRefundInputData(
        transaction: 'T685312322670591',
        amount: '10000',
        currency: 'NGN',
        customerNote: 'Changed my mind',
        merchantNote: 'Approved by support',
        extra: ['source' => 'workbench'],
    );

    $list = new ListRefundsInputData(
        transaction: '1641',
        currency: 'NGN',
        from: '2026-01-01',
        to: '2026-12-31',
        perPage: 25,
        page: 2,
        extra: ['status' => 'pending'],
    );

    $accountDetails = new RefundAccountDetailsInputData(
        currency: 'NGN',
        accountNumber: '1234567890',
        bankId: '9',
    );

    $retry = new RetryRefundInputData(
        id: 1234567,
        refundAccountDetails: $accountDetails,
    );

    expect($create->toRequestBody())->toBe([
        'source' => 'workbench',
        'transaction' => 'T685312322670591',
        'amount' => 10000,
        'currency' => 'NGN',
        'customer_note' => 'Changed my mind',
        'merchant_note' => 'Approved by support',
    ])->and($list->toRequestQuery())->toBe([
        'status' => 'pending',
        'transaction' => '1641',
        'currency' => 'NGN',
        'from' => '2026-01-01',
        'to' => '2026-12-31',
        'perPage' => 25,
        'page' => 2,
    ])->and($accountDetails->toRequestBody())->toBe([
        'currency' => 'NGN',
        'account_number' => '1234567890',
        'bank_id' => '9',
    ])->and($retry->toRequestBody())->toBe([
        'refund_account_details' => [
            'currency' => 'NGN',
            'account_number' => '1234567890',
            'bank_id' => '9',
        ],
    ]);
});

it('rejects invalid refund dto input at construction time', function (callable $factory) {
    $factory();
})->with([
    'empty create transaction' => fn () => new CreateRefundInputData(transaction: '   '),
    'negative refund amount' => fn () => new CreateRefundInputData(transaction: '1641', amount: -1),
    'empty list transaction filter' => fn () => new ListRefundsInputData(transaction: '   '),
    'invalid list page size' => fn () => new ListRefundsInputData(perPage: 0),
    'invalid retry identifier' => fn () => new RetryRefundInputData(
        id: 0,
        refundAccountDetails: new RefundAccountDetailsInputData(currency: 'NGN', accountNumber: '1234567890', bankId: '9'),
    ),
    'empty retry currency' => fn () => new RefundAccountDetailsInputData(currency: ' ', accountNumber: '1234567890', bankId: '9'),
    'empty retry account number' => fn () => new RefundAccountDetailsInputData(currency: 'NGN', accountNumber: ' ', bankId: '9'),
    'empty retry bank id' => fn () => new RefundAccountDetailsInputData(currency: 'NGN', accountNumber: '1234567890', bankId: ' '),
])->throws(InvalidPaystackInputException::class);

it('rejects invalid fetch refund identifiers at construction time', function () {
    new FetchRefundInputData('   ');
})->throws(InvalidPaystackInputException::class);

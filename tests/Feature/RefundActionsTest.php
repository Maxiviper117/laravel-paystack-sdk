<?php

use Carbon\CarbonImmutable;
use Maxiviper117\Paystack\Actions\Refund\CreateRefundAction;
use Maxiviper117\Paystack\Actions\Refund\FetchRefundAction;
use Maxiviper117\Paystack\Actions\Refund\ListRefundsAction;
use Maxiviper117\Paystack\Actions\Refund\RetryRefundAction;
use Maxiviper117\Paystack\Data\Input\Refund\CreateRefundInputData;
use Maxiviper117\Paystack\Data\Input\Refund\FetchRefundInputData;
use Maxiviper117\Paystack\Data\Input\Refund\ListRefundsInputData;
use Maxiviper117\Paystack\Data\Input\Refund\RefundAccountDetailsInputData;
use Maxiviper117\Paystack\Data\Input\Refund\RetryRefundInputData;
use Maxiviper117\Paystack\Data\Output\Refund\CreateRefundResponseData;
use Maxiviper117\Paystack\Data\Output\Refund\FetchRefundResponseData;
use Maxiviper117\Paystack\Data\Output\Refund\ListRefundsResponseData;
use Maxiviper117\Paystack\Data\Output\Refund\RetryRefundResponseData;
use Maxiviper117\Paystack\Data\Refund\RefundStatus;
use Maxiviper117\Paystack\Data\Transaction\TransactionData;
use Maxiviper117\Paystack\Facades\Paystack;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Refund\CreateRefundRequest;
use Maxiviper117\Paystack\Integrations\Requests\Refund\FetchRefundRequest;
use Maxiviper117\Paystack\Integrations\Requests\Refund\ListRefundsRequest;
use Maxiviper117\Paystack\Integrations\Requests\Refund\RetryRefundRequest;
use Maxiviper117\Paystack\PaystackManager;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

it('creates a refund and maps a nested transaction payload', function () {
    $mockClient = new MockClient([
        CreateRefundRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Refund has been queued for processing',
            'data' => [
                'id' => 3018284,
                'integration' => 412829,
                'transaction' => [
                    'id' => 1004723697,
                    'reference' => 'T685312322670591',
                    'amount' => 10000,
                    'currency' => 'NGN',
                ],
                'amount' => 10000,
                'currency' => 'NGN',
                'status' => 'pending',
                'createdAt' => '2026-03-01T10:00:00+00:00',
            ],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $result = app(CreateRefundAction::class)->execute(new CreateRefundInputData(
        transaction: 'T685312322670591',
        amount: 10000,
        currency: 'NGN',
    ));

    assert($result->refund->transaction instanceof TransactionData);

    expect($result)->toBeInstanceOf(CreateRefundResponseData::class)
        ->and($result->refund->transaction->reference)->toBe('T685312322670591')
        ->and($result->refund->createdAt)->toBeInstanceOf(CarbonImmutable::class);

    $mockClient->assertSent(fn (Request $request) => $request instanceof CreateRefundRequest
        && $request->body()->all()['transaction'] === 'T685312322670591'
        && $request->body()->all()['amount'] === 10000
        && $request->body()->all()['currency'] === 'NGN');
});

it('fetches a refund and lists refunds with pagination', function () {
    $mockClient = new MockClient([
        FetchRefundRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Refund retrieved',
            'data' => [
                'id' => 1,
                'integration' => 100982,
                'transaction' => 1641,
                'amount' => 500000,
                'currency' => 'NGN',
                'status' => 'processed',
                'refunded_at' => '2026-03-01T10:00:00+00:00',
            ],
        ], 200),
        ListRefundsRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Refunds retrieved',
            'data' => [
                [
                    'id' => 1,
                    'integration' => 100982,
                    'transaction' => 1641,
                    'amount' => 500000,
                    'currency' => 'NGN',
                    'status' => 'processed',
                ],
            ],
            'meta' => [
                'perPage' => 50,
                'page' => 1,
            ],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $fetched = app(FetchRefundAction::class)->execute(new FetchRefundInputData(1));
    $listed = app(ListRefundsAction::class)->execute(new ListRefundsInputData(transaction: 1641, perPage: 50, page: 1));

    expect($fetched)->toBeInstanceOf(FetchRefundResponseData::class)
        ->and($fetched->refund->transaction)->toBe(1641)
        ->and($fetched->refund->refundedAt)->toBeInstanceOf(CarbonImmutable::class)
        ->and($listed)->toBeInstanceOf(ListRefundsResponseData::class)
        ->and($listed->refunds)->toHaveCount(1)
        ->and($listed->meta?->pagination?->perPage)->toBe(50);

    $mockClient->assertSent(fn (Request $request) => $request instanceof ListRefundsRequest
        && $request->query()->all()['perPage'] === 50
        && $request->query()->all()['page'] === 1
        && $request->query()->all()['transaction'] === 1641);
});

it('retries a refund and exposes manager and facade access', function () {
    $mockClient = new MockClient([
        RetryRefundRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Refund retried and has been queued for processing',
            'data' => [
                'id' => 1234567,
                'integration' => 123456,
                'transaction' => 3298598423,
                'currency' => 'NGN',
                'amount' => 20000,
                'status' => 'processing',
                'deducted_amount' => 20000,
                'fully_deducted' => true,
            ],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $input = new RetryRefundInputData(
        id: 1234567,
        refundAccountDetails: new RefundAccountDetailsInputData(
            currency: 'NGN',
            accountNumber: '1234567890',
            bankId: '9',
        ),
    );

    $managerResult = app(PaystackManager::class)->retryRefund($input);
    $facadeResult = Paystack::retryRefund($input);

    expect($managerResult)->toBeInstanceOf(RetryRefundResponseData::class)
        ->and($managerResult->refund->status)->toBe(RefundStatus::Processing)
        ->and($facadeResult)->toBeInstanceOf(RetryRefundResponseData::class)
        ->and($facadeResult->refund->transaction)->toBe(3298598423);
});

it('throws on refund api errors', function (string $action) {
    $mockClient = new MockClient([
        CreateRefundRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Refund could not be created',
        ], 422),
        FetchRefundRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Refund not found',
        ], 404),
        ListRefundsRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Refunds not available',
        ], 422),
        RetryRefundRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Refund could not be retried',
        ], 422),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    match ($action) {
        'create' => app(CreateRefundAction::class)->execute(new CreateRefundInputData(transaction: '1641')),
        'fetch' => app(FetchRefundAction::class)->execute(new FetchRefundInputData(1)),
        'list' => app(ListRefundsAction::class)->execute(new ListRefundsInputData(perPage: 50)),
        'retry' => app(RetryRefundAction::class)->execute(new RetryRefundInputData(
            id: 1234567,
            refundAccountDetails: new RefundAccountDetailsInputData(currency: 'NGN', accountNumber: '1234567890', bankId: '9'),
        )),
        default => throw new InvalidArgumentException('Unknown refund action test case.'),
    };
})->with(['create', 'fetch', 'list', 'retry'])->throws(RequestException::class);

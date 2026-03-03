<?php

use Carbon\CarbonImmutable;
use Maxiviper117\Paystack\Actions\Transaction\FetchTransactionAction;
use Maxiviper117\Paystack\Actions\Transaction\InitializeTransactionAction;
use Maxiviper117\Paystack\Actions\Transaction\ListTransactionsAction;
use Maxiviper117\Paystack\Actions\Transaction\VerifyTransactionAction;
use Maxiviper117\Paystack\Data\Input\Transaction\FetchTransactionInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\ListTransactionsInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;
use Maxiviper117\Paystack\Data\Output\Transaction\FetchTransactionResponseData;
use Maxiviper117\Paystack\Data\Output\Transaction\InitializeTransactionResponseData;
use Maxiviper117\Paystack\Data\Output\Transaction\ListTransactionsResponseData;
use Maxiviper117\Paystack\Data\Output\Transaction\VerifyTransactionResponseData;
use Maxiviper117\Paystack\Facades\Paystack;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Transaction\FetchTransactionRequest;
use Maxiviper117\Paystack\Integrations\Requests\Transaction\InitializeTransactionRequest;
use Maxiviper117\Paystack\Integrations\Requests\Transaction\ListTransactionsRequest;
use Maxiviper117\Paystack\Integrations\Requests\Transaction\VerifyTransactionRequest;
use Maxiviper117\Paystack\PaystackManager;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

it('initializes a transaction and normalizes amount', function () {
    $mockClient = new MockClient([
        InitializeTransactionRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Authorization URL created',
            'data' => [
                'authorization_url' => 'https://checkout.paystack.com/test',
                'access_code' => 'access_123',
                'reference' => 'ref_123',
            ],
        ], 200),
    ]);

    $connector = app(PaystackConnector::class);
    $connector->withMockClient($mockClient);

    $result = app(InitializeTransactionAction::class)->execute(new InitializeTransactionInputData(
        email: 'jane@example.com',
        amount: 15.5,
        callbackUrl: 'https://example.com/callback',
        metadata: [
            'order_id' => 'ORD-123',
        ],
    ));

    expect($result)->toBeInstanceOf(InitializeTransactionResponseData::class)
        ->and($result->reference)->toBe('ref_123');

    $mockClient->assertSent(fn (Request $request) => $request instanceof InitializeTransactionRequest
        && $request->body()->all()['amount'] === '1550'
        && $request->body()->all()['email'] === 'jane@example.com'
        && $request->body()->all()['metadata'] === '{"order_id":"ORD-123"}');
});

it('verifies a transaction and returns a dto', function () {
    $mockClient = new MockClient([
        VerifyTransactionRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Verification successful',
            'data' => [
                'id' => 10,
                'status' => 'success',
                'reference' => 'ref_123',
                'amount' => 1550,
                'currency' => 'NGN',
                'channel' => 'card',
                'paid_at' => '2026-03-01T10:00:00+00:00',
                'customer' => [
                    'email' => 'jane@example.com',
                    'customer_code' => 'CUS_123',
                ],
            ],
        ], 200),
    ]);

    $connector = app(PaystackConnector::class);
    $connector->withMockClient($mockClient);

    $result = app(VerifyTransactionAction::class)->execute(new VerifyTransactionInputData('ref_123'));

    expect($result)->toBeInstanceOf(VerifyTransactionResponseData::class)
        ->and($result->transaction->status)->toBe('success')
        ->and($result->transaction->paidAt)->toBeInstanceOf(CarbonImmutable::class)
        ->and($result->transaction->paidAt?->toAtomString())->toBe('2026-03-01T10:00:00+00:00')
        ->and($result->transaction->customer?->customerCode)->toBe('CUS_123');
});

it('verifies a transaction with sparse payload fields safely', function () {
    $mockClient = new MockClient([
        VerifyTransactionRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Verification successful',
            'data' => [
                'id' => 10,
                'status' => 'success',
                'reference' => 'ref_123',
                'amount' => 1550,
                'currency' => 'NGN',
                'metadata' => '',
                'customer' => [
                    'email' => 'jane@example.com',
                ],
            ],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $result = app(VerifyTransactionAction::class)->execute(new VerifyTransactionInputData('ref_123'));

    expect($result->transaction->customer?->email)->toBe('jane@example.com')
        ->and($result->transaction->customer?->customerCode)->toBeNull();
});

it('supports invoking a transaction action directly', function () {
    $mockClient = new MockClient([
        VerifyTransactionRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Verification successful',
            'data' => [
                'id' => 10,
                'status' => 'success',
                'reference' => 'ref_123',
                'amount' => 1550,
                'currency' => 'NGN',
            ],
        ], 200),
    ]);

    $connector = app(PaystackConnector::class);
    $connector->withMockClient($mockClient);

    $action = app(VerifyTransactionAction::class);
    $result = $action(new VerifyTransactionInputData('ref_123'));

    expect($result)->toBeInstanceOf(VerifyTransactionResponseData::class)
        ->and($result->transaction->reference)->toBe('ref_123');
});

it('fetches a transaction by identifier', function () {
    $transactionId = 907589086712345678;

    $mockClient = new MockClient([
        FetchTransactionRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Transaction retrieved',
            'data' => [
                'id' => $transactionId,
                'status' => 'success',
                'reference' => 'ref_fetch',
                'amount' => 5000,
                'currency' => 'NGN',
            ],
        ], 200),
    ]);

    $connector = app(PaystackConnector::class);
    $connector->withMockClient($mockClient);

    $result = app(FetchTransactionAction::class)->execute(new FetchTransactionInputData($transactionId));

    expect($result)->toBeInstanceOf(FetchTransactionResponseData::class)
        ->and($result->transaction->reference)->toBe('ref_fetch')
        ->and($result->transaction->id)->toBe($transactionId);
});

it('lists transactions and maps pagination', function () {
    $mockClient = new MockClient([
        ListTransactionsRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Transactions retrieved',
            'data' => [
                [
                    'id' => 1,
                    'status' => 'success',
                    'reference' => 'ref_a',
                    'amount' => 1000,
                    'currency' => 'NGN',
                ],
            ],
            'meta' => [
                'next' => 'https://api.paystack.co/transaction?page=2',
                'previous' => null,
                'perPage' => 50,
            ],
        ], 200),
    ]);

    $connector = app(PaystackConnector::class);
    $connector->withMockClient($mockClient);

    $result = app(ListTransactionsAction::class)->execute(new ListTransactionsInputData(perPage: 50));

    expect($result)->toBeInstanceOf(ListTransactionsResponseData::class)
        ->and($result->transactions)->toHaveCount(1)
        ->and($result->meta?->pagination?->perPage)->toBe(50)
        ->and($result->meta?->pagination?->next)->toBe('https://api.paystack.co/transaction?page=2')
        ->and($result->meta?->pagination?->previous)->toBeNull();

    $mockClient->assertSent(fn (Request $request) => $request instanceof ListTransactionsRequest
        && $request->query()->all()['perPage'] === 50);
});

it('lists transactions with empty data and without meta', function () {
    $mockClient = new MockClient([
        ListTransactionsRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Transactions retrieved',
            'data' => [],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $result = app(ListTransactionsAction::class)->execute(new ListTransactionsInputData);

    expect($result->transactions)->toBe([])
        ->and($result->meta)->toBeNull();
});

it('exposes transaction listing through the manager and facade', function () {
    $mockClient = new MockClient([
        ListTransactionsRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Transactions retrieved',
            'data' => [
                [
                    'id' => 1,
                    'status' => 'success',
                    'reference' => 'ref_a',
                    'amount' => 1000,
                    'currency' => 'NGN',
                ],
            ],
            'meta' => [
                'next' => 'cursor-2',
                'previous' => null,
                'perPage' => 50,
            ],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $input = new ListTransactionsInputData(perPage: 50);

    $managerResult = app(PaystackManager::class)->listTransactions($input);
    $facadeResult = Paystack::listTransactions($input);

    expect($managerResult->transactions)->toHaveCount(1)
        ->and($facadeResult->transactions)->toHaveCount(1);
});

it('throws on transaction api errors', function (string $action) {
    $mockClient = new MockClient([
        InitializeTransactionRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Invalid transaction payload',
        ], 422),
        VerifyTransactionRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Transaction not found',
        ], 404),
        FetchTransactionRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Transaction not found',
        ], 404),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    match ($action) {
        'initialize' => app(InitializeTransactionAction::class)->execute(new InitializeTransactionInputData(
            email: 'jane@example.com',
            amount: 15.5,
        )),
        'verify' => app(VerifyTransactionAction::class)->execute(new VerifyTransactionInputData('ref_123')),
        'fetch' => app(FetchTransactionAction::class)->execute(new FetchTransactionInputData('ref_123')),
        default => throw new InvalidArgumentException('Unknown transaction action test case.'),
    };
})->with(['initialize', 'verify', 'fetch'])->throws(RequestException::class);

it('rejects malformed transaction timestamps', function () {
    app(PaystackConnector::class)->withMockClient(new MockClient([
        VerifyTransactionRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Verification successful',
            'data' => [
                'id' => 10,
                'status' => 'success',
                'reference' => 'ref_bad_date',
                'amount' => 1550,
                'currency' => 'NGN',
                'paid_at' => 'not-a-date',
            ],
        ], 200),
    ]));

    app(VerifyTransactionAction::class)->execute(new VerifyTransactionInputData('ref_bad_date'));
})->throws(InvalidArgumentException::class);

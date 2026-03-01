<?php

use Maxiviper117\Paystack\Actions\Transaction\InitializeTransactionAction;
use Maxiviper117\Paystack\Actions\Transaction\FetchTransactionAction;
use Maxiviper117\Paystack\Actions\Transaction\ListTransactionsAction;
use Maxiviper117\Paystack\Actions\Transaction\VerifyTransactionAction;
use Maxiviper117\Paystack\Data\Transaction\InitializedTransactionData;
use Maxiviper117\Paystack\Data\Transaction\TransactionData;
use Maxiviper117\Paystack\Data\Transaction\TransactionListData;
use Maxiviper117\Paystack\Data\Transaction\VerificationData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Transaction\FetchTransactionRequest;
use Maxiviper117\Paystack\Integrations\Requests\Transaction\InitializeTransactionRequest;
use Maxiviper117\Paystack\Integrations\Requests\Transaction\ListTransactionsRequest;
use Maxiviper117\Paystack\Integrations\Requests\Transaction\VerifyTransactionRequest;
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

    $result = app(InitializeTransactionAction::class)->execute('jane@example.com', 15.5, [
        'callback_url' => 'https://example.com/callback',
        'metadata' => [
            'order_id' => 'ORD-123',
        ],
    ]);

    expect($result)->toBeInstanceOf(InitializedTransactionData::class)
        ->and($result->reference)->toBe('ref_123');

    $mockClient->assertSent(function (Request $request) {
        return $request instanceof InitializeTransactionRequest
            && $request->body()->all()['amount'] === '1550'
            && $request->body()->all()['email'] === 'jane@example.com'
            && $request->body()->all()['metadata'] === '{"order_id":"ORD-123"}';
    });
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

    $result = app(VerifyTransactionAction::class)->execute('ref_123');

    expect($result)->toBeInstanceOf(VerificationData::class)
        ->and($result->status)->toBe('success')
        ->and($result->customer?->customerCode)->toBe('CUS_123');
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

    $result = app(FetchTransactionAction::class)->execute($transactionId);

    expect($result)->toBeInstanceOf(TransactionData::class)
        ->and($result->reference)->toBe('ref_fetch')
        ->and($result->id)->toBe($transactionId);
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

    $result = app(ListTransactionsAction::class)->execute(['perPage' => 50]);

    expect($result)->toBeInstanceOf(TransactionListData::class)
        ->and($result->items)->toHaveCount(1)
        ->and($result->meta?->pagination?->perPage)->toBe(50)
        ->and($result->meta?->pagination?->next)->toBe('https://api.paystack.co/transaction?page=2')
        ->and($result->meta?->pagination?->previous)->toBeNull();

    $mockClient->assertSent(function (Request $request) {
        return $request instanceof ListTransactionsRequest
            && $request->query()->all()['perPage'] === 50;
    });
});

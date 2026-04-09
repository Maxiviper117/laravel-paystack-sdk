<?php

use Carbon\CarbonImmutable;
use Maxiviper117\Paystack\Actions\Dispute\AddDisputeEvidenceAction;
use Maxiviper117\Paystack\Actions\Dispute\ExportDisputesAction;
use Maxiviper117\Paystack\Actions\Dispute\FetchDisputeAction;
use Maxiviper117\Paystack\Actions\Dispute\GetDisputeUploadUrlAction;
use Maxiviper117\Paystack\Actions\Dispute\ListDisputesAction;
use Maxiviper117\Paystack\Actions\Dispute\ListTransactionDisputesAction;
use Maxiviper117\Paystack\Actions\Dispute\ResolveDisputeAction;
use Maxiviper117\Paystack\Actions\Dispute\UpdateDisputeAction;
use Maxiviper117\Paystack\Data\Dispute\DisputeStatus;
use Maxiviper117\Paystack\Data\Input\Dispute\AddDisputeEvidenceInputData;
use Maxiviper117\Paystack\Data\Input\Dispute\FetchDisputeInputData;
use Maxiviper117\Paystack\Data\Input\Dispute\GetDisputeUploadUrlInputData;
use Maxiviper117\Paystack\Data\Input\Dispute\ListDisputesInputData;
use Maxiviper117\Paystack\Data\Input\Dispute\ListTransactionDisputesInputData;
use Maxiviper117\Paystack\Data\Input\Dispute\ResolveDisputeInputData;
use Maxiviper117\Paystack\Data\Input\Dispute\UpdateDisputeInputData;
use Maxiviper117\Paystack\Data\Output\Dispute\AddDisputeEvidenceResponseData;
use Maxiviper117\Paystack\Data\Output\Dispute\ExportDisputesResponseData;
use Maxiviper117\Paystack\Data\Output\Dispute\FetchDisputeResponseData;
use Maxiviper117\Paystack\Data\Output\Dispute\GetDisputeUploadUrlResponseData;
use Maxiviper117\Paystack\Data\Output\Dispute\ListDisputesResponseData;
use Maxiviper117\Paystack\Data\Output\Dispute\ListTransactionDisputesResponseData;
use Maxiviper117\Paystack\Data\Output\Dispute\ResolveDisputeResponseData;
use Maxiviper117\Paystack\Data\Output\Dispute\UpdateDisputeResponseData;
use Maxiviper117\Paystack\Facades\Paystack;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Dispute\AddDisputeEvidenceRequest;
use Maxiviper117\Paystack\Integrations\Requests\Dispute\ExportDisputesRequest;
use Maxiviper117\Paystack\Integrations\Requests\Dispute\FetchDisputeRequest;
use Maxiviper117\Paystack\Integrations\Requests\Dispute\GetDisputeUploadUrlRequest;
use Maxiviper117\Paystack\Integrations\Requests\Dispute\ListDisputesRequest;
use Maxiviper117\Paystack\Integrations\Requests\Dispute\ListTransactionDisputesRequest;
use Maxiviper117\Paystack\Integrations\Requests\Dispute\ResolveDisputeRequest;
use Maxiviper117\Paystack\Integrations\Requests\Dispute\UpdateDisputeRequest;
use Maxiviper117\Paystack\PaystackManager;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

it('lists disputes and maps nested payload data', function () {
    $mockClient = new MockClient([
        ListDisputesRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Disputes retrieved',
            'data' => [
                [
                    'id' => 2867,
                    'refund_amount' => 1002,
                    'currency' => 'NGN',
                    'status' => 'pending',
                    'resolution' => null,
                    'transaction' => [
                        'id' => 5991760,
                        'reference' => 'txn_123',
                        'amount' => 12000,
                        'customer' => [
                            'email' => 'customer@example.com',
                            'customer_code' => 'CUS_123',
                        ],
                    ],
                ],
            ],
            'meta' => [
                'perPage' => 25,
                'page' => 1,
            ],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $result = app(ListDisputesAction::class)->execute(new ListDisputesInputData(
        perPage: 25,
        page: 1,
        transaction: '5991760',
        status: DisputeStatus::Pending,
    ));

    expect($result)->toBeInstanceOf(ListDisputesResponseData::class)
        ->and($result->disputes)->toHaveCount(1)
        ->and($result->disputes[0]->transaction?->customer?->customerCode)->toBe('CUS_123')
        ->and($result->meta?->pagination?->perPage)->toBe(25);

    $mockClient->assertSent(fn (Request $request) => $request instanceof ListDisputesRequest
        && $request->query()->all()['perPage'] === 25
        && $request->query()->all()['page'] === 1
        && $request->query()->all()['transaction'] === '5991760'
        && $request->query()->all()['status'] === 'pending');
});

it('fetches a dispute and transaction disputes with typed nested data', function () {
    $mockClient = new MockClient([
        FetchDisputeRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Dispute retrieved',
            'data' => [
                'id' => 2867,
                'refund_amount' => 1002,
                'currency' => 'NGN',
                'status' => 'pending',
                'createdAt' => '2026-03-01T10:00:00+00:00',
                'updatedAt' => '2026-03-02T10:00:00+00:00',
                'transaction' => [
                    'id' => 5991760,
                    'reference' => 'txn_123',
                    'amount' => 12000,
                    'customer' => [
                        'email' => 'customer@example.com',
                        'customer_code' => 'CUS_123',
                    ],
                ],
            ],
        ], 200),
        ListTransactionDisputesRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Transaction disputes retrieved',
            'data' => [
                'id' => 2867,
                'refund_amount' => 1002,
                'currency' => 'NGN',
                'status' => 'pending',
                'history' => [
                    [
                        'id' => 1,
                        'dispute' => 2867,
                        'status' => 'pending',
                        'by' => 'merchant',
                        'createdAt' => '2026-03-01T10:00:00+00:00',
                    ],
                ],
                'messages' => [
                    [
                        'id' => 9,
                        'dispute' => 2867,
                        'sender' => 'merchant',
                        'body' => 'We delivered the service.',
                        'is_deleted' => 0,
                        'created_at' => '2026-03-01T10:00:00+00:00',
                    ],
                ],
                'transaction' => [
                    'id' => 5991760,
                    'reference' => 'txn_123',
                    'amount' => 12000,
                    'customer' => [
                        'email' => 'customer@example.com',
                    ],
                ],
            ],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $fetched = app(FetchDisputeAction::class)->execute(new FetchDisputeInputData('DIS/2026?beta'));
    $transactionDispute = app(ListTransactionDisputesAction::class)->execute(new ListTransactionDisputesInputData(5991760));

    expect($fetched)->toBeInstanceOf(FetchDisputeResponseData::class)
        ->and($fetched->dispute->createdAt)->toBeInstanceOf(CarbonImmutable::class)
        ->and($fetched->dispute->transaction?->customer?->email)->toBe('customer@example.com')
        ->and($transactionDispute)->toBeInstanceOf(ListTransactionDisputesResponseData::class)
        ->and($transactionDispute->dispute->history)->toHaveCount(1)
        ->and($transactionDispute->dispute->history[0]->status)->toBe(DisputeStatus::Pending)
        ->and($transactionDispute->dispute->messages[0]->isDeleted)->toBeFalse();

    $mockClient->assertSent(fn (Request $request) => $request instanceof FetchDisputeRequest
        && $request->resolveEndpoint() === '/dispute/DIS%2F2026%3Fbeta');

    $mockClient->assertSent(fn (Request $request) => $request instanceof ListTransactionDisputesRequest
        && $request->resolveEndpoint() === '/dispute/transaction/5991760');
});

it('updates exports and resolves disputes with the expected payloads', function () {
    $mockClient = new MockClient([
        UpdateDisputeRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Dispute updated',
            'data' => [
                [
                    'id' => 2867,
                    'refund_amount' => 1002,
                    'status' => 'pending',
                    'transaction' => [
                        'id' => 5991760,
                        'reference' => 'txn_123',
                    ],
                ],
            ],
        ], 200),
        AddDisputeEvidenceRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Evidence added',
            'data' => [
                'customer_email' => 'customer@example.com',
                'customer_name' => 'Jane Doe',
                'customer_phone' => '08023456789',
                'service_details' => 'Delivered as agreed',
                'delivery_address' => '3 Main Street',
                'delivery_date' => '2026-01-05T00:00:00+00:00',
                'dispute' => 2867,
                'id' => 21,
            ],
        ], 200),
        GetDisputeUploadUrlRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Upload URL generated',
            'data' => [
                'signedUrl' => 'https://files.example.com/upload',
                'fileName' => 'receipt.pdf',
            ],
        ], 200),
        ResolveDisputeRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Dispute resolved',
            'data' => [
                'id' => 2867,
                'refund_amount' => 1002,
                'resolution' => 'merchant-accepted',
                'status' => 'resolved',
                'resolvedAt' => '2026-03-03T10:00:00+00:00',
            ],
        ], 200),
        ExportDisputesRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Disputes export generated',
            'data' => [
                'path' => 'https://paystack.s3.amazonaws.com/disputes.csv',
                'expiresAt' => '2026-03-03 11:00:00',
            ],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $updated = app(UpdateDisputeAction::class)->execute(new UpdateDisputeInputData(
        id: 2867,
        refundAmount: 1002,
        uploadedFilename: 'receipt.pdf',
    ));

    $evidence = app(AddDisputeEvidenceAction::class)->execute(new AddDisputeEvidenceInputData(
        id: 2867,
        customerEmail: 'customer@example.com',
        customerName: 'Jane Doe',
        customerPhone: '08023456789',
        serviceDetails: 'Delivered as agreed',
        deliveryAddress: '3 Main Street',
        deliveryDate: '2026-01-05',
    ));

    $uploadUrl = app(GetDisputeUploadUrlAction::class)->execute(new GetDisputeUploadUrlInputData(2867, 'receipt.pdf'));

    $resolved = app(ResolveDisputeAction::class)->execute(new ResolveDisputeInputData(
        id: 2867,
        resolution: 'merchant-accepted',
        message: 'Merchant accepted',
        refundAmount: 1002,
        uploadedFilename: 'receipt.pdf',
        evidence: 21,
    ));

    $export = app(ExportDisputesAction::class)->execute(new ListDisputesInputData(
        from: '2026-01-01',
        to: '2026-12-31',
        perPage: 25,
        page: 1,
        transaction: '5991760',
        status: DisputeStatus::Pending,
    ));

    expect($updated)->toBeInstanceOf(UpdateDisputeResponseData::class)
        ->and($updated->disputes)->toHaveCount(1)
        ->and($updated->disputes[0]->refundAmount)->toBe(1002)
        ->and($evidence)->toBeInstanceOf(AddDisputeEvidenceResponseData::class)
        ->and($evidence->evidence->deliveryAddress)->toBe('3 Main Street')
        ->and($evidence->evidence->deliveryDate)->toBeInstanceOf(CarbonImmutable::class)
        ->and($uploadUrl)->toBeInstanceOf(GetDisputeUploadUrlResponseData::class)
        ->and($uploadUrl->uploadUrl->signedUrl)->toBe('https://files.example.com/upload')
        ->and($resolved)->toBeInstanceOf(ResolveDisputeResponseData::class)
        ->and($resolved->dispute->status)->toBe(DisputeStatus::Resolved)
        ->and($resolved->dispute->resolvedAt)->toBeInstanceOf(CarbonImmutable::class)
        ->and($export)->toBeInstanceOf(ExportDisputesResponseData::class)
        ->and($export->export->path)->toContain('disputes.csv');

    $mockClient->assertSent(fn (Request $request) => $request instanceof UpdateDisputeRequest
        && $request->body()->all() === [
            'refund_amount' => 1002,
            'uploaded_filename' => 'receipt.pdf',
        ]);

    $mockClient->assertSent(fn (Request $request) => $request instanceof AddDisputeEvidenceRequest
        && $request->body()->all()['customer_email'] === 'customer@example.com'
        && $request->body()->all()['delivery_date'] === '2026-01-05');

    $mockClient->assertSent(fn (Request $request) => $request instanceof GetDisputeUploadUrlRequest
        && $request->query()->all()['upload_filename'] === 'receipt.pdf');

    $mockClient->assertSent(fn (Request $request) => $request instanceof ResolveDisputeRequest
        && $request->body()->all()['resolution'] === 'merchant-accepted'
        && $request->body()->all()['evidence'] === 21);

    $mockClient->assertSent(fn (Request $request) => $request instanceof ExportDisputesRequest
        && $request->query()->all()['from'] === '2026-01-01'
        && $request->query()->all()['status'] === 'pending');
});

it('exposes disputes through the manager and facade', function () {
    $mockClient = new MockClient([
        ListDisputesRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Disputes retrieved',
            'data' => [
                [
                    'id' => 2867,
                    'refund_amount' => 1002,
                    'status' => 'pending',
                ],
            ],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $input = new ListDisputesInputData(perPage: 25);
    $managerResult = app(PaystackManager::class)->listDisputes($input);
    $facadeResult = Paystack::listDisputes($input);

    expect($managerResult)->toBeInstanceOf(ListDisputesResponseData::class)
        ->and($managerResult->disputes)->toHaveCount(1)
        ->and($facadeResult)->toBeInstanceOf(ListDisputesResponseData::class)
        ->and($facadeResult->disputes)->toHaveCount(1);
});

it('throws on dispute api errors', function (string $action) {
    $mockClient = new MockClient([
        ListDisputesRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Disputes not available',
        ], 422),
        FetchDisputeRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Dispute not found',
        ], 404),
        ListTransactionDisputesRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Dispute not found',
        ], 404),
        UpdateDisputeRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Dispute could not be updated',
        ], 422),
        AddDisputeEvidenceRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Evidence could not be added',
        ], 422),
        GetDisputeUploadUrlRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Upload URL could not be generated',
        ], 422),
        ResolveDisputeRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Dispute could not be resolved',
        ], 422),
        ExportDisputesRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Disputes export could not be generated',
        ], 422),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    match ($action) {
        'list' => app(ListDisputesAction::class)->execute(new ListDisputesInputData(perPage: 25)),
        'fetch' => app(FetchDisputeAction::class)->execute(new FetchDisputeInputData(2867)),
        'transaction' => app(ListTransactionDisputesAction::class)->execute(new ListTransactionDisputesInputData(5991760)),
        'update' => app(UpdateDisputeAction::class)->execute(new UpdateDisputeInputData(id: 2867, refundAmount: 1002)),
        'evidence' => app(AddDisputeEvidenceAction::class)->execute(new AddDisputeEvidenceInputData(
            id: 2867,
            customerEmail: 'customer@example.com',
            customerName: 'Jane Doe',
            customerPhone: '08023456789',
            serviceDetails: 'Delivered as agreed',
        )),
        'upload-url' => app(GetDisputeUploadUrlAction::class)->execute(new GetDisputeUploadUrlInputData(2867, 'receipt.pdf')),
        'resolve' => app(ResolveDisputeAction::class)->execute(new ResolveDisputeInputData(
            id: 2867,
            resolution: 'merchant-accepted',
        )),
        'export' => app(ExportDisputesAction::class)->execute(new ListDisputesInputData(perPage: 25)),
        default => throw new InvalidArgumentException('Unknown dispute action test case.'),
    };
})->with(['list', 'fetch', 'transaction', 'update', 'evidence', 'upload-url', 'resolve', 'export'])->throws(RequestException::class);

it('rejects malformed dispute timestamps', function () {
    app(PaystackConnector::class)->withMockClient(new MockClient([
        FetchDisputeRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Dispute retrieved',
            'data' => [
                'id' => 2867,
                'refund_amount' => 1002,
                'status' => 'pending',
                'createdAt' => 'not-a-date',
            ],
        ], 200),
    ]));

    app(FetchDisputeAction::class)->execute(new FetchDisputeInputData(2867));
})->throws(InvalidArgumentException::class);

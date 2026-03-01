<?php

use Maxiviper117\Paystack\Actions\Customer\CreateCustomerAction;
use Maxiviper117\Paystack\Actions\Customer\ListCustomersAction;
use Maxiviper117\Paystack\Actions\Customer\UpdateCustomerAction;
use Maxiviper117\Paystack\Data\Customer\CustomerData;
use Maxiviper117\Paystack\Data\Customer\CustomerListData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Customer\CreateCustomerRequest;
use Maxiviper117\Paystack\Integrations\Requests\Customer\ListCustomersRequest;
use Maxiviper117\Paystack\Integrations\Requests\Customer\UpdateCustomerRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

it('creates a customer and returns a dto', function () {
    $mockClient = new MockClient([
        CreateCustomerRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Customer created',
            'data' => [
                'email' => 'jane@example.com',
                'customer_code' => 'CUS_123',
                'first_name' => 'Jane',
                'last_name' => 'Doe',
            ],
        ], 200),
    ]);

    $connector = app(PaystackConnector::class);
    $connector->withMockClient($mockClient);

    $result = app(CreateCustomerAction::class)->execute('jane@example.com', [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'metadata' => [
            'crm_id' => 'CRM-123',
        ],
    ]);

    expect($result)->toBeInstanceOf(CustomerData::class)
        ->and($result->customerCode)->toBe('CUS_123');

    $mockClient->assertSent(fn(Request $request) => $request instanceof CreateCustomerRequest
        && $request->body()->all()['email'] === 'jane@example.com'
        && $request->body()->all()['metadata'] === ['crm_id' => 'CRM-123']);
});

it('lists customers and returns pagination data', function () {
    $mockClient = new MockClient([
        ListCustomersRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Customers retrieved',
            'data' => [
                [
                    'email' => 'jane@example.com',
                    'customer_code' => 'CUS_123',
                ],
            ],
            'meta' => [
                'next' => 'https://api.paystack.co/customer?page=2',
                'previous' => null,
                'perPage' => 50,
            ],
        ], 200),
    ]);

    $connector = app(PaystackConnector::class);
    $connector->withMockClient($mockClient);

    $result = app(ListCustomersAction::class)->execute(['perPage' => 50]);

    expect($result)->toBeInstanceOf(CustomerListData::class)
        ->and($result->items)->toHaveCount(1)
        ->and($result->meta?->pagination?->perPage)->toBe(50)
        ->and($result->meta?->pagination?->next)->toBe('https://api.paystack.co/customer?page=2')
        ->and($result->meta?->pagination?->previous)->toBeNull();

    $mockClient->assertSent(fn(Request $request) => $request instanceof ListCustomersRequest
        && $request->query()->all()['perPage'] === 50);
});

it('updates a customer and sends the expected payload', function () {
    $mockClient = new MockClient([
        UpdateCustomerRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Customer updated',
            'data' => [
                'email' => 'jane@example.com',
                'customer_code' => 'CUS_123',
                'first_name' => 'Janet',
            ],
        ], 200),
    ]);

    $connector = app(PaystackConnector::class);
    $connector->withMockClient($mockClient);

    $result = app(UpdateCustomerAction::class)->execute('CUS_123', [
        'first_name' => 'Janet',
        'metadata' => ['crm_id' => 'CRM-456'],
    ]);

    expect($result)->toBeInstanceOf(CustomerData::class)
        ->and($result->firstName)->toBe('Janet');

    $mockClient->assertSent(fn(Request $request) => $request instanceof UpdateCustomerRequest
        && $request->body()->all()['first_name'] === 'Janet'
        && $request->body()->all()['metadata'] === ['crm_id' => 'CRM-456']);
});

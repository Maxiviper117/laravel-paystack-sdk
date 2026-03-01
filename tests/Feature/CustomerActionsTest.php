<?php

use Maxiviper117\Paystack\Actions\Customer\CreateCustomerAction;
use Maxiviper117\Paystack\Actions\Customer\ListCustomersAction;
use Maxiviper117\Paystack\Actions\Customer\UpdateCustomerAction;
use Maxiviper117\Paystack\Data\Input\Customer\CreateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\ListCustomersInputData;
use Maxiviper117\Paystack\Data\Input\Customer\UpdateCustomerInputData;
use Maxiviper117\Paystack\Data\Output\Customer\CreateCustomerResponseData;
use Maxiviper117\Paystack\Data\Output\Customer\ListCustomersResponseData;
use Maxiviper117\Paystack\Data\Output\Customer\UpdateCustomerResponseData;
use Maxiviper117\Paystack\Facades\Paystack;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Customer\CreateCustomerRequest;
use Maxiviper117\Paystack\Integrations\Requests\Customer\ListCustomersRequest;
use Maxiviper117\Paystack\Integrations\Requests\Customer\UpdateCustomerRequest;
use Maxiviper117\Paystack\PaystackManager;
use Saloon\Exceptions\Request\RequestException;
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

    $result = app(CreateCustomerAction::class)->execute(new CreateCustomerInputData(
        email: 'jane@example.com',
        firstName: 'Jane',
        lastName: 'Doe',
        metadata: [
            'crm_id' => 'CRM-123',
        ],
    ));

    expect($result)->toBeInstanceOf(CreateCustomerResponseData::class)
        ->and($result->customer->customerCode)->toBe('CUS_123');

    $mockClient->assertSent(fn (Request $request) => $request instanceof CreateCustomerRequest
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

    $result = app(ListCustomersAction::class)->execute(new ListCustomersInputData(perPage: 50));

    expect($result)->toBeInstanceOf(ListCustomersResponseData::class)
        ->and($result->customers)->toHaveCount(1)
        ->and($result->meta?->pagination?->perPage)->toBe(50)
        ->and($result->meta?->pagination?->next)->toBe('https://api.paystack.co/customer?page=2')
        ->and($result->meta?->pagination?->previous)->toBeNull();

    $mockClient->assertSent(fn (Request $request) => $request instanceof ListCustomersRequest
        && $request->query()->all()['perPage'] === 50);
});

it('supports invoking a customer action directly', function () {
    $mockClient = new MockClient([
        CreateCustomerRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Customer created',
            'data' => [
                'email' => 'jane@example.com',
                'customer_code' => 'CUS_123',
            ],
        ], 200),
    ]);

    $connector = app(PaystackConnector::class);
    $connector->withMockClient($mockClient);

    $action = app(CreateCustomerAction::class);
    $result = $action(new CreateCustomerInputData(email: 'jane@example.com'));

    expect($result)->toBeInstanceOf(CreateCustomerResponseData::class)
        ->and($result->customer->customerCode)->toBe('CUS_123');
});

it('creates a customer from a sparse payload safely', function () {
    $mockClient = new MockClient([
        CreateCustomerRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Customer created',
            'data' => [
                'email' => 'jane@example.com',
                'customer_code' => 'CUS_123',
            ],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $result = app(CreateCustomerAction::class)->execute(new CreateCustomerInputData(email: 'jane@example.com'));

    expect($result->customer->email)->toBe('jane@example.com')
        ->and($result->customer->firstName)->toBeNull()
        ->and($result->customer->metadata)->toBeNull();
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

    $result = app(UpdateCustomerAction::class)->execute(new UpdateCustomerInputData(
        customerCode: 'CUS_123',
        firstName: 'Janet',
        metadata: ['crm_id' => 'CRM-456'],
    ));

    expect($result)->toBeInstanceOf(UpdateCustomerResponseData::class)
        ->and($result->customer->firstName)->toBe('Janet');

    $mockClient->assertSent(fn (Request $request) => $request instanceof UpdateCustomerRequest
        && $request->body()->all()['first_name'] === 'Janet'
        && $request->body()->all()['metadata'] === ['crm_id' => 'CRM-456']);
});

it('lists customers with empty data and without meta', function () {
    $mockClient = new MockClient([
        ListCustomersRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Customers retrieved',
            'data' => [],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $result = app(ListCustomersAction::class)->execute(new ListCustomersInputData);

    expect($result->customers)->toBe([])
        ->and($result->meta)->toBeNull();
});

it('exposes customer listing through the manager and facade', function () {
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
                'next' => 'cursor-2',
                'previous' => null,
                'perPage' => 50,
            ],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $input = new ListCustomersInputData(perPage: 50);

    $managerResult = app(PaystackManager::class)->listCustomers($input);
    $facadeResult = Paystack::listCustomers($input);

    expect($managerResult->customers)->toHaveCount(1)
        ->and($facadeResult->customers)->toHaveCount(1);
});

it('throws on customer api errors', function (string $action) {
    $mockClient = new MockClient([
        CreateCustomerRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Customer could not be created',
        ], 422),
        UpdateCustomerRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Customer could not be updated',
        ], 422),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    match ($action) {
        'create' => app(CreateCustomerAction::class)->execute(new CreateCustomerInputData(
            email: 'jane@example.com',
        )),
        'update' => app(UpdateCustomerAction::class)->execute(new UpdateCustomerInputData(
            customerCode: 'CUS_123',
            firstName: 'Janet',
        )),
        default => throw new InvalidArgumentException('Unknown customer action test case.'),
    };
})->with(['create', 'update'])->throws(RequestException::class);

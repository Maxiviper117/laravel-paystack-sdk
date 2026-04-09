<?php

use Maxiviper117\Paystack\Actions\Customer\CreateCustomerAction;
use Maxiviper117\Paystack\Actions\Customer\FetchCustomerAction;
use Maxiviper117\Paystack\Actions\Customer\ListCustomersAction;
use Maxiviper117\Paystack\Actions\Customer\SetCustomerRiskAction;
use Maxiviper117\Paystack\Actions\Customer\UpdateCustomerAction;
use Maxiviper117\Paystack\Actions\Customer\ValidateCustomerAction;
use Maxiviper117\Paystack\Data\Input\Customer\CreateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\FetchCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\ListCustomersInputData;
use Maxiviper117\Paystack\Data\Input\Customer\SetCustomerRiskActionInputData;
use Maxiviper117\Paystack\Data\Input\Customer\UpdateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\ValidateCustomerInputData;
use Maxiviper117\Paystack\Data\Output\Customer\CreateCustomerResponseData;
use Maxiviper117\Paystack\Data\Output\Customer\FetchCustomerResponseData;
use Maxiviper117\Paystack\Data\Output\Customer\ListCustomersResponseData;
use Maxiviper117\Paystack\Data\Output\Customer\SetCustomerRiskActionResponseData;
use Maxiviper117\Paystack\Data\Output\Customer\UpdateCustomerResponseData;
use Maxiviper117\Paystack\Data\Output\Customer\ValidateCustomerResponseData;
use Maxiviper117\Paystack\Facades\Paystack;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Customer\CreateCustomerRequest;
use Maxiviper117\Paystack\Integrations\Requests\Customer\FetchCustomerRequest;
use Maxiviper117\Paystack\Integrations\Requests\Customer\ListCustomersRequest;
use Maxiviper117\Paystack\Integrations\Requests\Customer\SetCustomerRiskActionRequest;
use Maxiviper117\Paystack\Integrations\Requests\Customer\UpdateCustomerRequest;
use Maxiviper117\Paystack\Integrations\Requests\Customer\ValidateCustomerRequest;
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

it('fetches a customer by email or code', function () {
    $mockClient = new MockClient([
        FetchCustomerRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Customer retrieved',
            'data' => [
                'email' => 'jane@example.com',
                'customer_code' => 'CUS_123',
                'first_name' => 'Jane',
                'last_name' => 'Doe',
            ],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $result = app(FetchCustomerAction::class)->execute(new FetchCustomerInputData('CUS_123'));

    expect($result)->toBeInstanceOf(FetchCustomerResponseData::class)
        ->and($result->customer->customerCode)->toBe('CUS_123')
        ->and($result->customer->email)->toBe('jane@example.com');

    $mockClient->assertSent(fn (Request $request) => $request instanceof FetchCustomerRequest
        && $request->resolveEndpoint() === '/customer/CUS_123');
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

it('validates a customer and sends the expected payload', function () {
    $mockClient = new MockClient([
        ValidateCustomerRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Customer Identification in progress',
        ], 202),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $result = app(ValidateCustomerAction::class)->execute(new ValidateCustomerInputData(
        customerCode: 'CUS_123',
        country: 'NG',
        type: 'bank_account',
        firstName: 'Asta',
        lastName: 'Lavista',
        bvn: '200123456677',
        bankCode: '007',
        accountNumber: '0123456789',
    ));

    expect($result)->toBeInstanceOf(ValidateCustomerResponseData::class)
        ->and($result->status)->toBeTrue()
        ->and($result->message)->toBe('Customer Identification in progress');

    $mockClient->assertSent(fn (Request $request) => $request instanceof ValidateCustomerRequest
        && $request->resolveEndpoint() === '/customer/CUS_123/identification'
        && $request->body()->all()['country'] === 'NG'
        && $request->body()->all()['type'] === 'bank_account'
        && $request->body()->all()['account_number'] === '0123456789'
        && $request->body()->all()['bvn'] === '200123456677'
        && $request->body()->all()['bank_code'] === '007');
});

it('sets a customer risk action and sends the expected payload', function () {
    $mockClient = new MockClient([
        SetCustomerRiskActionRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Customer risk action updated',
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $result = app(SetCustomerRiskAction::class)->execute(new SetCustomerRiskActionInputData(
        customer: 'CUS_123',
        riskAction: 'deny',
    ));

    expect($result)->toBeInstanceOf(SetCustomerRiskActionResponseData::class)
        ->and($result->status)->toBeTrue()
        ->and($result->message)->toBe('Customer risk action updated');

    $mockClient->assertSent(fn (Request $request) => $request instanceof SetCustomerRiskActionRequest
        && $request->body()->all()['customer'] === 'CUS_123'
        && $request->body()->all()['risk_action'] === 'deny');
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
        FetchCustomerRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Customer not found',
        ], 404),
        UpdateCustomerRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Customer could not be updated',
        ], 422),
        ValidateCustomerRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Customer identification failed',
        ], 422),
        SetCustomerRiskActionRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Customer risk action could not be updated',
        ], 422),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    match ($action) {
        'create' => app(CreateCustomerAction::class)->execute(new CreateCustomerInputData(
            email: 'jane@example.com',
        )),
        'fetch' => app(FetchCustomerAction::class)->execute(new FetchCustomerInputData('CUS_123')),
        'update' => app(UpdateCustomerAction::class)->execute(new UpdateCustomerInputData(
            customerCode: 'CUS_123',
            firstName: 'Janet',
        )),
        'validate' => app(ValidateCustomerAction::class)->execute(new ValidateCustomerInputData(
            customerCode: 'CUS_123',
            country: 'NG',
            type: 'bank_account',
            bvn: '200123456677',
            bankCode: '007',
            accountNumber: '0123456789',
        )),
        'risk-action' => app(SetCustomerRiskAction::class)->execute(new SetCustomerRiskActionInputData(
            customer: 'CUS_123',
            riskAction: 'deny',
        )),
        default => throw new InvalidArgumentException('Unknown customer action test case.'),
    };
})->with(['create', 'fetch', 'update', 'validate', 'risk-action'])->throws(RequestException::class);

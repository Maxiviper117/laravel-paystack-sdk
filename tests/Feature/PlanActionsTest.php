<?php

use Maxiviper117\Paystack\Actions\Plan\CreatePlanAction;
use Maxiviper117\Paystack\Actions\Plan\FetchPlanAction;
use Maxiviper117\Paystack\Actions\Plan\ListPlansAction;
use Maxiviper117\Paystack\Actions\Plan\UpdatePlanAction;
use Maxiviper117\Paystack\Data\Input\Plan\CreatePlanInputData;
use Maxiviper117\Paystack\Data\Input\Plan\FetchPlanInputData;
use Maxiviper117\Paystack\Data\Input\Plan\ListPlansInputData;
use Maxiviper117\Paystack\Data\Input\Plan\UpdatePlanInputData;
use Maxiviper117\Paystack\Data\Output\Plan\CreatePlanResponseData;
use Maxiviper117\Paystack\Data\Output\Plan\FetchPlanResponseData;
use Maxiviper117\Paystack\Data\Output\Plan\ListPlansResponseData;
use Maxiviper117\Paystack\Data\Output\Plan\UpdatePlanResponseData;
use Maxiviper117\Paystack\Facades\Paystack;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Plan\CreatePlanRequest;
use Maxiviper117\Paystack\Integrations\Requests\Plan\FetchPlanRequest;
use Maxiviper117\Paystack\Integrations\Requests\Plan\ListPlansRequest;
use Maxiviper117\Paystack\Integrations\Requests\Plan\UpdatePlanRequest;
use Maxiviper117\Paystack\PaystackManager;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

it('creates a plan and normalizes amount', function () {
    $mockClient = new MockClient([
        CreatePlanRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Plan created',
            'data' => [
                'id' => 11,
                'name' => 'Starter',
                'plan_code' => 'PLN_start',
                'amount' => 500000,
                'interval' => 'monthly',
                'currency' => 'NGN',
            ],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $result = app(CreatePlanAction::class)->execute(new CreatePlanInputData(
        name: 'Starter',
        amount: 5000,
        interval: 'monthly',
    ));

    expect($result)->toBeInstanceOf(CreatePlanResponseData::class)
        ->and($result->plan->planCode)->toBe('PLN_start');

    $mockClient->assertSent(fn (Request $request) => $request instanceof CreatePlanRequest
        && $request->body()->all()['amount'] === '500000'
        && $request->body()->all()['name'] === 'Starter');
});

it('updates and fetches a plan', function () {
    $mockClient = new MockClient([
        UpdatePlanRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Plan updated',
            'data' => [
                'id' => 11,
                'name' => 'Starter Pro',
                'plan_code' => 'PLN_start',
                'amount' => 750000,
                'interval' => 'monthly',
            ],
        ], 200),
        FetchPlanRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Plan retrieved',
            'data' => [
                'id' => 11,
                'name' => 'Starter Pro',
                'plan_code' => 'PLN_start',
                'amount' => 750000,
                'interval' => 'monthly',
            ],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $updated = app(UpdatePlanAction::class)->execute(new UpdatePlanInputData(
        planCode: 'PLN_start',
        name: 'Starter Pro',
        amount: 7500,
        updateExistingSubscriptions: true,
    ));

    $fetched = app(FetchPlanAction::class)->execute(new FetchPlanInputData('PLN_start'));

    expect($updated)->toBeInstanceOf(UpdatePlanResponseData::class)
        ->and($updated->plan->name)->toBe('Starter Pro')
        ->and($fetched)->toBeInstanceOf(FetchPlanResponseData::class)
        ->and($fetched->plan->amount)->toBe(750000);

    $mockClient->assertSent(fn (Request $request) => $request instanceof UpdatePlanRequest
        && $request->body()->all()['update_existing_subscriptions'] === true);
});

it('lists plans and exposes manager or facade usage', function () {
    $mockClient = new MockClient([
        ListPlansRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Plans retrieved',
            'data' => [
                [
                    'id' => 11,
                    'name' => 'Starter',
                    'plan_code' => 'PLN_start',
                    'amount' => 500000,
                    'interval' => 'monthly',
                ],
            ],
            'meta' => [
                'total' => 1,
                'skipped' => 0,
                'perPage' => 50,
                'page' => 1,
                'pageCount' => 1,
            ],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $input = new ListPlansInputData(perPage: 50);
    $managerResult = app(PaystackManager::class)->listPlans($input);
    $facadeResult = Paystack::listPlans($input);

    expect($managerResult)->toBeInstanceOf(ListPlansResponseData::class)
        ->and($managerResult->plans)->toHaveCount(1)
        ->and($managerResult->meta?->pagination?->perPage)->toBe(50)
        ->and($managerResult->meta?->pagination?->total)->toBe(1)
        ->and($managerResult->meta?->pagination?->currentPage)->toBe(1)
        ->and($managerResult->meta?->pagination?->pageCount)->toBe(1)
        ->and($managerResult->meta?->extra['skipped'] ?? null)->toBe(0)
        ->and($facadeResult)->toBeInstanceOf(ListPlansResponseData::class)
        ->and($facadeResult->plans)->toHaveCount(1);

    $mockClient->assertSent(fn (Request $request) => $request instanceof ListPlansRequest
        && $request->query()->all()['perPage'] === 50);
});

it('fetches a plan by integer identifier and maps sparse payloads safely', function () {
    $mockClient = new MockClient([
        FetchPlanRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Plan retrieved',
            'data' => [
                'id' => 11,
                'plan_code' => 'PLN_start',
                'amount' => 750000,
            ],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $result = app(FetchPlanAction::class)->execute(new FetchPlanInputData(11));

    expect($result)->toBeInstanceOf(FetchPlanResponseData::class)
        ->and($result->plan->id)->toBe(11)
        ->and($result->plan->planCode)->toBe('PLN_start')
        ->and($result->plan->name)->toBeNull()
        ->and($result->plan->interval)->toBeNull()
        ->and($result->plan->currency)->toBeNull();
});

it('encodes plan identifiers in fetch request paths', function () {
    $mockClient = new MockClient([
        FetchPlanRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Plan retrieved',
            'data' => [
                'id' => 11,
                'plan_code' => 'PLN/Starter 01',
            ],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    app(FetchPlanAction::class)->execute(new FetchPlanInputData('PLN/Starter 01'));

    $mockClient->assertSent(fn (Request $request) => $request instanceof FetchPlanRequest
        && $request->resolveEndpoint() === '/plan/PLN%2FStarter%2001');
});

it('lists plans with empty data and without meta', function () {
    $mockClient = new MockClient([
        ListPlansRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Plans retrieved',
            'data' => [],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $result = app(ListPlansAction::class)->execute(new ListPlansInputData);

    expect($result->plans)->toBe([])
        ->and($result->meta)->toBeNull();
});

it('throws on plan api errors', function (string $action) {
    $mockClient = new MockClient([
        CreatePlanRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Plan could not be created',
        ], 422),
        UpdatePlanRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Plan could not be updated',
        ], 422),
        FetchPlanRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Plan not found',
        ], 404),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    match ($action) {
        'create' => app(CreatePlanAction::class)->execute(new CreatePlanInputData(
            name: 'Starter',
            amount: 5000,
            interval: 'monthly',
        )),
        'update' => app(UpdatePlanAction::class)->execute(new UpdatePlanInputData(
            planCode: 'PLN_start',
            amount: 7500,
        )),
        'fetch' => app(FetchPlanAction::class)->execute(new FetchPlanInputData('PLN_start')),
        default => throw new InvalidArgumentException('Unknown plan action test case.'),
    };
})->with(['create', 'update', 'fetch'])->throws(RequestException::class);

it('omits unchanged optional values from the update plan request body', function () {
    $mockClient = new MockClient([
        UpdatePlanRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Plan updated',
            'data' => [
                'id' => 11,
                'name' => 'Starter',
                'plan_code' => 'PLN_start',
                'amount' => 500000,
                'interval' => 'monthly',
            ],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    app(UpdatePlanAction::class)->execute(new UpdatePlanInputData(
        planCode: 'PLN_start',
        sendSms: false,
    ));

    $mockClient->assertSent(fn (Request $request) => $request instanceof UpdatePlanRequest
        && $request->body()->all() === ['send_sms' => false]);
});

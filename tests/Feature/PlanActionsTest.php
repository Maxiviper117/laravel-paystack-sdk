<?php

use Maxiviper117\Paystack\Actions\Plan\CreatePlanAction;
use Maxiviper117\Paystack\Actions\Plan\FetchPlanAction;
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
    ));

    $fetched = app(FetchPlanAction::class)->execute(new FetchPlanInputData('PLN_start'));

    expect($updated)->toBeInstanceOf(UpdatePlanResponseData::class)
        ->and($updated->plan->name)->toBe('Starter Pro')
        ->and($fetched)->toBeInstanceOf(FetchPlanResponseData::class)
        ->and($fetched->plan->amount)->toBe(750000);
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
                'next' => 'https://api.paystack.co/plan?page=2',
                'previous' => null,
                'perPage' => 50,
            ],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $result = Paystack::listPlans(new ListPlansInputData(perPage: 50));

    expect($result)->toBeInstanceOf(ListPlansResponseData::class)
        ->and($result->plans)->toHaveCount(1)
        ->and($result->meta?->pagination?->perPage)->toBe(50);

    $mockClient->assertSent(fn (Request $request) => $request instanceof ListPlansRequest
        && $request->query()->all()['perPage'] === 50);
});

<?php

use Carbon\CarbonImmutable;
use Maxiviper117\Paystack\Actions\Subscription\CreateSubscriptionAction;
use Maxiviper117\Paystack\Actions\Subscription\DisableSubscriptionAction;
use Maxiviper117\Paystack\Actions\Subscription\EnableSubscriptionAction;
use Maxiviper117\Paystack\Actions\Subscription\FetchSubscriptionAction;
use Maxiviper117\Paystack\Actions\Subscription\GenerateSubscriptionUpdateLinkAction;
use Maxiviper117\Paystack\Actions\Subscription\ListSubscriptionsAction;
use Maxiviper117\Paystack\Actions\Subscription\SendSubscriptionUpdateLinkAction;
use Maxiviper117\Paystack\Data\Input\Subscription\CreateSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\DisableSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\EnableSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\FetchSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\GenerateSubscriptionUpdateLinkInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\ListSubscriptionsInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\SendSubscriptionUpdateLinkInputData;
use Maxiviper117\Paystack\Data\Output\Subscription\CreateSubscriptionResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\DisableSubscriptionResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\EnableSubscriptionResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\FetchSubscriptionResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\GenerateSubscriptionUpdateLinkResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\ListSubscriptionsResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\SendSubscriptionUpdateLinkResponseData;
use Maxiviper117\Paystack\Data\Subscription\SubscriptionStatus;
use Maxiviper117\Paystack\Facades\Paystack;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Subscription\CreateSubscriptionRequest;
use Maxiviper117\Paystack\Integrations\Requests\Subscription\DisableSubscriptionRequest;
use Maxiviper117\Paystack\Integrations\Requests\Subscription\EnableSubscriptionRequest;
use Maxiviper117\Paystack\Integrations\Requests\Subscription\FetchSubscriptionRequest;
use Maxiviper117\Paystack\Integrations\Requests\Subscription\GenerateSubscriptionUpdateLinkRequest;
use Maxiviper117\Paystack\Integrations\Requests\Subscription\ListSubscriptionsRequest;
use Maxiviper117\Paystack\Integrations\Requests\Subscription\SendSubscriptionUpdateLinkRequest;
use Maxiviper117\Paystack\PaystackManager;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

it('creates and fetches a subscription', function () {
    $mockClient = new MockClient([
        CreateSubscriptionRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Subscription created',
            'data' => [
                'id' => 31,
                'subscription_code' => 'SUB_123',
                'status' => 'active',
                'email_token' => 'token_123',
                'plan' => [
                    'plan_code' => 'PLN_start',
                    'amount' => 500000,
                    'interval' => 'monthly',
                ],
                'customer' => [
                    'email' => 'jane@example.com',
                    'customer_code' => 'CUS_123',
                ],
            ],
        ], 200),
        FetchSubscriptionRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Subscription fetched',
            'data' => [
                'id' => 31,
                'subscription_code' => 'SUB_123',
                'status' => 'active',
                'next_payment_date' => '2026-03-10T00:00:00+00:00',
            ],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $created = app(CreateSubscriptionAction::class)->execute(new CreateSubscriptionInputData(
        customer: 'CUS_123',
        plan: 'PLN_start',
    ));

    $fetched = app(FetchSubscriptionAction::class)->execute(new FetchSubscriptionInputData('SUB_123'));

    expect($created)->toBeInstanceOf(CreateSubscriptionResponseData::class)
        ->and($created->subscription->subscriptionCode)->toBe('SUB_123')
        ->and($created->subscription->customer?->customerCode)->toBe('CUS_123')
        ->and($fetched)->toBeInstanceOf(FetchSubscriptionResponseData::class)
        ->and($fetched->subscription->status)->toBe(SubscriptionStatus::Active)
        ->and($fetched->subscription->nextPaymentDate)->toBeInstanceOf(CarbonImmutable::class)
        ->and($fetched->subscription->nextPaymentDate?->toAtomString())->toBe('2026-03-10T00:00:00+00:00');
});

it('lists subscriptions and supports invoking the action directly', function () {
    $mockClient = new MockClient([
        ListSubscriptionsRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Subscriptions listed',
            'data' => [
                [
                    'id' => 31,
                    'subscription_code' => 'SUB_123',
                    'status' => 'active',
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

    $action = app(ListSubscriptionsAction::class);
    $result = $action(new ListSubscriptionsInputData(perPage: 50));

    expect($result)->toBeInstanceOf(ListSubscriptionsResponseData::class)
        ->and($result->subscriptions)->toHaveCount(1)
        ->and($result->meta?->pagination?->perPage)->toBe(50)
        ->and($result->meta?->pagination?->total)->toBe(1)
        ->and($result->meta?->pagination?->currentPage)->toBe(1)
        ->and($result->meta?->pagination?->pageCount)->toBe(1)
        ->and($result->meta?->extra['skipped'] ?? null)->toBe(0);
});

it('enables and disables a subscription', function () {
    $mockClient = new MockClient([
        EnableSubscriptionRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Subscription enabled',
        ], 200),
        DisableSubscriptionRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Subscription disabled',
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $enabled = app(EnableSubscriptionAction::class)->execute(new EnableSubscriptionInputData(
        code: 'SUB_123',
        token: 'email_token_123',
    ));

    $disabled = app(DisableSubscriptionAction::class)->execute(new DisableSubscriptionInputData(
        code: 'SUB_123',
        token: 'email_token_123',
    ));

    expect($enabled)->toBeInstanceOf(EnableSubscriptionResponseData::class)
        ->and($enabled->successful)->toBeTrue()
        ->and($disabled)->toBeInstanceOf(DisableSubscriptionResponseData::class)
        ->and($disabled->successful)->toBeTrue();

    $mockClient->assertSent(fn (Request $request) => $request instanceof EnableSubscriptionRequest
        && $request->body()->all() === [
            'code' => 'SUB_123',
            'token' => 'email_token_123',
        ]);

    $mockClient->assertSent(fn (Request $request) => $request instanceof DisableSubscriptionRequest
        && $request->body()->all() === [
            'code' => 'SUB_123',
            'token' => 'email_token_123',
        ]);
});

it('generates and emails a subscription update link', function () {
    $mockClient = new MockClient([
        GenerateSubscriptionUpdateLinkRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Link generated',
            'data' => [
                'link' => 'https://paystack.com/manage/subscriptions/SUB_123?subscription_token=abc',
            ],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $input = new GenerateSubscriptionUpdateLinkInputData(code: 'SUB_123');
    $generated = app(PaystackManager::class)->generateSubscriptionUpdateLink($input);
    $facadeGenerated = Paystack::generateSubscriptionUpdateLink($input);

    expect($generated)->toBeInstanceOf(GenerateSubscriptionUpdateLinkResponseData::class)
        ->and($generated->status)->toBeTrue()
        ->and($generated->link)->toContain('subscription_token=abc')
        ->and($facadeGenerated->link)->toBe($generated->link);

    $mockClient->assertSent(fn (Request $request) => $request instanceof GenerateSubscriptionUpdateLinkRequest
        && $request->resolveEndpoint() === '/subscription/SUB_123/manage/link');
});

it('sends a subscription update link by email', function () {
    $mockClient = new MockClient([
        SendSubscriptionUpdateLinkRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Email successfully sent',
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $input = new SendSubscriptionUpdateLinkInputData(code: 'SUB_123');
    $sent = app(PaystackManager::class)->sendSubscriptionUpdateLink($input);
    $facadeSent = Paystack::sendSubscriptionUpdateLink($input);

    expect($sent)->toBeInstanceOf(SendSubscriptionUpdateLinkResponseData::class)
        ->and($sent->status)->toBeTrue()
        ->and($sent->message)->toBe('Email successfully sent')
        ->and($facadeSent->message)->toBe($sent->message);

    $mockClient->assertSent(fn (Request $request) => $request instanceof SendSubscriptionUpdateLinkRequest
        && $request->resolveEndpoint() === '/subscription/SUB_123/manage/email');
});

it('creates a subscription without an authorization code and supports manager or facade usage', function () {
    $mockClient = new MockClient([
        CreateSubscriptionRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Subscription created',
            'data' => [
                'id' => 41,
                'subscription_code' => 'SUB_456',
                'status' => 'active',
                'customer' => [
                    'customer_code' => 'CUS_123',
                ],
                'plan' => [
                    'plan_code' => 'PLN_start',
                    'amount' => 500000,
                ],
            ],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $input = new CreateSubscriptionInputData(
        customer: 'CUS_123',
        plan: 'PLN_start',
    );

    $managerResult = app(PaystackManager::class)->createSubscription($input);
    $facadeResult = Paystack::createSubscription($input);

    expect($managerResult)->toBeInstanceOf(CreateSubscriptionResponseData::class)
        ->and($managerResult->subscription->subscriptionCode)->toBe('SUB_456')
        ->and($facadeResult->subscription->subscriptionCode)->toBe('SUB_456');

    $mockClient->assertSent(fn (Request $request) => $request instanceof CreateSubscriptionRequest
        && ! array_key_exists('authorization', $request->body()->all()));
});

it('fetches a subscription by integer identifier and maps sparse nested payloads safely', function () {
    $mockClient = new MockClient([
        FetchSubscriptionRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Subscription fetched',
            'data' => [
                'id' => 31,
                'subscription_code' => 'SUB_123',
                'status' => 'active',
                'plan' => [
                    'plan_code' => 'PLN_start',
                    'amount' => 500000,
                ],
                'customer' => [
                    'email' => 'jane@example.com',
                ],
            ],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $result = app(FetchSubscriptionAction::class)->execute(new FetchSubscriptionInputData(31));

    expect($result)->toBeInstanceOf(FetchSubscriptionResponseData::class)
        ->and($result->subscription->subscriptionCode)->toBe('SUB_123')
        ->and($result->subscription->plan?->planCode)->toBe('PLN_start')
        ->and($result->subscription->customer?->email)->toBe('jane@example.com')
        ->and($result->subscription->customer?->customerCode)->toBeNull();
});

it('encodes subscription identifiers in fetch request paths', function () {
    $mockClient = new MockClient([
        FetchSubscriptionRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Subscription fetched',
            'data' => [
                'id' => 31,
                'subscription_code' => 'SUB/2026?beta',
                'status' => 'active',
            ],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    app(FetchSubscriptionAction::class)->execute(new FetchSubscriptionInputData('SUB/2026?beta'));

    $mockClient->assertSent(fn (Request $request) => $request instanceof FetchSubscriptionRequest
        && $request->resolveEndpoint() === '/subscription/SUB%2F2026%3Fbeta');
});

it('lists subscriptions with empty data and without meta', function () {
    $mockClient = new MockClient([
        ListSubscriptionsRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Subscriptions listed',
            'data' => [],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $result = app(ListSubscriptionsAction::class)->execute(new ListSubscriptionsInputData);

    expect($result->subscriptions)->toBe([])
        ->and($result->meta)->toBeNull();
});

it('throws on subscription api errors', function (string $action) {
    $mockClient = new MockClient([
        CreateSubscriptionRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Subscription could not be created',
        ], 422),
        FetchSubscriptionRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Subscription not found',
        ], 404),
        EnableSubscriptionRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Subscription could not be enabled',
        ], 422),
        DisableSubscriptionRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Subscription could not be disabled',
        ], 422),
        GenerateSubscriptionUpdateLinkRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Link could not be generated',
        ], 422),
        SendSubscriptionUpdateLinkRequest::class => MockResponse::make([
            'status' => false,
            'message' => 'Email could not be sent',
        ], 422),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    match ($action) {
        'create' => app(CreateSubscriptionAction::class)->execute(new CreateSubscriptionInputData(
            customer: 'CUS_123',
            plan: 'PLN_start',
        )),
        'fetch' => app(FetchSubscriptionAction::class)->execute(new FetchSubscriptionInputData('SUB_123')),
        'enable' => app(EnableSubscriptionAction::class)->execute(new EnableSubscriptionInputData(
            code: 'SUB_123',
            token: 'TOKEN_123',
        )),
        'disable' => app(DisableSubscriptionAction::class)->execute(new DisableSubscriptionInputData(
            code: 'SUB_123',
            token: 'TOKEN_123',
        )),
        'generate-link' => app(GenerateSubscriptionUpdateLinkAction::class)->execute(
            new GenerateSubscriptionUpdateLinkInputData(code: 'SUB_123')
        ),
        'send-link' => app(SendSubscriptionUpdateLinkAction::class)->execute(
            new SendSubscriptionUpdateLinkInputData(code: 'SUB_123')
        ),
        default => throw new InvalidArgumentException('Unknown subscription action test case.'),
    };
})->with(['create', 'fetch', 'enable', 'disable', 'generate-link', 'send-link'])->throws(RequestException::class);

it('rejects malformed subscription timestamps', function () {
    app(PaystackConnector::class)->withMockClient(new MockClient([
        FetchSubscriptionRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Subscription fetched',
            'data' => [
                'id' => 31,
                'subscription_code' => 'SUB_123',
                'status' => 'active',
                'next_payment_date' => 'not-a-date',
            ],
        ], 200),
    ]));

    app(FetchSubscriptionAction::class)->execute(new FetchSubscriptionInputData('SUB_123'));
})->throws(InvalidArgumentException::class);

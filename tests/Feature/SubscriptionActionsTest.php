<?php

use Maxiviper117\Paystack\Actions\Subscription\CreateSubscriptionAction;
use Maxiviper117\Paystack\Actions\Subscription\DisableSubscriptionAction;
use Maxiviper117\Paystack\Actions\Subscription\EnableSubscriptionAction;
use Maxiviper117\Paystack\Actions\Subscription\FetchSubscriptionAction;
use Maxiviper117\Paystack\Actions\Subscription\ListSubscriptionsAction;
use Maxiviper117\Paystack\Data\Input\Subscription\CreateSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\DisableSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\EnableSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\FetchSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\ListSubscriptionsInputData;
use Maxiviper117\Paystack\Data\Output\Subscription\CreateSubscriptionResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\DisableSubscriptionResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\EnableSubscriptionResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\FetchSubscriptionResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\ListSubscriptionsResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Subscription\CreateSubscriptionRequest;
use Maxiviper117\Paystack\Integrations\Requests\Subscription\DisableSubscriptionRequest;
use Maxiviper117\Paystack\Integrations\Requests\Subscription\EnableSubscriptionRequest;
use Maxiviper117\Paystack\Integrations\Requests\Subscription\FetchSubscriptionRequest;
use Maxiviper117\Paystack\Integrations\Requests\Subscription\ListSubscriptionsRequest;
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
        ->and($fetched->subscription->status)->toBe('active');
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
                'next' => 'https://api.paystack.co/subscription?page=2',
                'previous' => null,
                'perPage' => 50,
            ],
        ], 200),
    ]);

    app(PaystackConnector::class)->withMockClient($mockClient);

    $action = app(ListSubscriptionsAction::class);
    $result = $action(new ListSubscriptionsInputData(perPage: 50));

    expect($result)->toBeInstanceOf(ListSubscriptionsResponseData::class)
        ->and($result->subscriptions)->toHaveCount(1)
        ->and($result->meta?->pagination?->perPage)->toBe(50);
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
});

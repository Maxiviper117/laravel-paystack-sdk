<?php

use Carbon\CarbonImmutable;
use Maxiviper117\Paystack\Data\Customer\CustomerData;
use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Customer\CreateCustomerRequest;
use Maxiviper117\Paystack\Integrations\Requests\Customer\UpdateCustomerRequest;
use Maxiviper117\Paystack\Integrations\Requests\Subscription\CreateSubscriptionRequest;
use Maxiviper117\Paystack\Integrations\Requests\Subscription\DisableSubscriptionRequest;
use Maxiviper117\Paystack\Integrations\Requests\Subscription\EnableSubscriptionRequest;
use Maxiviper117\Paystack\Integrations\Requests\Subscription\FetchSubscriptionRequest;
use Maxiviper117\Paystack\Models\PaystackCustomer;
use Maxiviper117\Paystack\Models\PaystackSubscription;
use Maxiviper117\Paystack\Tests\Fixtures\BillableUser;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

it('creates and persists a paystack customer for a billable model', function () {
    app(PaystackConnector::class)->withMockClient(new MockClient([
        CreateCustomerRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Customer created',
            'data' => [
                'email' => 'jane@example.com',
                'customer_code' => 'CUS_123',
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'phone' => '+27110001111',
                'metadata' => [
                    'billable_type' => BillableUser::class,
                    'billable_id' => 1,
                ],
            ],
        ], 200),
    ]));

    $user = BillableUser::query()->create([
        'email' => 'jane@example.com',
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'phone' => '+27110001111',
    ]);

    $response = $user->createAsPaystackCustomer();
    $customer = $user->paystackCustomer()->first();

    expect($response->customer)->toBeInstanceOf(CustomerData::class)
        ->and($customer)->toBeInstanceOf(PaystackCustomer::class)
        ->and($customer?->customer_code)->toBe('CUS_123')
        ->and($customer?->email)->toBe('jane@example.com')
        ->and($customer?->metadata)->toMatchArray([
            'billable_type' => BillableUser::class,
            'billable_id' => 1,
        ]);
});

it('creates, syncs, enables, and disables a stored paystack subscription for a billable model', function () {
    $mockClient = new MockClient([
        CreateCustomerRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Customer created',
            'data' => [
                'email' => 'jane@example.com',
                'customer_code' => 'CUS_123',
            ],
        ], 200),
        CreateSubscriptionRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Subscription created',
            'data' => [
                'id' => 31,
                'subscription_code' => 'SUB_123',
                'status' => 'active',
                'email_token' => 'token_123',
                'next_payment_date' => '2026-05-10T00:00:00+00:00',
                'plan' => [
                    'plan_code' => 'PLN_growth',
                    'amount' => 500000,
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
                'email_token' => 'token_123',
                'next_payment_date' => '2026-06-10T00:00:00+00:00',
                'plan' => [
                    'plan_code' => 'PLN_growth',
                    'amount' => 500000,
                ],
            ],
        ], 200),
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

    $user = BillableUser::query()->create([
        'email' => 'jane@example.com',
    ]);

    $created = $user->createPaystackSubscription('PLN_growth', 'primary');
    $fetched = $user->fetchPaystackSubscription('SUB_123', 'primary');
    $enabled = $user->enablePaystackSubscription('primary');
    $disabled = $user->disablePaystackSubscription('primary');

    $subscription = $user->paystackSubscription('primary');

    expect($created->subscription->subscriptionCode)->toBe('SUB_123')
        ->and($fetched->subscription->nextPaymentDate)->toBeInstanceOf(CarbonImmutable::class)
        ->and($enabled->successful)->toBeTrue()
        ->and($disabled->successful)->toBeTrue()
        ->and($subscription)->toBeInstanceOf(PaystackSubscription::class)
        ->and($subscription?->subscription_code)->toBe('SUB_123')
        ->and($subscription?->plan_code)->toBe('PLN_growth')
        ->and($subscription?->email_token)->toBe('token_123')
        ->and($subscription?->next_payment_date?->toAtomString())->toBe('2026-06-10T00:00:00+00:00');

    $mockClient->assertSent(fn (Request $request) => $request instanceof EnableSubscriptionRequest
        && $request->body()->all() === [
            'code' => 'SUB_123',
            'token' => 'token_123',
        ]);

    $mockClient->assertSent(fn (Request $request) => $request instanceof DisableSubscriptionRequest
        && $request->body()->all() === [
            'code' => 'SUB_123',
            'token' => 'token_123',
        ]);
});

it('updates a stored paystack customer when syncing an existing billable model', function () {
    app(PaystackConnector::class)->withMockClient(new MockClient([
        UpdateCustomerRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Customer updated',
            'data' => [
                'email' => 'jane@example.com',
                'customer_code' => 'CUS_123',
                'first_name' => 'Jane',
                'last_name' => 'Updated',
            ],
        ], 200),
    ]));

    $user = BillableUser::query()->create([
        'email' => 'jane@example.com',
        'first_name' => 'Jane',
        'last_name' => 'Updated',
    ]);

    PaystackCustomer::query()->create([
        'billable_type' => $user->getMorphClass(),
        'billable_id' => $user->getKey(),
        'customer_code' => 'CUS_123',
        'email' => 'jane@example.com',
    ]);

    $response = $user->updateAsPaystackCustomer();
    $freshUser = $user->fresh();
    $customer = $freshUser?->paystackCustomer()->first();

    expect($response->customer->lastName)->toBe('Updated')
        ->and($customer?->last_name)->toBe('Updated');
});

it('rejects billable customer sync when the model cannot provide an email address', function () {
    $user = BillableUser::query()->create();

    $user->createAsPaystackCustomer();
})->throws(InvalidPaystackInputException::class);

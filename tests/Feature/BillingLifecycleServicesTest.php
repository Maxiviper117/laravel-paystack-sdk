<?php

use Carbon\CarbonImmutable;
use Maxiviper117\Paystack\Billing\BillableCustomerLifecycleService;
use Maxiviper117\Paystack\Billing\BillableSubscriptionLifecycleService;
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

it('creates, updates, and syncs customer lifecycle through the billing customer service', function () {
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
            ],
        ], 200),
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
        'last_name' => 'Doe',
        'phone' => '+27110001111',
    ]);

    $service = app(BillableCustomerLifecycleService::class);
    $created = $service->create($user);
    $user->forceFill(['last_name' => 'Updated'])->save();
    $updated = $service->update($user);
    $synced = $service->sync($user);

    expect($created->customer->customerCode)->toBe('CUS_123')
        ->and($updated->customer->lastName)->toBe('Updated')
        ->and($synced)->toBeInstanceOf(PaystackCustomer::class)
        ->and($synced->last_name)->toBe('Updated');
});

it('creates, fetches, enables, and disables subscription lifecycle through the billing subscription service', function () {
    app(PaystackConnector::class)->withMockClient(new MockClient([
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
    ]));

    $user = BillableUser::query()->create([
        'email' => 'jane@example.com',
    ]);

    $service = app(BillableSubscriptionLifecycleService::class);
    $created = $service->create($user, 'PLN_growth', 'primary');
    $fetched = $service->fetch($user, 'SUB_123', 'primary');
    $enabled = $service->enable($user, 'primary');
    $disabled = $service->disable($user, 'primary');

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
});

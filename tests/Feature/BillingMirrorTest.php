<?php

use Maxiviper117\Paystack\Data\Dispute\DisputeData;
use Maxiviper117\Paystack\Data\Output\Webhook\PaystackWebhookEventData;
use Maxiviper117\Paystack\Data\Plan\PlanData;
use Maxiviper117\Paystack\Data\Refund\RefundData;
use Maxiviper117\Paystack\Data\Subscription\SubscriptionData;
use Maxiviper117\Paystack\Data\Transaction\TransactionData;
use Maxiviper117\Paystack\Events\PaystackWebhookReceived;
use Maxiviper117\Paystack\Listeners\SyncPaystackBillingLayer;
use Maxiviper117\Paystack\Models\PaystackCustomer;
use Maxiviper117\Paystack\Models\PaystackDispute;
use Maxiviper117\Paystack\Models\PaystackPlan;
use Maxiviper117\Paystack\Models\PaystackRefund;
use Maxiviper117\Paystack\Models\PaystackSubscription;
use Maxiviper117\Paystack\Models\PaystackTransaction;
use Maxiviper117\Paystack\Models\PaystackWebhookCall;

it('syncs mirrored billing records from dto payloads without duplicating rows', function () {
    $customerPayload = [
        'email' => 'alice@example.com',
        'customer_code' => 'CUS_1',
        'first_name' => 'Alice',
        'last_name' => 'Example',
        'phone' => '+27110000001',
        'metadata' => ['source' => 'test'],
    ];

    $planPayload = [
        'id' => 21,
        'name' => 'Growth',
        'plan_code' => 'PLN_GROWTH',
        'amount' => 500000,
        'interval' => 'monthly',
        'description' => 'Monthly growth plan',
        'currency' => 'NGN',
        'subscriptions' => [
            ['subscription_code' => 'SUB_A', 'status' => 'active'],
            ['subscription_code' => 'SUB_B', 'status' => 'canceled'],
        ],
    ];

    $subscriptionPayload = [
        'id' => 31,
        'subscription_code' => 'SUB_1',
        'status' => 'active',
        'email_token' => 'token_1',
        'amount' => 500000,
        'next_payment_date' => '2026-04-10T00:00:00+00:00',
        'open_invoice' => 'INV_1',
        'plan' => $planPayload,
        'customer' => $customerPayload,
    ];

    $transactionPayload = [
        'id' => 41,
        'reference' => 'TRX_1',
        'status' => 'success',
        'amount' => 500000,
        'currency' => 'NGN',
        'paid_at' => '2026-04-10T00:00:00+00:00',
        'channel' => 'card',
        'customer' => $customerPayload,
    ];

    $refundPayload = [
        'id' => 51,
        'integration' => 7,
        'transaction' => [
            'id' => 41,
            'reference' => 'TRX_1',
            'status' => 'success',
            'amount' => 500000,
            'currency' => 'NGN',
            'customer' => $customerPayload,
        ],
        'amount' => 500000,
        'deducted_amount' => 0,
        'currency' => 'NGN',
        'status' => 'processed',
        'refunded_at' => '2026-04-11T00:00:00+00:00',
        'customer' => $customerPayload,
        'bank_reference' => 'BANK_REF_1',
    ];

    $disputePayload = [
        'id' => 61,
        'refund_amount' => 250000,
        'currency' => 'NGN',
        'status' => 'pending',
        'resolution' => null,
        'domain' => 'test',
        'category' => 'fraud',
        'note' => 'Chargeback',
        'attachments' => null,
        'last4' => '1234',
        'bin' => '507850',
        'transaction_reference' => 'TRX_1',
        'created_by' => 123,
        'organization' => 456,
        'integration' => 789,
        'evidence' => ['receipt' => 'https://files.example.com/receipt.pdf'],
        'resolved_at' => null,
        'due_at' => '2026-04-12T00:00:00+00:00',
        'created_at' => '2026-04-10T00:00:00+00:00',
        'updated_at' => '2026-04-10T12:00:00+00:00',
        'transaction' => [
            'id' => 41,
            'domain' => 'test',
            'status' => 'success',
            'reference' => 'TRX_1',
            'amount' => 500000,
            'currency' => 'NGN',
            'customer' => $customerPayload,
        ],
        'customer' => [
            'id' => 200,
            'first_name' => 'Alice',
            'last_name' => 'Example',
            'email' => 'alice@example.com',
            'customer_code' => 'CUS_1',
            'phone' => '+27110000001',
            'metadata' => ['source' => 'test'],
        ],
    ];

    $plan = PaystackPlan::syncFromPlanData(PlanData::fromPayload($planPayload));
    $subscription = PaystackSubscription::syncFromSubscriptionData(SubscriptionData::fromPayload($subscriptionPayload), 'primary');
    $transaction = PaystackTransaction::syncFromTransactionData(TransactionData::fromPayload($transactionPayload));
    $refund = PaystackRefund::syncFromRefundData(RefundData::fromPayload($refundPayload));
    $dispute = PaystackDispute::syncFromDisputeData(DisputeData::fromPayload($disputePayload));

    expect(PaystackCustomer::query()->count())->toBe(1)
        ->and(PaystackPlan::query()->count())->toBe(1)
        ->and(PaystackTransaction::query()->count())->toBe(1)
        ->and($subscription->plan?->plan_code)->toBe('PLN_GROWTH')
        ->and($subscription->customer?->customer_code)->toBe('CUS_1')
        ->and($transaction->customer?->customer_code)->toBe('CUS_1')
        ->and($refund->transaction?->reference)->toBe('TRX_1')
        ->and($refund->customer?->customer_code)->toBe('CUS_1')
        ->and($dispute->transaction?->reference)->toBe('TRX_1')
        ->and($dispute->customer?->customer_code)->toBe('CUS_1');

    /** @var array{subscriptions?: array<int, array{subscription_code?: string}>} $snapshot */
    $snapshot = $plan->payload_snapshot ?? [];
    /** @var array<int, array{subscription_code?: string}> $subscriptions */
    $subscriptions = $snapshot['subscriptions'] ?? [];

    expect($subscriptions)->toHaveCount(1)
        ->and($subscriptions[0]['subscription_code'] ?? null)->toBe('SUB_A');
});

it('reconciles a validated subscription webhook into the local billing mirror', function () {
    $payload = [
        'event' => 'subscription.create',
        'data' => [
            'id' => 200,
            'subscription_code' => 'SUB_WEBHOOK',
            'status' => 'active',
            'email_token' => 'token_webhook',
            'amount' => 125000,
            'next_payment_date' => '2026-04-15T00:00:00+00:00',
            'open_invoice' => 'INV_WEBHOOK',
            'plan' => [
                'id' => 20,
                'name' => 'Webhook Plan',
                'plan_code' => 'PLAN_WEBHOOK',
                'amount' => 125000,
                'interval' => 'monthly',
            ],
            'customer' => [
                'email' => 'webhook@example.com',
                'customer_code' => 'CUS_WEBHOOK',
                'first_name' => 'Web',
                'last_name' => 'Hook',
            ],
        ],
    ];

    $event = PaystackWebhookEventData::fromPayload($payload);
    $listener = app(SyncPaystackBillingLayer::class);

    $listener->handle(new PaystackWebhookReceived(new PaystackWebhookCall, $event));
    $listener->handle(new PaystackWebhookReceived(new PaystackWebhookCall, $event));

    $subscription = PaystackSubscription::query()->where('subscription_code', 'SUB_WEBHOOK')->first();

    expect(PaystackCustomer::query()->where('customer_code', 'CUS_WEBHOOK')->count())->toBe(1)
        ->and(PaystackPlan::query()->where('plan_code', 'PLAN_WEBHOOK')->count())->toBe(1)
        ->and(PaystackSubscription::query()->where('subscription_code', 'SUB_WEBHOOK')->count())->toBe(1)
        ->and($subscription?->paystack_customer_id)->not->toBeNull()
        ->and($subscription?->paystack_plan_id)->not->toBeNull();
});

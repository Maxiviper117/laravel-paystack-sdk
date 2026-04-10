<?php

use Maxiviper117\Paystack\Data\Output\Webhook\PaystackWebhookEventData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\ChargeDisputeCreatedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\ChargeDisputeRemindedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\ChargeDisputeResolvedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\ChargeSuccessWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\CustomerIdentificationFailedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\CustomerIdentificationSuccessWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\DedicatedAccountAssignFailedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\DedicatedAccountAssignSuccessWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\InvoiceCreatedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\InvoicePaymentFailedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\InvoiceUpdatedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\PaymentRequestPendingWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\PaymentRequestSuccessWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\RefundFailedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\RefundPendingWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\RefundProcessedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\RefundProcessingWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionCreatedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionDisabledWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionExpiringCardsWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionNotRenewingWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\TransferFailedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\TransferReversedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\TransferSuccessWebhookData;
use Maxiviper117\Paystack\Events\PaystackWebhookReceived;
use Maxiviper117\Paystack\Listeners\PaystackWebhookHandler;
use Maxiviper117\Paystack\Models\PaystackWebhookCall;

/**
 * @return array<string, mixed>
 */
function webhookHandlerFixture(string $name): array
{
    $path = __DIR__.'/../../reference/webhook_events/'.$name.'.json';
    $contents = file_get_contents($path);

    expect($contents)->not->toBeFalse();

    /** @var array<string, mixed> $decoded */
    $decoded = json_decode((string) $contents, true, 512, JSON_THROW_ON_ERROR);

    return $decoded;
}

/**
 * @param  list<string>  $calls
 */
function recordingWebhookHandler(array &$calls): PaystackWebhookHandler
{
    return (new PaystackWebhookHandler)
        ->onChargeSuccess(function (ChargeSuccessWebhookData $webhook) use (&$calls): void {
            $calls[] = sprintf('charge.success:%s:%d', $webhook->reference, $webhook->amount);
        })
        ->onChargeDisputeCreated(function (ChargeDisputeCreatedWebhookData $webhook) use (&$calls): void {
            $calls[] = sprintf('charge.dispute.create:%s', $webhook->disputeId);
        })
        ->onChargeDisputeReminded(function (ChargeDisputeRemindedWebhookData $webhook) use (&$calls): void {
            $calls[] = sprintf('charge.dispute.remind:%s', $webhook->disputeId);
        })
        ->onChargeDisputeResolved(function (ChargeDisputeResolvedWebhookData $webhook) use (&$calls): void {
            $calls[] = sprintf('charge.dispute.resolve:%s', $webhook->disputeId);
        })
        ->onCustomerIdentificationSucceeded(function (CustomerIdentificationSuccessWebhookData $webhook) use (&$calls): void {
            $calls[] = sprintf('customeridentification.success:%s', $webhook->customerCode);
        })
        ->onCustomerIdentificationFailed(function (CustomerIdentificationFailedWebhookData $webhook) use (&$calls): void {
            $calls[] = sprintf('customeridentification.failed:%s', $webhook->customerCode);
        })
        ->onDedicatedAccountAssigned(function (DedicatedAccountAssignSuccessWebhookData $webhook) use (&$calls): void {
            $calls[] = sprintf('dedicatedaccount.assign.success:%s', $webhook->customer->customerCode);
        })
        ->onDedicatedAccountAssignFailed(function (DedicatedAccountAssignFailedWebhookData $webhook) use (&$calls): void {
            $calls[] = sprintf('dedicatedaccount.assign.failed:%s', $webhook->customer->customerCode);
        })
        ->onInvoiceCreated(function (InvoiceCreatedWebhookData $webhook) use (&$calls): void {
            $calls[] = sprintf('invoice.create:%s:%s', $webhook->invoiceCode, $webhook->paid ? 'paid' : 'unpaid');
        })
        ->onInvoiceUpdated(function (InvoiceUpdatedWebhookData $webhook) use (&$calls): void {
            $calls[] = sprintf('invoice.update:%s:%s', $webhook->invoiceCode, $webhook->paid ? 'paid' : 'unpaid');
        })
        ->onInvoicePaymentFailed(function (InvoicePaymentFailedWebhookData $webhook) use (&$calls): void {
            $calls[] = sprintf('invoice.payment_failed:%s:%s', $webhook->invoiceCode, $webhook->paid ? 'paid' : 'unpaid');
        })
        ->onPaymentRequestPending(function (PaymentRequestPendingWebhookData $webhook) use (&$calls): void {
            $calls[] = sprintf('paymentrequest.pending:%s', $webhook->requestCode);
        })
        ->onPaymentRequestSuccess(function (PaymentRequestSuccessWebhookData $webhook) use (&$calls): void {
            $calls[] = sprintf('paymentrequest.success:%s', $webhook->requestCode);
        })
        ->onRefundPending(function (RefundPendingWebhookData $webhook) use (&$calls): void {
            $calls[] = sprintf('refund.pending:%s', $webhook->status);
        })
        ->onRefundProcessing(function (RefundProcessingWebhookData $webhook) use (&$calls): void {
            $calls[] = sprintf('refund.processing:%s', $webhook->status);
        })
        ->onRefundProcessed(function (RefundProcessedWebhookData $webhook) use (&$calls): void {
            $calls[] = sprintf('refund.processed:%s', $webhook->status);
        })
        ->onRefundFailed(function (RefundFailedWebhookData $webhook) use (&$calls): void {
            $calls[] = sprintf('refund.failed:%s', $webhook->status);
        })
        ->onSubscriptionCreated(function (SubscriptionCreatedWebhookData $webhook) use (&$calls): void {
            $calls[] = sprintf('subscription.create:%s', $webhook->subscriptionCode);
        })
        ->onSubscriptionNotRenewing(function (SubscriptionNotRenewingWebhookData $webhook) use (&$calls): void {
            $calls[] = sprintf('subscription.not_renew:%s', $webhook->status->value);
        })
        ->onSubscriptionDisabled(function (SubscriptionDisabledWebhookData $webhook) use (&$calls): void {
            $calls[] = sprintf('subscription.disable:%s', $webhook->status->value);
        })
        ->onSubscriptionExpiringCards(function (SubscriptionExpiringCardsWebhookData $webhook) use (&$calls): void {
            $calls[] = sprintf('subscription.expiring_cards:%d', count($webhook->cards));
        })
        ->onTransferSuccess(function (TransferSuccessWebhookData $webhook) use (&$calls): void {
            $calls[] = sprintf('transfer.success:%s', $webhook->reference);
        })
        ->onTransferFailed(function (TransferFailedWebhookData $webhook) use (&$calls): void {
            $calls[] = sprintf('transfer.failed:%s', $webhook->reference);
        })
        ->onTransferReversed(function (TransferReversedWebhookData $webhook) use (&$calls): void {
            $calls[] = sprintf('transfer.reversed:%s', $webhook->reference);
        })
        ->onUnhandled(function (PaystackWebhookEventData $webhook) use (&$calls): void {
            $calls[] = sprintf('unhandled:%s', $webhook->event);
        });
}

dataset('paystackWebhookHandlerCases', [
    'charge success' => [
        [
            'event' => 'charge.success',
            'data' => [
                'id' => 2001,
                'domain' => 'live',
                'status' => 'success',
                'reference' => 'txn_123',
                'amount' => 450000,
                'currency' => 'NGN',
                'paid_at' => '2026-03-03T08:00:00+00:00',
                'channel' => 'card',
                'gateway_response' => 'Successful',
                'customer' => [
                    'email' => 'customer@example.com',
                    'customer_code' => 'CUS_123',
                    'first_name' => 'Ada',
                    'last_name' => 'Lovelace',
                ],
            ],
        ],
        ['charge.success:txn_123:450000'],
    ],
    'invoice create' => [
        [
            'event' => 'invoice.create',
            'data' => [
                'id' => 101,
                'domain' => 'test',
                'invoice_code' => 'INV_123',
                'status' => 'pending',
                'paid' => false,
                'amount' => 150000,
                'description' => 'March invoice',
                'period_start' => '2026-03-01T00:00:00+00:00',
                'period_end' => '2026-03-31T23:59:59+00:00',
                'next_payment_date' => '2026-04-01T00:00:00+00:00',
                'subscription' => [
                    'id' => 50,
                    'subscription_code' => 'SUB_123',
                    'status' => 'active',
                    'email_token' => 'token_123',
                    'next_payment_date' => '2026-04-01T00:00:00+00:00',
                    'open_invoice' => 'INV_123',
                ],
                'customer' => [
                    'email' => 'billing@example.com',
                    'customer_code' => 'CUS_123',
                ],
                'authorization' => [
                    'authorization_code' => 'AUTH_123',
                ],
                'transaction' => [
                    'id' => 901,
                    'status' => 'pending',
                    'reference' => 'txn_invoice_123',
                    'amount' => 150000,
                    'currency' => 'NGN',
                ],
            ],
        ],
        ['invoice.create:INV_123:unpaid'],
    ],
    'invoice update' => [
        [
            'event' => 'invoice.update',
            'data' => [
                'id' => 102,
                'domain' => 'test',
                'invoice_code' => 'INV_124',
                'status' => 'success',
                'paid' => true,
                'amount' => 150000,
            ],
        ],
        ['invoice.update:INV_124:paid'],
    ],
    'invoice payment failed' => [
        [
            'event' => 'invoice.payment_failed',
            'data' => [
                'id' => 103,
                'domain' => 'live',
                'invoice_code' => 'INV_125',
                'status' => 'failed',
                'paid' => 0,
                'amount' => 175000,
            ],
        ],
        ['invoice.payment_failed:INV_125:unpaid'],
    ],
    'subscription create' => [
        [
            'event' => 'subscription.create',
            'data' => [
                'id' => 200,
                'domain' => 'test',
                'subscription_code' => 'SUB_200',
                'status' => 'active',
                'email_token' => 'email_token_200',
                'amount' => 125000,
                'next_payment_date' => '2026-04-05T00:00:00+00:00',
                'open_invoice' => 'INV_200',
                'plan' => [
                    'id' => 20,
                    'name' => 'Pro',
                    'plan_code' => 'PLAN_PRO',
                    'amount' => 125000,
                    'interval' => 'monthly',
                ],
                'customer' => [
                    'email' => 'subscriber@example.com',
                    'customer_code' => 'CUS_200',
                ],
            ],
        ],
        ['subscription.create:SUB_200'],
    ],
    'subscription not renew' => [
        [
            'event' => 'subscription.not_renew',
            'data' => [
                'id' => 201,
                'subscription_code' => 'SUB_201',
                'status' => 'non-renewing',
            ],
        ],
        ['subscription.not_renew:non-renewing'],
    ],
    'subscription disable' => [
        [
            'event' => 'subscription.disable',
            'data' => [
                'id' => 202,
                'subscription_code' => 'SUB_202',
                'status' => 'complete',
            ],
        ],
        ['subscription.disable:complete'],
    ],
    'charge dispute create fixture' => [
        webhookHandlerFixture('charge_dispute_create'),
        ['charge.dispute.create:358950'],
    ],
    'charge dispute remind fixture' => [
        webhookHandlerFixture('charge_dispute_remind'),
        ['charge.dispute.remind:358950'],
    ],
    'charge dispute resolve fixture' => [
        webhookHandlerFixture('charge_dispute_resolve'),
        ['charge.dispute.resolve:358949'],
    ],
    'customer identification success fixture' => [
        webhookHandlerFixture('customeridentification_success'),
        ['customeridentification.success:CUS_xnxdt6s1zg1f4nx'],
    ],
    'customer identification failed fixture' => [
        webhookHandlerFixture('customeridentification_failed'),
        ['customeridentification.failed:CUS_XXXXXXXXXXXXXXX'],
    ],
    'dedicated account assign success fixture' => [
        webhookHandlerFixture('dedicatedaccount_assign_success'),
        ['dedicatedaccount.assign.success:CUS_hp05n9khsqcesz2'],
    ],
    'dedicated account assign failed fixture' => [
        webhookHandlerFixture('dedicatedaccount_assign_failed'),
        ['dedicatedaccount.assign.failed:CUS_hcekca0j0bbg2m4'],
    ],
    'payment request pending fixture' => [
        webhookHandlerFixture('paymentrequest_pending'),
        ['paymentrequest.pending:PRQ_y0paeo93jh99mho'],
    ],
    'payment request success fixture' => [
        webhookHandlerFixture('paymentrequest_success'),
        ['paymentrequest.success:PRQ_y0paeo93jh99mho'],
    ],
    'refund pending fixture' => [
        webhookHandlerFixture('refund_pending'),
        ['refund.pending:pending'],
    ],
    'refund processing fixture' => [
        webhookHandlerFixture('refund_processing'),
        ['refund.processing:processing'],
    ],
    'refund processed fixture' => [
        webhookHandlerFixture('refund_processed'),
        ['refund.processed:processed'],
    ],
    'refund failed fixture' => [
        webhookHandlerFixture('refund_failed'),
        ['refund.failed:failed'],
    ],
    'subscription expiring cards fixture' => [
        webhookHandlerFixture('subscription_expiring_cards'),
        ['subscription.expiring_cards:1'],
    ],
    'transfer success fixture' => [
        webhookHandlerFixture('transfer_success'),
        ['transfer.success:acv_9ee55786-2323-4760-98e2-6380c9cb3f68'],
    ],
    'transfer failed fixture' => [
        webhookHandlerFixture('transfer_failed'),
        ['transfer.failed:1976435206'],
    ],
    'transfer reversed fixture' => [
        webhookHandlerFixture('transfer_reversed'),
        ['transfer.reversed:jvrjckwenm'],
    ],
    'unhandled' => [
        [
            'event' => 'customer.create',
            'data' => [
                'id' => 300,
            ],
        ],
        ['unhandled:customer.create'],
    ],
]);

it('dispatches each supported webhook event to the matching callback', function (array $payload, array $expectedCalls) {
    /** @var list<string> $calls */
    $calls = [];
    $handler = recordingWebhookHandler($calls);

    /** @var array<string, mixed> $payload */
    $event = PaystackWebhookEventData::fromPayload($payload);

    $handler->handle(new PaystackWebhookReceived(
        webhookCall: new PaystackWebhookCall,
        event: $event,
    ));

    expect($calls)->toBe($expectedCalls);
})->with('paystackWebhookHandlerCases');

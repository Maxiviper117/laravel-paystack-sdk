<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
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

class HandlePaystackWebhook
{
    public function handle(PaystackWebhookReceived $event): void
    {
        $this->cacheLatestWebhook($event->event);

        // ->handle() is passing the event data to the appropriate callback based on the event type.
        $this->createWebhookHandler()->handle($event);
    }

    private function createWebhookHandler(): PaystackWebhookHandler
    {
        return (new PaystackWebhookHandler)
            ->onChargeSuccess($this->logChargeSuccess(...))
            ->onChargeDisputeCreated($this->logChargeDisputeCreated(...))
            ->onChargeDisputeReminded($this->logChargeDisputeReminded(...))
            ->onChargeDisputeResolved($this->logChargeDisputeResolved(...))
            ->onCustomerIdentificationSucceeded($this->logCustomerIdentificationSuccess(...))
            ->onCustomerIdentificationFailed($this->logCustomerIdentificationFailed(...))
            ->onDedicatedAccountAssigned($this->logDedicatedAccountAssignSuccess(...))
            ->onDedicatedAccountAssignFailed($this->logDedicatedAccountAssignFailed(...))
            ->onInvoiceCreated($this->logInvoiceCreated(...))
            ->onInvoiceUpdated($this->logInvoiceUpdated(...))
            ->onInvoicePaymentFailed($this->logInvoicePaymentFailed(...))
            ->onPaymentRequestPending($this->logPaymentRequestPending(...))
            ->onPaymentRequestSuccess($this->logPaymentRequestSuccess(...))
            ->onRefundPending($this->logRefundPending(...))
            ->onRefundProcessing($this->logRefundProcessing(...))
            ->onRefundProcessed($this->logRefundProcessed(...))
            ->onRefundFailed($this->logRefundFailed(...))
            ->onSubscriptionCreated($this->logSubscriptionCreated(...))
            ->onSubscriptionNotRenewing($this->logSubscriptionNotRenewing(...))
            ->onSubscriptionDisabled($this->logSubscriptionDisabled(...))
            ->onSubscriptionExpiringCards($this->logSubscriptionExpiringCards(...))
            ->onTransferSuccess($this->logTransferSuccess(...))
            ->onTransferFailed($this->logTransferFailed(...))
            ->onTransferReversed($this->logTransferReversed(...))
            ->onUnhandled($this->logUnhandledWebhook(...));

        // (...) syntax is used to pass the methods as callbacks while preserving their signatures.
    }

    private function logChargeSuccess(ChargeSuccessWebhookData $webhook): void
    {
        Log::info('Handled Paystack charge success webhook.', [
            'reference' => $webhook->reference,
            'amount' => $webhook->amount,
            'customer_code' => $webhook->customer?->customerCode,
        ]);
    }

    private function logChargeDisputeCreated(ChargeDisputeCreatedWebhookData $webhook): void
    {
        Log::info('Handled Paystack charge dispute created webhook.', [
            'dispute_id' => $webhook->disputeId,
            'transaction_reference' => $webhook->transactionReference,
            'status' => $webhook->dispute->status,
        ]);
    }

    private function logChargeDisputeReminded(ChargeDisputeRemindedWebhookData $webhook): void
    {
        Log::info('Handled Paystack charge dispute reminded webhook.', [
            'dispute_id' => $webhook->disputeId,
            'transaction_reference' => $webhook->transactionReference,
            'status' => $webhook->dispute->status,
        ]);
    }

    private function logChargeDisputeResolved(ChargeDisputeResolvedWebhookData $webhook): void
    {
        Log::info('Handled Paystack charge dispute resolved webhook.', [
            'dispute_id' => $webhook->disputeId,
            'transaction_reference' => $webhook->transactionReference,
            'status' => $webhook->dispute->status,
        ]);
    }

    private function logCustomerIdentificationSuccess(CustomerIdentificationSuccessWebhookData $webhook): void
    {
        Log::info('Handled Paystack customer identification success webhook.', [
            'customer_code' => $webhook->customerCode,
            'customer_id' => $webhook->customerId,
            'identification_type' => $webhook->identification->type,
            'identification_value' => $webhook->identification->value,
        ]);
    }

    private function logCustomerIdentificationFailed(CustomerIdentificationFailedWebhookData $webhook): void
    {
        Log::info('Handled Paystack customer identification failed webhook.', [
            'customer_code' => $webhook->customerCode,
            'customer_id' => $webhook->customerId,
            'identification_type' => $webhook->identification->type,
            'identification_value' => $webhook->identification->value,
        ]);
    }

    private function logDedicatedAccountAssignSuccess(DedicatedAccountAssignSuccessWebhookData $webhook): void
    {
        Log::info('Handled Paystack dedicated account assign success webhook.', [
            'account_number' => $webhook->dedicatedAccount?->accountNumber,
            'bank' => $webhook->dedicatedAccount?->bank,
            'customer_code' => $webhook->customer->customerCode,
        ]);
    }

    private function logDedicatedAccountAssignFailed(DedicatedAccountAssignFailedWebhookData $webhook): void
    {
        Log::info('Handled Paystack dedicated account assign failed webhook.', [
            'customer_code' => $webhook->customer->customerCode,
            'has_dedicated_account' => $webhook->dedicatedAccount !== null,
        ]);
    }

    private function logInvoiceCreated(InvoiceCreatedWebhookData $webhook): void
    {
        Log::info('Handled Paystack invoice created webhook.', [
            'invoice_code' => $webhook->invoiceCode,
            'subscription_code' => $webhook->subscriptionCode,
            'customer_code' => $webhook->customerCode,
            'paid' => $webhook->paid,
        ]);
    }

    private function logInvoiceUpdated(InvoiceUpdatedWebhookData $webhook): void
    {
        Log::info('Handled Paystack invoice updated webhook.', [
            'invoice_code' => $webhook->invoiceCode,
            'subscription_code' => $webhook->subscriptionCode,
            'customer_code' => $webhook->customerCode,
            'paid' => $webhook->paid,
        ]);
    }

    private function logInvoicePaymentFailed(InvoicePaymentFailedWebhookData $webhook): void
    {
        Log::info('Handled Paystack invoice payment failed webhook.', [
            'invoice_code' => $webhook->invoiceCode,
            'subscription_code' => $webhook->subscriptionCode,
            'customer_code' => $webhook->customerCode,
            'paid' => $webhook->paid,
        ]);
    }

    private function logPaymentRequestPending(PaymentRequestPendingWebhookData $webhook): void
    {
        Log::info('Handled Paystack payment request pending webhook.', [
            'request_code' => $webhook->requestCode,
            'amount' => $webhook->amount,
        ]);
    }

    private function logPaymentRequestSuccess(PaymentRequestSuccessWebhookData $webhook): void
    {
        Log::info('Handled Paystack payment request success webhook.', [
            'request_code' => $webhook->requestCode,
            'amount' => $webhook->amount,
        ]);
    }

    private function logRefundPending(RefundPendingWebhookData $webhook): void
    {
        Log::info('Handled Paystack refund pending webhook.', [
            'refund_reference' => $webhook->refundReference,
            'transaction_reference' => $webhook->transactionReference,
            'amount' => $webhook->amount,
        ]);
    }

    private function logRefundProcessing(RefundProcessingWebhookData $webhook): void
    {
        Log::info('Handled Paystack refund processing webhook.', [
            'refund_reference' => $webhook->refundReference,
            'transaction_reference' => $webhook->transactionReference,
            'amount' => $webhook->amount,
        ]);
    }

    private function logRefundProcessed(RefundProcessedWebhookData $webhook): void
    {
        Log::info('Handled Paystack refund processed webhook.', [
            'refund_reference' => $webhook->refundReference,
            'transaction_reference' => $webhook->transactionReference,
            'amount' => $webhook->amount,
        ]);
    }

    private function logRefundFailed(RefundFailedWebhookData $webhook): void
    {
        Log::info('Handled Paystack refund failed webhook.', [
            'refund_reference' => $webhook->refundReference,
            'transaction_reference' => $webhook->transactionReference,
            'amount' => $webhook->amount,
        ]);
    }

    private function logSubscriptionCreated(SubscriptionCreatedWebhookData $webhook): void
    {
        Log::info('Handled Paystack subscription created webhook.', [
            'subscription_code' => $webhook->subscriptionCode,
            'customer_code' => $webhook->customer?->customerCode,
            'next_payment_date' => $webhook->nextPaymentDate?->toAtomString(),
        ]);
    }

    private function logSubscriptionNotRenewing(SubscriptionNotRenewingWebhookData $webhook): void
    {
        Log::info('Handled Paystack subscription not renew webhook.', [
            'subscription_code' => $webhook->subscriptionCode,
            'status' => $webhook->status->value,
        ]);
    }

    private function logSubscriptionDisabled(SubscriptionDisabledWebhookData $webhook): void
    {
        Log::info('Handled Paystack subscription disabled webhook.', [
            'subscription_code' => $webhook->subscriptionCode,
            'status' => $webhook->status->value,
        ]);
    }

    private function logSubscriptionExpiringCards(SubscriptionExpiringCardsWebhookData $webhook): void
    {
        Log::info('Handled Paystack subscription expiring cards webhook.', [
            'total' => count($webhook->cards),
        ]);
    }

    private function logTransferSuccess(TransferSuccessWebhookData $webhook): void
    {
        Log::info('Handled Paystack transfer success webhook.', [
            'transfer_reference' => $webhook->reference,
            'transfer_code' => $webhook->transferCode,
            'amount' => $webhook->amount,
            'recipient_code' => $webhook->recipient?->recipientCode,
        ]);
    }

    private function logTransferFailed(TransferFailedWebhookData $webhook): void
    {
        Log::info('Handled Paystack transfer failed webhook.', [
            'transfer_reference' => $webhook->reference,
            'transfer_code' => $webhook->transferCode,
            'amount' => $webhook->amount,
            'recipient_code' => $webhook->recipient?->recipientCode,
        ]);
    }

    private function logTransferReversed(TransferReversedWebhookData $webhook): void
    {
        Log::info('Handled Paystack transfer reversed webhook.', [
            'transfer_reference' => $webhook->reference,
            'transfer_code' => $webhook->transferCode,
            'amount' => $webhook->amount,
            'recipient_code' => $webhook->recipient?->recipientCode,
        ]);
    }

    private function logUnhandledWebhook(PaystackWebhookEventData $webhook): void
    {
        Log::info('Received unhandled Paystack webhook event.', [
            'event' => $webhook->event,
        ]);
    }

    /**
     * Keep the workbench demo page in sync with the latest webhook payload.
     */
    private function cacheLatestWebhook(PaystackWebhookEventData $webhook): void
    {
        Cache::put('paystack:last-webhook-event', [
            'event' => $webhook->event,
            'resource_type' => $webhook->resourceType,
            'id' => $webhook->id,
            'occurred_at' => $webhook->occurredAt,
            'data' => $webhook->data,
        ]);
    }
}

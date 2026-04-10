<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Maxiviper117\Paystack\Data\Output\Webhook\PaystackWebhookEventData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\ChargeSuccessWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\InvoiceCreatedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\InvoicePaymentFailedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\InvoiceUpdatedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionCreatedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionDisabledWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionNotRenewingWebhookData;
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
            ->onInvoiceCreated($this->logInvoiceCreated(...))
            ->onInvoiceUpdated($this->logInvoiceUpdated(...))
            ->onInvoicePaymentFailed($this->logInvoicePaymentFailed(...))
            ->onSubscriptionCreated($this->logSubscriptionCreated(...))
            ->onSubscriptionNotRenewing($this->logSubscriptionNotRenewing(...))
            ->onSubscriptionDisabled($this->logSubscriptionDisabled(...))
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

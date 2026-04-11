<?php

namespace Maxiviper117\Paystack\Listeners;

use Closure;
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
use Maxiviper117\Paystack\Enums\Webhook\PaystackWebhookEvent;
use Maxiviper117\Paystack\Events\PaystackWebhookReceived;

class PaystackWebhookHandler
{
    private ?Closure $chargeSuccessCallback = null;

    private ?Closure $chargeDisputeCreatedCallback = null;

    private ?Closure $chargeDisputeRemindedCallback = null;

    private ?Closure $chargeDisputeResolvedCallback = null;

    private ?Closure $customerIdentificationSucceededCallback = null;

    private ?Closure $customerIdentificationFailedCallback = null;

    private ?Closure $dedicatedAccountAssignedCallback = null;

    private ?Closure $dedicatedAccountAssignFailedCallback = null;

    private ?Closure $invoiceCreatedCallback = null;

    private ?Closure $invoiceUpdatedCallback = null;

    private ?Closure $invoicePaymentFailedCallback = null;

    private ?Closure $paymentRequestPendingCallback = null;

    private ?Closure $paymentRequestSuccessCallback = null;

    private ?Closure $refundPendingCallback = null;

    private ?Closure $refundProcessingCallback = null;

    private ?Closure $refundProcessedCallback = null;

    private ?Closure $refundFailedCallback = null;

    private ?Closure $subscriptionCreatedCallback = null;

    private ?Closure $subscriptionNotRenewingCallback = null;

    private ?Closure $subscriptionDisabledCallback = null;

    private ?Closure $subscriptionExpiringCardsCallback = null;

    private ?Closure $transferSuccessCallback = null;

    private ?Closure $transferFailedCallback = null;

    private ?Closure $transferReversedCallback = null;

    private ?Closure $unhandledCallback = null;

    /**
     * Register a callback for `charge.success` webhook events.
     *
     * @param  callable(ChargeSuccessWebhookData): void  $callback
     */
    public function onChargeSuccess(callable $callback): static
    {
        $this->chargeSuccessCallback = Closure::fromCallable($callback);

        return $this;
    }

    /**
     * Register a callback for `charge.dispute.create` webhook events.
     *
     * @param  callable(ChargeDisputeCreatedWebhookData): void  $callback
     */
    public function onChargeDisputeCreated(callable $callback): static
    {
        $this->chargeDisputeCreatedCallback = Closure::fromCallable($callback);

        return $this;
    }

    /**
     * Register a callback for `charge.dispute.remind` webhook events.
     *
     * @param  callable(ChargeDisputeRemindedWebhookData): void  $callback
     */
    public function onChargeDisputeReminded(callable $callback): static
    {
        $this->chargeDisputeRemindedCallback = Closure::fromCallable($callback);

        return $this;
    }

    /**
     * Register a callback for `charge.dispute.resolve` webhook events.
     *
     * @param  callable(ChargeDisputeResolvedWebhookData): void  $callback
     */
    public function onChargeDisputeResolved(callable $callback): static
    {
        $this->chargeDisputeResolvedCallback = Closure::fromCallable($callback);

        return $this;
    }

    /**
     * Register a callback for `customeridentification.success` webhook events.
     *
     * @param  callable(CustomerIdentificationSuccessWebhookData): void  $callback
     */
    public function onCustomerIdentificationSucceeded(callable $callback): static
    {
        $this->customerIdentificationSucceededCallback = Closure::fromCallable($callback);

        return $this;
    }

    /**
     * Register a callback for `customeridentification.failed` webhook events.
     *
     * @param  callable(CustomerIdentificationFailedWebhookData): void  $callback
     */
    public function onCustomerIdentificationFailed(callable $callback): static
    {
        $this->customerIdentificationFailedCallback = Closure::fromCallable($callback);

        return $this;
    }

    /**
     * Register a callback for `dedicatedaccount.assign.success` webhook events.
     *
     * @param  callable(DedicatedAccountAssignSuccessWebhookData): void  $callback
     */
    public function onDedicatedAccountAssigned(callable $callback): static
    {
        $this->dedicatedAccountAssignedCallback = Closure::fromCallable($callback);

        return $this;
    }

    /**
     * Register a callback for `dedicatedaccount.assign.failed` webhook events.
     *
     * @param  callable(DedicatedAccountAssignFailedWebhookData): void  $callback
     */
    public function onDedicatedAccountAssignFailed(callable $callback): static
    {
        $this->dedicatedAccountAssignFailedCallback = Closure::fromCallable($callback);

        return $this;
    }

    /**
     * Register a callback for `invoice.create` webhook events.
     *
     * @param  callable(InvoiceCreatedWebhookData): void  $callback
     */
    public function onInvoiceCreated(callable $callback): static
    {
        $this->invoiceCreatedCallback = Closure::fromCallable($callback);

        return $this;
    }

    /**
     * Register a callback for `invoice.update` webhook events.
     *
     * @param  callable(InvoiceUpdatedWebhookData): void  $callback
     */
    public function onInvoiceUpdated(callable $callback): static
    {
        $this->invoiceUpdatedCallback = Closure::fromCallable($callback);

        return $this;
    }

    /**
     * Register a callback for `invoice.payment_failed` webhook events.
     *
     * @param  callable(InvoicePaymentFailedWebhookData): void  $callback
     */
    public function onInvoicePaymentFailed(callable $callback): static
    {
        $this->invoicePaymentFailedCallback = Closure::fromCallable($callback);

        return $this;
    }

    /**
     * Register a callback for `paymentrequest.pending` webhook events.
     *
     * @param  callable(PaymentRequestPendingWebhookData): void  $callback
     */
    public function onPaymentRequestPending(callable $callback): static
    {
        $this->paymentRequestPendingCallback = Closure::fromCallable($callback);

        return $this;
    }

    /**
     * Register a callback for `paymentrequest.success` webhook events.
     *
     * @param  callable(PaymentRequestSuccessWebhookData): void  $callback
     */
    public function onPaymentRequestSuccess(callable $callback): static
    {
        $this->paymentRequestSuccessCallback = Closure::fromCallable($callback);

        return $this;
    }

    /**
     * Register a callback for `refund.pending` webhook events.
     *
     * @param  callable(RefundPendingWebhookData): void  $callback
     */
    public function onRefundPending(callable $callback): static
    {
        $this->refundPendingCallback = Closure::fromCallable($callback);

        return $this;
    }

    /**
     * Register a callback for `refund.processing` webhook events.
     *
     * @param  callable(RefundProcessingWebhookData): void  $callback
     */
    public function onRefundProcessing(callable $callback): static
    {
        $this->refundProcessingCallback = Closure::fromCallable($callback);

        return $this;
    }

    /**
     * Register a callback for `refund.processed` webhook events.
     *
     * @param  callable(RefundProcessedWebhookData): void  $callback
     */
    public function onRefundProcessed(callable $callback): static
    {
        $this->refundProcessedCallback = Closure::fromCallable($callback);

        return $this;
    }

    /**
     * Register a callback for `refund.failed` webhook events.
     *
     * @param  callable(RefundFailedWebhookData): void  $callback
     */
    public function onRefundFailed(callable $callback): static
    {
        $this->refundFailedCallback = Closure::fromCallable($callback);

        return $this;
    }

    /**
     * Register a callback for `subscription.create` webhook events.
     *
     * @param  callable(SubscriptionCreatedWebhookData): void  $callback
     */
    public function onSubscriptionCreated(callable $callback): static
    {
        $this->subscriptionCreatedCallback = Closure::fromCallable($callback);

        return $this;
    }

    /**
     * Register a callback for `subscription.not_renew` webhook events.
     *
     * @param  callable(SubscriptionNotRenewingWebhookData): void  $callback
     */
    public function onSubscriptionNotRenewing(callable $callback): static
    {
        $this->subscriptionNotRenewingCallback = Closure::fromCallable($callback);

        return $this;
    }

    /**
     * Register a callback for `subscription.disable` webhook events.
     *
     * @param  callable(SubscriptionDisabledWebhookData): void  $callback
     */
    public function onSubscriptionDisabled(callable $callback): static
    {
        $this->subscriptionDisabledCallback = Closure::fromCallable($callback);

        return $this;
    }

    /**
     * Register a callback for `subscription.expiring_cards` webhook events.
     *
     * @param  callable(SubscriptionExpiringCardsWebhookData): void  $callback
     */
    public function onSubscriptionExpiringCards(callable $callback): static
    {
        $this->subscriptionExpiringCardsCallback = Closure::fromCallable($callback);

        return $this;
    }

    /**
     * Register a callback for `transfer.success` webhook events.
     *
     * @param  callable(TransferSuccessWebhookData): void  $callback
     */
    public function onTransferSuccess(callable $callback): static
    {
        $this->transferSuccessCallback = Closure::fromCallable($callback);

        return $this;
    }

    /**
     * Register a callback for `transfer.failed` webhook events.
     *
     * @param  callable(TransferFailedWebhookData): void  $callback
     */
    public function onTransferFailed(callable $callback): static
    {
        $this->transferFailedCallback = Closure::fromCallable($callback);

        return $this;
    }

    /**
     * Register a callback for `transfer.reversed` webhook events.
     *
     * @param  callable(TransferReversedWebhookData): void  $callback
     */
    public function onTransferReversed(callable $callback): static
    {
        $this->transferReversedCallback = Closure::fromCallable($callback);

        return $this;
    }

    /**
     * Register a fallback callback for unsupported webhook events.
     *
     * @param  callable(PaystackWebhookEventData): void  $callback
     */
    public function onUnhandled(callable $callback): static
    {
        $this->unhandledCallback = Closure::fromCallable($callback);

        return $this;
    }

    public function handle(PaystackWebhookReceived $event): void
    {
        $webhook = $event->event;
        $eventType = PaystackWebhookEvent::tryFrom($webhook->event);

        match ($eventType) {
            PaystackWebhookEvent::ChargeSuccess => $this->dispatchTyped($webhook, ChargeSuccessWebhookData::class, $this->chargeSuccessCallback),
            PaystackWebhookEvent::ChargeDisputeCreate => $this->dispatchTyped($webhook, ChargeDisputeCreatedWebhookData::class, $this->chargeDisputeCreatedCallback),
            PaystackWebhookEvent::ChargeDisputeRemind => $this->dispatchTyped($webhook, ChargeDisputeRemindedWebhookData::class, $this->chargeDisputeRemindedCallback),
            PaystackWebhookEvent::ChargeDisputeResolve => $this->dispatchTyped($webhook, ChargeDisputeResolvedWebhookData::class, $this->chargeDisputeResolvedCallback),
            PaystackWebhookEvent::CustomerIdentificationSuccess => $this->dispatchTyped($webhook, CustomerIdentificationSuccessWebhookData::class, $this->customerIdentificationSucceededCallback),
            PaystackWebhookEvent::CustomerIdentificationFailed => $this->dispatchTyped($webhook, CustomerIdentificationFailedWebhookData::class, $this->customerIdentificationFailedCallback),
            PaystackWebhookEvent::DedicatedAccountAssignSuccess => $this->dispatchTyped($webhook, DedicatedAccountAssignSuccessWebhookData::class, $this->dedicatedAccountAssignedCallback),
            PaystackWebhookEvent::DedicatedAccountAssignFailed => $this->dispatchTyped($webhook, DedicatedAccountAssignFailedWebhookData::class, $this->dedicatedAccountAssignFailedCallback),
            PaystackWebhookEvent::InvoiceCreate => $this->dispatchTyped($webhook, InvoiceCreatedWebhookData::class, $this->invoiceCreatedCallback),
            PaystackWebhookEvent::InvoiceUpdate => $this->dispatchTyped($webhook, InvoiceUpdatedWebhookData::class, $this->invoiceUpdatedCallback),
            PaystackWebhookEvent::InvoicePaymentFailed => $this->dispatchTyped($webhook, InvoicePaymentFailedWebhookData::class, $this->invoicePaymentFailedCallback),
            PaystackWebhookEvent::PaymentRequestPending => $this->dispatchTyped($webhook, PaymentRequestPendingWebhookData::class, $this->paymentRequestPendingCallback),
            PaystackWebhookEvent::PaymentRequestSuccess => $this->dispatchTyped($webhook, PaymentRequestSuccessWebhookData::class, $this->paymentRequestSuccessCallback),
            PaystackWebhookEvent::RefundPending => $this->dispatchTyped($webhook, RefundPendingWebhookData::class, $this->refundPendingCallback),
            PaystackWebhookEvent::RefundProcessing => $this->dispatchTyped($webhook, RefundProcessingWebhookData::class, $this->refundProcessingCallback),
            PaystackWebhookEvent::RefundProcessed => $this->dispatchTyped($webhook, RefundProcessedWebhookData::class, $this->refundProcessedCallback),
            PaystackWebhookEvent::RefundFailed => $this->dispatchTyped($webhook, RefundFailedWebhookData::class, $this->refundFailedCallback),
            PaystackWebhookEvent::SubscriptionCreate => $this->dispatchTyped($webhook, SubscriptionCreatedWebhookData::class, $this->subscriptionCreatedCallback),
            PaystackWebhookEvent::SubscriptionNotRenew => $this->dispatchTyped($webhook, SubscriptionNotRenewingWebhookData::class, $this->subscriptionNotRenewingCallback),
            PaystackWebhookEvent::SubscriptionDisable => $this->dispatchTyped($webhook, SubscriptionDisabledWebhookData::class, $this->subscriptionDisabledCallback),
            PaystackWebhookEvent::SubscriptionExpiringCards => $this->dispatchTyped($webhook, SubscriptionExpiringCardsWebhookData::class, $this->subscriptionExpiringCardsCallback),
            PaystackWebhookEvent::TransferSuccess => $this->dispatchTyped($webhook, TransferSuccessWebhookData::class, $this->transferSuccessCallback),
            PaystackWebhookEvent::TransferFailed => $this->dispatchTyped($webhook, TransferFailedWebhookData::class, $this->transferFailedCallback),
            PaystackWebhookEvent::TransferReversed => $this->dispatchTyped($webhook, TransferReversedWebhookData::class, $this->transferReversedCallback),
            default => $this->dispatchUnhandled($webhook),
        };
    }

    /**
     * @param  class-string  $expectedClass
     */
    private function dispatchTyped(PaystackWebhookEventData $webhook, string $expectedClass, ?Closure $callback): void
    {
        $typed = $webhook->typedData();

        if ($typed instanceof $expectedClass) {
            $this->invokeCallback($callback, $typed);
        }
    }

    private function dispatchUnhandled(PaystackWebhookEventData $webhook): void
    {
        $this->invokeCallback($this->unhandledCallback, $webhook);
    }

    private function invokeCallback(?Closure $callback, mixed ...$arguments): void
    {
        if (! $callback instanceof Closure) {
            return;
        }

        $callback(...$arguments);
    }
}

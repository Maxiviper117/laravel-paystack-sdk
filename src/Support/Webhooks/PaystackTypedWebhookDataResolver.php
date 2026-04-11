<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Support\Webhooks;

use Maxiviper117\Paystack\Data\Output\Webhook\PaystackWebhookEventData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\PaystackTypedWebhookData;
use Maxiviper117\Paystack\Enums\Webhook\PaystackWebhookEvent;
use Maxiviper117\Paystack\Support\Webhooks\Mappers\ChargeDisputeWebhookDataMapper;
use Maxiviper117\Paystack\Support\Webhooks\Mappers\ChargeSuccessWebhookDataMapper;
use Maxiviper117\Paystack\Support\Webhooks\Mappers\CustomerIdentificationWebhookDataMapper;
use Maxiviper117\Paystack\Support\Webhooks\Mappers\DedicatedAccountWebhookDataMapper;
use Maxiviper117\Paystack\Support\Webhooks\Mappers\InvoiceWebhookDataMapper;
use Maxiviper117\Paystack\Support\Webhooks\Mappers\PaymentRequestWebhookDataMapper;
use Maxiviper117\Paystack\Support\Webhooks\Mappers\RefundWebhookDataMapper;
use Maxiviper117\Paystack\Support\Webhooks\Mappers\SubscriptionExpiringCardsWebhookDataMapper;
use Maxiviper117\Paystack\Support\Webhooks\Mappers\SubscriptionWebhookDataMapper;
use Maxiviper117\Paystack\Support\Webhooks\Mappers\TransferWebhookDataMapper;

class PaystackTypedWebhookDataResolver
{
    /**
     * @var list<string>
     */
    private const array SUPPORTED_EVENTS = [
        PaystackWebhookEvent::ChargeSuccess->value,
        PaystackWebhookEvent::ChargeDisputeCreate->value,
        PaystackWebhookEvent::ChargeDisputeRemind->value,
        PaystackWebhookEvent::ChargeDisputeResolve->value,
        PaystackWebhookEvent::CustomerIdentificationSuccess->value,
        PaystackWebhookEvent::CustomerIdentificationFailed->value,
        PaystackWebhookEvent::DedicatedAccountAssignSuccess->value,
        PaystackWebhookEvent::DedicatedAccountAssignFailed->value,
        PaystackWebhookEvent::InvoiceCreate->value,
        PaystackWebhookEvent::InvoiceUpdate->value,
        PaystackWebhookEvent::InvoicePaymentFailed->value,
        PaystackWebhookEvent::PaymentRequestPending->value,
        PaystackWebhookEvent::PaymentRequestSuccess->value,
        PaystackWebhookEvent::RefundPending->value,
        PaystackWebhookEvent::RefundProcessing->value,
        PaystackWebhookEvent::RefundProcessed->value,
        PaystackWebhookEvent::RefundFailed->value,
        PaystackWebhookEvent::SubscriptionCreate->value,
        PaystackWebhookEvent::SubscriptionNotRenew->value,
        PaystackWebhookEvent::SubscriptionDisable->value,
        PaystackWebhookEvent::SubscriptionExpiringCards->value,
        PaystackWebhookEvent::TransferSuccess->value,
        PaystackWebhookEvent::TransferFailed->value,
        PaystackWebhookEvent::TransferReversed->value,
    ];

    public static function supports(string $event): bool
    {
        return in_array($event, self::SUPPORTED_EVENTS, true);
    }

    public static function resolve(PaystackWebhookEventData $event): ?PaystackTypedWebhookData
    {
        return match (PaystackWebhookEvent::tryFrom($event->event)) {
            PaystackWebhookEvent::ChargeSuccess => ChargeSuccessWebhookDataMapper::map($event),
            PaystackWebhookEvent::ChargeDisputeCreate,
            PaystackWebhookEvent::ChargeDisputeRemind,
            PaystackWebhookEvent::ChargeDisputeResolve => ChargeDisputeWebhookDataMapper::map($event),
            PaystackWebhookEvent::CustomerIdentificationSuccess,
            PaystackWebhookEvent::CustomerIdentificationFailed => CustomerIdentificationWebhookDataMapper::map($event),
            PaystackWebhookEvent::DedicatedAccountAssignSuccess,
            PaystackWebhookEvent::DedicatedAccountAssignFailed => DedicatedAccountWebhookDataMapper::map($event),
            PaystackWebhookEvent::InvoiceCreate,
            PaystackWebhookEvent::InvoiceUpdate,
            PaystackWebhookEvent::InvoicePaymentFailed => InvoiceWebhookDataMapper::map($event),
            PaystackWebhookEvent::PaymentRequestPending,
            PaystackWebhookEvent::PaymentRequestSuccess => PaymentRequestWebhookDataMapper::map($event),
            PaystackWebhookEvent::RefundPending,
            PaystackWebhookEvent::RefundProcessing,
            PaystackWebhookEvent::RefundProcessed,
            PaystackWebhookEvent::RefundFailed => RefundWebhookDataMapper::map($event),
            PaystackWebhookEvent::SubscriptionCreate,
            PaystackWebhookEvent::SubscriptionNotRenew,
            PaystackWebhookEvent::SubscriptionDisable => SubscriptionWebhookDataMapper::map($event),
            PaystackWebhookEvent::SubscriptionExpiringCards => SubscriptionExpiringCardsWebhookDataMapper::map($event),
            PaystackWebhookEvent::TransferSuccess,
            PaystackWebhookEvent::TransferFailed,
            PaystackWebhookEvent::TransferReversed => TransferWebhookDataMapper::map($event),
            default => null,
        };
    }
}

<?php

namespace Maxiviper117\Paystack\Support\Webhooks;

use Maxiviper117\Paystack\Data\Output\Webhook\PaystackWebhookEventData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\PaystackTypedWebhookData;
use Maxiviper117\Paystack\Support\Webhooks\Mappers\ChargeSuccessWebhookDataMapper;
use Maxiviper117\Paystack\Support\Webhooks\Mappers\InvoiceWebhookDataMapper;
use Maxiviper117\Paystack\Support\Webhooks\Mappers\SubscriptionWebhookDataMapper;

class PaystackTypedWebhookDataResolver
{
    /**
     * @var list<string>
     */
    private const SUPPORTED_EVENTS = [
        'charge.success',
        'invoice.create',
        'invoice.update',
        'invoice.payment_failed',
        'subscription.create',
        'subscription.not_renew',
        'subscription.disable',
    ];

    public static function supports(string $event): bool
    {
        return in_array($event, self::SUPPORTED_EVENTS, true);
    }

    public static function resolve(PaystackWebhookEventData $event): ?PaystackTypedWebhookData
    {
        return match ($event->event) {
            'charge.success' => ChargeSuccessWebhookDataMapper::map($event),
            'invoice.create', 'invoice.update', 'invoice.payment_failed' => InvoiceWebhookDataMapper::map($event),
            'subscription.create', 'subscription.not_renew', 'subscription.disable' => SubscriptionWebhookDataMapper::map($event),
            default => null,
        };
    }
}

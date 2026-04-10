<?php

namespace Maxiviper117\Paystack\Support\Webhooks\Mappers;

use Maxiviper117\Paystack\Data\Output\Webhook\PaystackWebhookEventData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested\WebhookCustomerPayloadData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested\WebhookSubscriptionPayloadData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionExpiringCardData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionExpiringCardsWebhookData;
use Maxiviper117\Paystack\Exceptions\MalformedWebhookPayloadException;
use Maxiviper117\Paystack\Support\Payload;

class SubscriptionExpiringCardsWebhookDataMapper
{
    public static function map(PaystackWebhookEventData $event): SubscriptionExpiringCardsWebhookData
    {
        if (! array_is_list($event->data)) {
            throw new MalformedWebhookPayloadException(sprintf(
                'The Paystack webhook payload for [%s] is missing a list data payload.',
                $event->event,
            ));
        }

        $cards = [];

        foreach ($event->data as $item) {
            if (! is_array($item) || array_is_list($item)) {
                throw new MalformedWebhookPayloadException(sprintf(
                    'The Paystack webhook payload for [%s] contains an invalid expiring card entry.',
                    $event->event,
                ));
            }

            /** @var array<string, mixed> $item */
            $subscriptionPayload = Payload::nullableArray($item, 'subscription');
            $customerPayload = Payload::nullableArray($item, 'customer');

            if ($subscriptionPayload === null || array_is_list($subscriptionPayload)) {
                throw new MalformedWebhookPayloadException(sprintf(
                    'The Paystack webhook payload for [%s] is missing an object [subscription] field.',
                    $event->event,
                ));
            }

            if ($customerPayload === null || array_is_list($customerPayload)) {
                throw new MalformedWebhookPayloadException(sprintf(
                    'The Paystack webhook payload for [%s] is missing an object [customer] field.',
                    $event->event,
                ));
            }

            /** @var array<string, mixed> $subscriptionPayload */
            /** @var array<string, mixed> $customerPayload */
            $cards[] = new SubscriptionExpiringCardData(
                expiryDate: Payload::nullableString($item, 'expiry_date') ?? Payload::nullableString($item, 'expiryDate'),
                description: Payload::nullableString($item, 'description'),
                brand: Payload::nullableString($item, 'brand'),
                subscription: WebhookSubscriptionPayloadData::fromPayload($subscriptionPayload),
                customer: WebhookCustomerPayloadData::fromPayload($customerPayload),
                rawData: $item,
            );
        }

        return new SubscriptionExpiringCardsWebhookData(
            event: $event->event,
            cards: $cards,
            rawData: $event->data,
        );
    }
}

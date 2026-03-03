<?php

namespace Maxiviper117\Paystack\Support\Webhooks\Mappers;

use Maxiviper117\Paystack\Data\Output\Webhook\PaystackWebhookEventData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionCreatedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionDisabledWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionNotRenewingWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionWebhookData;
use Maxiviper117\Paystack\Data\Subscription\SubscriptionData;
use Maxiviper117\Paystack\Exceptions\MalformedWebhookPayloadException;
use Maxiviper117\Paystack\Support\Payload;

class SubscriptionWebhookDataMapper
{
    public static function map(PaystackWebhookEventData $event): SubscriptionWebhookData
    {
        self::requireNonEmptyString($event->data, 'subscription_code', $event->event);
        self::requireNonEmptyString($event->data, 'status', $event->event);

        $subscription = SubscriptionData::fromPayload($event->data);

        $subscriptionData = [
            'event' => $event->event,
            'subscriptionCode' => $subscription->subscriptionCode,
            'status' => Payload::string($event->data, 'status'),
            'domain' => Payload::nullableString($event->data, 'domain'),
            'emailToken' => $subscription->emailToken,
            'amount' => self::nullableIntLike($event->data, 'amount') ?? $subscription->plan?->amount,
            'nextPaymentDate' => $subscription->nextPaymentDate,
            'openInvoice' => $subscription->openInvoice,
            'customer' => $subscription->customer,
            'plan' => $subscription->plan,
            'subscription' => $subscription,
            'rawData' => $event->data,
        ];

        return match ($event->event) {
            'subscription.create' => new SubscriptionCreatedWebhookData(...$subscriptionData),
            'subscription.not_renew' => new SubscriptionNotRenewingWebhookData(...$subscriptionData),
            'subscription.disable' => new SubscriptionDisabledWebhookData(...$subscriptionData),
            default => throw new MalformedWebhookPayloadException(sprintf(
                'Unsupported subscription webhook event [%s] requested for typed mapping.',
                $event->event,
            )),
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private static function requireNonEmptyString(array $payload, string $key, string $event): void
    {
        $value = Payload::nullableString($payload, $key);

        if ($value === null || trim($value) === '') {
            throw new MalformedWebhookPayloadException(sprintf(
                'The Paystack webhook payload for [%s] is missing a non-empty [%s] field.',
                $event,
                $key,
            ));
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private static function nullableIntLike(array $payload, string $key): ?int
    {
        if (! array_key_exists($key, $payload)) {
            return null;
        }

        $value = $payload[$key];

        return is_numeric($value) ? (int) $value : null;
    }
}

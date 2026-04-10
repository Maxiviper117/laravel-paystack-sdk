<?php

namespace Maxiviper117\Paystack\Support\Webhooks\Mappers;

use Maxiviper117\Paystack\Data\Output\Webhook\PaystackWebhookEventData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionCreatedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionDisabledWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionNotRenewingWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionWebhookData;
use Maxiviper117\Paystack\Data\Subscription\SubscriptionData;
use Maxiviper117\Paystack\Data\Subscription\SubscriptionStatus;
use Maxiviper117\Paystack\Exceptions\MalformedWebhookPayloadException;
use Maxiviper117\Paystack\Support\Payload;

class SubscriptionWebhookDataMapper
{
    public static function map(PaystackWebhookEventData $event): SubscriptionWebhookData
    {
        $payload = self::requireObjectData($event);
        self::requireNonEmptyString($payload, 'subscription_code', $event->event);
        self::requireNonEmptyString($payload, 'status', $event->event);

        $subscription = SubscriptionData::fromPayload($payload);

        $status = self::subscriptionStatus($payload, $event->event);
        $domain = Payload::nullableString($payload, 'domain');
        $amount = self::nullableIntLike($payload, 'amount') ?? $subscription->plan?->amount;

        return match ($event->event) {
            'subscription.create' => new SubscriptionCreatedWebhookData(
                event: $event->event,
                subscriptionCode: $subscription->subscriptionCode,
                status: $status,
                domain: $domain,
                emailToken: $subscription->emailToken,
                amount: $amount,
                nextPaymentDate: $subscription->nextPaymentDate,
                openInvoice: $subscription->openInvoice,
                customer: $subscription->customer,
                plan: $subscription->plan,
                subscription: $subscription,
                rawData: $payload,
            ),
            'subscription.not_renew' => new SubscriptionNotRenewingWebhookData(
                event: $event->event,
                subscriptionCode: $subscription->subscriptionCode,
                status: $status,
                domain: $domain,
                emailToken: $subscription->emailToken,
                amount: $amount,
                nextPaymentDate: $subscription->nextPaymentDate,
                openInvoice: $subscription->openInvoice,
                customer: $subscription->customer,
                plan: $subscription->plan,
                subscription: $subscription,
                rawData: $payload,
            ),
            'subscription.disable' => new SubscriptionDisabledWebhookData(
                event: $event->event,
                subscriptionCode: $subscription->subscriptionCode,
                status: $status,
                domain: $domain,
                emailToken: $subscription->emailToken,
                amount: $amount,
                nextPaymentDate: $subscription->nextPaymentDate,
                openInvoice: $subscription->openInvoice,
                customer: $subscription->customer,
                plan: $subscription->plan,
                subscription: $subscription,
                rawData: $payload,
            ),
            default => throw new MalformedWebhookPayloadException(sprintf(
                'Unsupported subscription webhook event [%s] requested for typed mapping.',
                $event->event,
            )),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private static function requireObjectData(PaystackWebhookEventData $event): array
    {
        $data = $event->data;

        if (array_is_list($data)) {
            throw new MalformedWebhookPayloadException(sprintf(
                'The Paystack webhook payload for [%s] is missing an object data payload.',
                $event->event,
            ));
        }

        /** @var array<string, mixed> $data */
        return $data;
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

    /**
     * @param  array<string, mixed>  $payload
     */
    private static function subscriptionStatus(array $payload, string $event): SubscriptionStatus
    {
        $status = Payload::string($payload, 'status');
        $subscriptionStatus = SubscriptionStatus::tryFrom($status);

        if ($subscriptionStatus === null) {
            throw new MalformedWebhookPayloadException(sprintf(
                'The Paystack webhook payload for [%s] contains an unsupported [status] field.',
                $event,
            ));
        }

        return $subscriptionStatus;
    }
}

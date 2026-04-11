<?php

namespace Maxiviper117\Paystack\Support\Webhooks\Mappers;

use Maxiviper117\Paystack\Data\Output\Webhook\PaystackWebhookEventData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested\WebhookCustomerPayloadData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\RefundFailedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\RefundPendingWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\RefundProcessedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\RefundProcessingWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\RefundWebhookData;
use Maxiviper117\Paystack\Exceptions\MalformedWebhookPayloadException;
use Maxiviper117\Paystack\Support\Payload;

class RefundWebhookDataMapper
{
    public static function map(PaystackWebhookEventData $event): RefundWebhookData
    {
        $payload = self::requireObjectData($event);
        self::requireNonEmptyString($payload, 'status', $event->event);
        self::requireNonEmptyString($payload, 'transaction_reference', $event->event);
        self::requireIntLike($payload, 'amount', $event->event);

        $customerPayload = Payload::nullableArray($payload, 'customer');
        $customer = null;

        if ($customerPayload !== null && ! array_is_list($customerPayload)) {
            /** @var array<string, mixed> $customerPayload */
            $customer = WebhookCustomerPayloadData::fromPayload($customerPayload);
        }

        $data = [
            'event' => $event->event,
            'refundReference' => Payload::nullableString($payload, 'refund_reference') ?? Payload::nullableString($payload, 'refundReference'),
            'transactionReference' => Payload::string($payload, 'transaction_reference'),
            'status' => Payload::string($payload, 'status'),
            'amount' => Payload::int($payload, 'amount'),
            'currency' => Payload::nullableString($payload, 'currency'),
            'domain' => Payload::nullableString($payload, 'domain'),
            'processor' => Payload::nullableString($payload, 'processor'),
            'integration' => Payload::intOrStringOrNull($payload, 'integration'),
            'customer' => $customer,
            'rawData' => $payload,
        ];

        return match ($event->event) {
            'refund.pending' => new RefundPendingWebhookData(...$data),
            'refund.processing' => new RefundProcessingWebhookData(...$data),
            'refund.processed' => new RefundProcessedWebhookData(...$data),
            'refund.failed' => new RefundFailedWebhookData(...$data),
            default => throw new MalformedWebhookPayloadException(sprintf(
                'Unsupported refund webhook event [%s] requested for typed mapping.',
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
    private static function requireIntLike(array $payload, string $key, string $event): void
    {
        if (! array_key_exists($key, $payload) || ! is_numeric($payload[$key])) {
            throw new MalformedWebhookPayloadException(sprintf(
                'The Paystack webhook payload for [%s] is missing a numeric [%s] field.',
                $event,
                $key,
            ));
        }
    }
}

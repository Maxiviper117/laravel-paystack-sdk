<?php

namespace Maxiviper117\Paystack\Support\Webhooks\Mappers;

use Maxiviper117\Paystack\Data\Output\Webhook\PaystackWebhookEventData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested\TransferIntegrationPayloadData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested\TransferRecipientPayloadData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested\TransferSessionPayloadData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\TransferFailedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\TransferReversedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\TransferSuccessWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\TransferWebhookData;
use Maxiviper117\Paystack\Exceptions\MalformedWebhookPayloadException;
use Maxiviper117\Paystack\Support\Payload;
use Maxiviper117\Paystack\Support\PaystackDate;

class TransferWebhookDataMapper
{
    public static function map(PaystackWebhookEventData $event): TransferWebhookData
    {
        $payload = self::requireObjectData($event);
        self::requireNonEmptyString($payload, 'reference', $event->event);
        self::requireNonEmptyString($payload, 'status', $event->event);
        self::requireIntLike($payload, 'amount', $event->event);

        $integrationPayload = Payload::nullableArray($payload, 'integration');
        $recipientPayload = Payload::nullableArray($payload, 'recipient');
        $sessionPayload = Payload::nullableArray($payload, 'session');
        $integration = null;
        $recipient = null;
        $session = null;

        if ($integrationPayload !== null && ! array_is_list($integrationPayload)) {
            /** @var array<string, mixed> $integrationPayload */
            $integration = TransferIntegrationPayloadData::fromPayload($integrationPayload);
        }

        if ($recipientPayload !== null && ! array_is_list($recipientPayload)) {
            /** @var array<string, mixed> $recipientPayload */
            $recipient = TransferRecipientPayloadData::fromPayload($recipientPayload);
        }

        if ($sessionPayload !== null && ! array_is_list($sessionPayload)) {
            /** @var array<string, mixed> $sessionPayload */
            $session = TransferSessionPayloadData::fromPayload($sessionPayload);
        }

        $data = [
            'event' => $event->event,
            'id' => Payload::intOrStringOrNull($payload, 'id'),
            'reference' => Payload::string($payload, 'reference'),
            'status' => Payload::string($payload, 'status'),
            'transferCode' => Payload::nullableString($payload, 'transfer_code') ?? Payload::nullableString($payload, 'transferCode'),
            'amount' => Payload::int($payload, 'amount'),
            'currency' => Payload::nullableString($payload, 'currency'),
            'domain' => Payload::nullableString($payload, 'domain'),
            'reason' => Payload::nullableString($payload, 'reason'),
            'source' => Payload::nullableString($payload, 'source'),
            'transferredAt' => PaystackDate::nullable(
                Payload::nullableString($payload, 'transferred_at') ?? Payload::nullableString($payload, 'transferredAt')
            ),
            'createdAt' => PaystackDate::nullable(
                Payload::nullableString($payload, 'created_at') ?? Payload::nullableString($payload, 'createdAt')
            ),
            'updatedAt' => PaystackDate::nullable(
                Payload::nullableString($payload, 'updated_at') ?? Payload::nullableString($payload, 'updatedAt')
            ),
            'titanCode' => Payload::nullableString($payload, 'titan_code') ?? Payload::nullableString($payload, 'titanCode'),
            'failures' => Payload::nullableArray($payload, 'failures'),
            'sourceDetails' => Payload::nullableArray($payload, 'source_details') ?? Payload::nullableArray($payload, 'sourceDetails'),
            'integration' => $integration,
            'recipient' => $recipient,
            'session' => $session,
            'feeCharged' => array_key_exists('fee_charged', $payload) || array_key_exists('feeCharged', $payload)
                ? Payload::int($payload, array_key_exists('fee_charged', $payload) ? 'fee_charged' : 'feeCharged')
                : null,
            'gatewayResponse' => Payload::nullableString($payload, 'gateway_response') ?? Payload::nullableString($payload, 'gatewayResponse'),
            'rawData' => $payload,
        ];

        return match ($event->event) {
            'transfer.success' => new TransferSuccessWebhookData(...$data),
            'transfer.failed' => new TransferFailedWebhookData(...$data),
            'transfer.reversed' => new TransferReversedWebhookData(...$data),
            default => throw new MalformedWebhookPayloadException(sprintf(
                'Unsupported transfer webhook event [%s] requested for typed mapping.',
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

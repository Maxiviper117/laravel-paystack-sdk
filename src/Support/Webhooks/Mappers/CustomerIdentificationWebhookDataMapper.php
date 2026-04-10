<?php

namespace Maxiviper117\Paystack\Support\Webhooks\Mappers;

use Maxiviper117\Paystack\Data\Output\Webhook\PaystackWebhookEventData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\CustomerIdentificationDetailsData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\CustomerIdentificationFailedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\CustomerIdentificationSuccessWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\CustomerIdentificationWebhookData;
use Maxiviper117\Paystack\Exceptions\MalformedWebhookPayloadException;
use Maxiviper117\Paystack\Support\Payload;

class CustomerIdentificationWebhookDataMapper
{
    public static function map(PaystackWebhookEventData $event): CustomerIdentificationWebhookData
    {
        $payload = self::requireObjectData($event);
        self::requireNonEmptyString($payload, 'customer_code', $event->event);
        self::requireNonEmptyString($payload, 'email', $event->event);

        $identificationPayload = Payload::nullableArray($payload, 'identification');

        if ($identificationPayload === null || array_is_list($identificationPayload)) {
            throw new MalformedWebhookPayloadException(sprintf(
                'The Paystack webhook payload for [%s] is missing an object [identification] field.',
                $event->event,
            ));
        }

        /** @var array<string, mixed> $identificationPayload */
        $data = [
            'event' => $event->event,
            'customerId' => Payload::intOrStringOrNull($payload, 'customer_id') ?? Payload::intOrStringOrNull($payload, 'customerId'),
            'customerCode' => Payload::string($payload, 'customer_code'),
            'email' => Payload::string($payload, 'email'),
            'identification' => CustomerIdentificationDetailsData::fromPayload($identificationPayload),
            'reason' => Payload::nullableString($payload, 'reason'),
            'rawData' => $payload,
        ];

        return match ($event->event) {
            'customeridentification.success' => new CustomerIdentificationSuccessWebhookData(...$data),
            'customeridentification.failed' => new CustomerIdentificationFailedWebhookData(...$data),
            default => throw new MalformedWebhookPayloadException(sprintf(
                'Unsupported customer identification webhook event [%s] requested for typed mapping.',
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
}

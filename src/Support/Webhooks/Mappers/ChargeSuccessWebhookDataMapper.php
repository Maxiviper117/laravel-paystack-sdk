<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Support\Webhooks\Mappers;

use Maxiviper117\Paystack\Data\Customer\CustomerData;
use Maxiviper117\Paystack\Data\Output\Webhook\PaystackWebhookEventData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\ChargeSuccessWebhookData;
use Maxiviper117\Paystack\Data\Transaction\TransactionData;
use Maxiviper117\Paystack\Exceptions\MalformedWebhookPayloadException;
use Maxiviper117\Paystack\Support\Payload;

class ChargeSuccessWebhookDataMapper
{
    public static function map(PaystackWebhookEventData $event): ChargeSuccessWebhookData
    {
        $payload = self::requireObjectData($event);
        self::requireNonEmptyString($payload, 'reference', $event->event);
        self::requireNonEmptyString($payload, 'status', $event->event);
        self::requireIntLike($payload, 'amount', $event->event);

        $transaction = TransactionData::fromPayload($payload);

        return new ChargeSuccessWebhookData(
            event: $event->event,
            reference: $transaction->reference,
            status: Payload::string($payload, 'status'),
            amount: $transaction->amount,
            domain: Payload::nullableString($payload, 'domain'),
            currency: $transaction->currency,
            paidAt: $transaction->paidAt,
            channel: $transaction->channel,
            gatewayResponse: Payload::nullableString($payload, 'gateway_response'),
            customer: self::customer($payload),
            transaction: $transaction,
            rawData: $payload,
        );
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
    private static function customer(array $payload): ?CustomerData
    {
        $customerPayload = Payload::nullableArray($payload, 'customer');

        if ($customerPayload === null || array_is_list($customerPayload)) {
            return null;
        }

        /** @var array<string, mixed> $customerPayload */
        return CustomerData::fromPayload($customerPayload);
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

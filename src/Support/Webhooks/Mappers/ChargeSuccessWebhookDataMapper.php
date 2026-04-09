<?php

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
        self::requireNonEmptyString($event->data, 'reference', $event->event);
        self::requireNonEmptyString($event->data, 'status', $event->event);
        self::requireIntLike($event->data, 'amount', $event->event);

        $transaction = TransactionData::fromPayload($event->data);

        return new ChargeSuccessWebhookData(
            event: $event->event,
            reference: $transaction->reference,
            status: Payload::string($event->data, 'status'),
            amount: $transaction->amount,
            domain: Payload::nullableString($event->data, 'domain'),
            currency: $transaction->currency,
            paidAt: $transaction->paidAt,
            channel: $transaction->channel,
            gatewayResponse: Payload::nullableString($event->data, 'gateway_response'),
            customer: self::customer($event->data),
            transaction: $transaction,
            rawData: $event->data,
        );
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

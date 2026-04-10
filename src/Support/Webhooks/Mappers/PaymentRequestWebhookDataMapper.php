<?php

namespace Maxiviper117\Paystack\Support\Webhooks\Mappers;

use Maxiviper117\Paystack\Data\Output\Webhook\PaystackWebhookEventData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\PaymentRequestPendingWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\PaymentRequestSuccessWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\PaymentRequestWebhookData;
use Maxiviper117\Paystack\Exceptions\MalformedWebhookPayloadException;
use Maxiviper117\Paystack\Support\Payload;
use Maxiviper117\Paystack\Support\PaystackDate;

class PaymentRequestWebhookDataMapper
{
    public static function map(PaystackWebhookEventData $event): PaymentRequestWebhookData
    {
        $payload = self::requireObjectData($event);
        self::requireNonEmptyString($payload, 'request_code', $event->event);
        self::requireNonEmptyString($payload, 'status', $event->event);
        self::requireIntLike($payload, 'amount', $event->event);
        $paid = self::requireBoolLike($payload, 'paid', $event->event);

        $data = [
            'event' => $event->event,
            'id' => Payload::intOrStringOrNull($payload, 'id'),
            'requestCode' => Payload::string($payload, 'request_code'),
            'status' => Payload::string($payload, 'status'),
            'amount' => Payload::int($payload, 'amount'),
            'paid' => $paid,
            'currency' => Payload::nullableString($payload, 'currency'),
            'domain' => Payload::nullableString($payload, 'domain'),
            'dueDate' => PaystackDate::nullable(
                Payload::nullableString($payload, 'due_date') ?? Payload::nullableString($payload, 'dueDate')
            ),
            'paidAt' => PaystackDate::nullable(
                Payload::nullableString($payload, 'paid_at') ?? Payload::nullableString($payload, 'paidAt')
            ),
            'createdAt' => PaystackDate::nullable(
                Payload::nullableString($payload, 'created_at') ?? Payload::nullableString($payload, 'createdAt')
            ),
            'description' => Payload::nullableString($payload, 'description'),
            'invoiceNumber' => Payload::nullableString($payload, 'invoice_number') ?? Payload::nullableString($payload, 'invoiceNumber'),
            'offlineReference' => Payload::nullableString($payload, 'offline_reference') ?? Payload::nullableString($payload, 'offlineReference'),
            'customerId' => Payload::intOrStringOrNull($payload, 'customer'),
            'hasInvoice' => array_key_exists('has_invoice', $payload) || array_key_exists('hasInvoice', $payload)
                ? Payload::bool($payload, array_key_exists('has_invoice', $payload) ? 'has_invoice' : 'hasInvoice')
                : null,
            'pdfUrl' => Payload::nullableString($payload, 'pdf_url') ?? Payload::nullableString($payload, 'pdfUrl'),
            'metadata' => Payload::nullableArray($payload, 'metadata'),
            'lineItems' => Payload::nullableArray($payload, 'line_items') ?? Payload::nullableArray($payload, 'lineItems') ?? [],
            'tax' => Payload::nullableArray($payload, 'tax') ?? [],
            'notifications' => Payload::nullableArray($payload, 'notifications') ?? [],
            'rawData' => $payload,
        ];

        return match ($event->event) {
            'paymentrequest.pending' => new PaymentRequestPendingWebhookData(...$data),
            'paymentrequest.success' => new PaymentRequestSuccessWebhookData(...$data),
            default => throw new MalformedWebhookPayloadException(sprintf(
                'Unsupported payment request webhook event [%s] requested for typed mapping.',
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

    /**
     * @param  array<string, mixed>  $payload
     */
    private static function requireBoolLike(array $payload, string $key, string $event): bool
    {
        if (! array_key_exists($key, $payload)) {
            throw new MalformedWebhookPayloadException(sprintf(
                'The Paystack webhook payload for [%s] is missing a [%s] field.',
                $event,
                $key,
            ));
        }

        $value = $payload[$key];

        if (\is_bool($value)) {
            return $value;
        }

        if ($value === 1 || $value === '1') {
            return true;
        }

        if ($value === 0 || $value === '0') {
            return false;
        }

        throw new MalformedWebhookPayloadException(sprintf(
            'The Paystack webhook payload for [%s] contains an invalid boolean-like [%s] field.',
            $event,
            $key,
        ));
    }
}

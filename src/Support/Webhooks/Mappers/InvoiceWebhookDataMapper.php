<?php

namespace Maxiviper117\Paystack\Support\Webhooks\Mappers;

use Maxiviper117\Paystack\Data\Customer\CustomerData;
use Maxiviper117\Paystack\Data\Output\Webhook\PaystackWebhookEventData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\InvoiceCreatedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\InvoicePaymentFailedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\InvoiceUpdatedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\InvoiceWebhookData;
use Maxiviper117\Paystack\Data\Subscription\SubscriptionData;
use Maxiviper117\Paystack\Data\Transaction\TransactionData;
use Maxiviper117\Paystack\Exceptions\MalformedWebhookPayloadException;
use Maxiviper117\Paystack\Support\Payload;

class InvoiceWebhookDataMapper
{
    public static function map(PaystackWebhookEventData $event): InvoiceWebhookData
    {
        self::requireNonEmptyString($event->data, 'invoice_code', $event->event);
        self::requireNonEmptyString($event->data, 'status', $event->event);
        self::requireIntLike($event->data, 'amount', $event->event);
        $paid = self::requireBoolLike($event->data, 'paid', $event->event);

        $subscription = self::subscription($event->data);
        $customer = self::customer($event->data);
        $transaction = self::transaction($event->data);

        $invoiceData = [
            'event' => $event->event,
            'invoiceCode' => Payload::string($event->data, 'invoice_code'),
            'status' => Payload::string($event->data, 'status'),
            'paid' => $paid,
            'amount' => Payload::int($event->data, 'amount'),
            'domain' => Payload::nullableString($event->data, 'domain'),
            'periodStart' => Payload::nullableString($event->data, 'period_start'),
            'periodEnd' => Payload::nullableString($event->data, 'period_end'),
            'paidAt' => Payload::nullableString($event->data, 'paid_at'),
            'nextPaymentDate' => Payload::nullableString($event->data, 'next_payment_date'),
            'description' => Payload::nullableString($event->data, 'description'),
            'subscriptionCode' => $subscription?->subscriptionCode,
            'customerCode' => $customer?->customerCode,
            'authorizationCode' => self::authorizationCode($event->data),
            'customer' => $customer,
            'subscription' => $subscription,
            'transaction' => $transaction,
            'rawData' => $event->data,
        ];

        return match ($event->event) {
            'invoice.create' => new InvoiceCreatedWebhookData(...$invoiceData),
            'invoice.update' => new InvoiceUpdatedWebhookData(...$invoiceData),
            'invoice.payment_failed' => new InvoicePaymentFailedWebhookData(...$invoiceData),
            default => throw new MalformedWebhookPayloadException(sprintf(
                'Unsupported invoice webhook event [%s] requested for typed mapping.',
                $event->event,
            )),
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private static function transaction(array $payload): ?TransactionData
    {
        $transactionPayload = Payload::nullableArray($payload, 'transaction');

        if ($transactionPayload === null || array_is_list($transactionPayload)) {
            return null;
        }

        /** @var array<string, mixed> $transactionPayload */
        return TransactionData::fromPayload($transactionPayload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private static function subscription(array $payload): ?SubscriptionData
    {
        $subscriptionPayload = Payload::nullableArray($payload, 'subscription');

        if ($subscriptionPayload === null || array_is_list($subscriptionPayload)) {
            return null;
        }

        /** @var array<string, mixed> $subscriptionPayload */
        return SubscriptionData::fromPayload($subscriptionPayload);
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
    private static function authorizationCode(array $payload): ?string
    {
        $authorization = Payload::nullableArray($payload, 'authorization');

        if ($authorization === null || array_is_list($authorization)) {
            return null;
        }

        /** @var array<string, mixed> $authorization */
        return Payload::nullableString($authorization, 'authorization_code');
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

        if (is_bool($value)) {
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

<?php

namespace Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested;

use Maxiviper117\Paystack\Support\Payload;
use Spatie\LaravelData\Data;

/**
 * Plan fragment embedded inside webhook payloads.
 *
 * This keeps webhook composition separate from the API response DTO used by
 * plan endpoints while still exposing the plan fields needed by webhook flows.
 */
class WebhookPlanPayloadData extends Data
{
    /**
     * @param  array<string, mixed>  $rawData
     */
    public function __construct(
        public int|string|null $id,
        public ?string $name,
        public ?string $planCode,
        public ?int $amount,
        public ?string $interval,
        public ?string $description,
        public ?string $currency,
        public ?int $invoiceLimit,
        public ?bool $sendInvoices,
        public ?bool $sendSms,
        public array $rawData = [],
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            id: Payload::intOrStringOrNull($payload, 'id'),
            name: Payload::nullableString($payload, 'name'),
            planCode: Payload::nullableString($payload, 'plan_code') ?? Payload::nullableString($payload, 'planCode'),
            amount: array_key_exists('amount', $payload) ? Payload::int($payload, 'amount') : null,
            interval: Payload::nullableString($payload, 'interval'),
            description: Payload::nullableString($payload, 'description'),
            currency: Payload::nullableString($payload, 'currency'),
            invoiceLimit: array_key_exists('invoice_limit', $payload) || array_key_exists('invoiceLimit', $payload)
                ? Payload::int($payload, array_key_exists('invoice_limit', $payload) ? 'invoice_limit' : 'invoiceLimit')
                : null,
            sendInvoices: array_key_exists('send_invoices', $payload) || array_key_exists('sendInvoices', $payload)
                ? Payload::bool($payload, array_key_exists('send_invoices', $payload) ? 'send_invoices' : 'sendInvoices')
                : null,
            sendSms: array_key_exists('send_sms', $payload) || array_key_exists('sendSms', $payload)
                ? Payload::bool($payload, array_key_exists('send_sms', $payload) ? 'send_sms' : 'sendSms')
                : null,
            rawData: $payload,
        );
    }
}

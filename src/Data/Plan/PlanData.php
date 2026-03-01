<?php

namespace Maxiviper117\Paystack\Data\Plan;

use Maxiviper117\Paystack\Support\Payload;
use Spatie\LaravelData\Data;

class PlanData extends Data
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public int|string|null $id,
        public ?string $name,
        public string $planCode,
        public int $amount,
        public ?string $interval,
        public ?string $description,
        public ?string $currency,
        public ?int $invoiceLimit,
        public ?bool $sendInvoices,
        public ?bool $sendSms,
        public array $raw = [],
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            id: Payload::intOrStringOrNull($payload, 'id'),
            name: Payload::nullableString($payload, 'name'),
            planCode: Payload::nullableString($payload, 'plan_code') ?? Payload::string($payload, 'planCode'),
            amount: Payload::int($payload, 'amount'),
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
            raw: $payload,
        );
    }
}

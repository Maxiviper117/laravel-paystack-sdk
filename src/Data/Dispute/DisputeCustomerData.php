<?php

namespace Maxiviper117\Paystack\Data\Dispute;

use Maxiviper117\Paystack\Support\Payload;
use Spatie\LaravelData\Data;

class DisputeCustomerData extends Data
{
    /**
     * @param  array<mixed>|null  $metadata
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public int|string|null $id,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $email,
        public ?string $customerCode,
        public ?string $phone,
        public ?array $metadata,
        public ?string $riskAction,
        public ?string $internationalFormatPhone,
        public array $raw = [],
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            id: Payload::intOrStringOrNull($payload, 'id'),
            firstName: Payload::nullableString($payload, 'first_name') ?? Payload::nullableString($payload, 'firstName'),
            lastName: Payload::nullableString($payload, 'last_name') ?? Payload::nullableString($payload, 'lastName'),
            email: Payload::nullableString($payload, 'email'),
            customerCode: Payload::nullableString($payload, 'customer_code') ?? Payload::nullableString($payload, 'customerCode'),
            phone: Payload::nullableString($payload, 'phone'),
            metadata: Payload::nullableArray($payload, 'metadata'),
            riskAction: Payload::nullableString($payload, 'risk_action') ?? Payload::nullableString($payload, 'riskAction'),
            internationalFormatPhone: Payload::nullableString($payload, 'international_format_phone') ?? Payload::nullableString($payload, 'internationalFormatPhone'),
            raw: $payload,
        );
    }
}

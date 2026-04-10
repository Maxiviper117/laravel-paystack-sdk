<?php

namespace Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested;

use Maxiviper117\Paystack\Support\Payload;
use Spatie\LaravelData\Data;

/**
 * Customer fragment embedded inside webhook payloads.
 *
 * This is not a full customer API response DTO. It only models the fields
 * commonly present inside webhook event payloads so typed webhook DTOs can
 * compose it without mixing concerns with endpoint response shapes.
 */
class WebhookCustomerPayloadData extends Data
{
    /**
     * @param  array<mixed>|null  $metadata
     * @param  array<string, mixed>  $rawData
     */
    public function __construct(
        public int|string|null $id,
        public ?string $email,
        public ?string $customerCode,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $phone,
        public ?array $metadata,
        public ?string $riskAction,
        public ?string $internationalFormatPhone,
        public array $rawData = [],
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            id: Payload::intOrStringOrNull($payload, 'id'),
            email: Payload::nullableString($payload, 'email'),
            customerCode: Payload::nullableString($payload, 'customer_code') ?? Payload::nullableString($payload, 'customerCode'),
            firstName: Payload::nullableString($payload, 'first_name') ?? Payload::nullableString($payload, 'firstName'),
            lastName: Payload::nullableString($payload, 'last_name') ?? Payload::nullableString($payload, 'lastName'),
            phone: Payload::nullableString($payload, 'phone'),
            metadata: Payload::nullableArray($payload, 'metadata'),
            riskAction: Payload::nullableString($payload, 'risk_action') ?? Payload::nullableString($payload, 'riskAction'),
            internationalFormatPhone: Payload::nullableString($payload, 'international_format_phone') ?? Payload::nullableString($payload, 'internationalFormatPhone'),
            rawData: $payload,
        );
    }
}

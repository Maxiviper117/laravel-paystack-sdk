<?php

namespace Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested;

use Carbon\CarbonImmutable;
use Maxiviper117\Paystack\Support\Payload;
use Maxiviper117\Paystack\Support\PaystackDate;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

/**
 * Transfer recipient fragment embedded inside transfer webhook payloads.
 */
class TransferRecipientPayloadData extends Data
{
    /**
     * @param  array<mixed>|null  $metadata
     * @param  array<mixed>|null  $details
     * @param  array<string, mixed>  $rawData
     */
    public function __construct(
        public int|string|null $id,
        public ?string $recipientCode,
        public ?string $name,
        public ?string $type,
        public ?bool $active,
        public ?string $currency,
        public ?string $domain,
        public ?string $email,
        public ?string $description,
        public int|string|null $integration,
        public ?array $metadata,
        public ?bool $isDeleted,
        public ?array $details,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $createdAt,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $updatedAt,
        public array $rawData = [],
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            id: Payload::intOrStringOrNull($payload, 'id'),
            recipientCode: Payload::nullableString($payload, 'recipient_code') ?? Payload::nullableString($payload, 'recipientCode'),
            name: Payload::nullableString($payload, 'name'),
            type: Payload::nullableString($payload, 'type'),
            active: array_key_exists('active', $payload) ? Payload::bool($payload, 'active') : null,
            currency: Payload::nullableString($payload, 'currency'),
            domain: Payload::nullableString($payload, 'domain'),
            email: Payload::nullableString($payload, 'email'),
            description: Payload::nullableString($payload, 'description'),
            integration: Payload::intOrStringOrNull($payload, 'integration'),
            metadata: Payload::nullableArray($payload, 'metadata'),
            isDeleted: array_key_exists('is_deleted', $payload) || array_key_exists('isDeleted', $payload)
                ? Payload::bool($payload, array_key_exists('is_deleted', $payload) ? 'is_deleted' : 'isDeleted')
                : null,
            details: Payload::nullableArray($payload, 'details'),
            createdAt: PaystackDate::nullable(
                Payload::nullableString($payload, 'created_at') ?? Payload::nullableString($payload, 'createdAt')
            ),
            updatedAt: PaystackDate::nullable(
                Payload::nullableString($payload, 'updated_at') ?? Payload::nullableString($payload, 'updatedAt')
            ),
            rawData: $payload,
        );
    }
}

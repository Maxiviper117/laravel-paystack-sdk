<?php

namespace Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested;

use Carbon\CarbonImmutable;
use Maxiviper117\Paystack\Support\Payload;
use Maxiviper117\Paystack\Support\PaystackDate;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

/**
 * Message fragment inside a charge dispute webhook payload.
 */
class ChargeDisputeMessagePayloadData extends Data
{
    /**
     * @param  array<string, mixed>  $rawData
     */
    public function __construct(
        public int|string|null $id,
        public int|string|null $dispute,
        public ?string $sender,
        public ?string $body,
        public ?bool $isDeleted,
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
            dispute: Payload::intOrStringOrNull($payload, 'dispute'),
            sender: Payload::nullableString($payload, 'sender'),
            body: Payload::nullableString($payload, 'body'),
            isDeleted: array_key_exists('is_deleted', $payload) || array_key_exists('isDeleted', $payload)
                ? (Payload::bool($payload, array_key_exists('is_deleted', $payload) ? 'is_deleted' : 'isDeleted'))
                : null,
            createdAt: PaystackDate::nullable(
                Payload::nullableString($payload, 'createdAt') ?? Payload::nullableString($payload, 'created_at')
            ),
            updatedAt: PaystackDate::nullable(
                Payload::nullableString($payload, 'updatedAt') ?? Payload::nullableString($payload, 'updated_at')
            ),
            rawData: $payload,
        );
    }
}

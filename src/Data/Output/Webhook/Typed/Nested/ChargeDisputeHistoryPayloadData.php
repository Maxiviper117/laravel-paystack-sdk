<?php

namespace Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested;

use Carbon\CarbonImmutable;
use Maxiviper117\Paystack\Support\Payload;
use Maxiviper117\Paystack\Support\PaystackDate;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

/**
 * Historical dispute action fragment inside a charge dispute webhook payload.
 */
class ChargeDisputeHistoryPayloadData extends Data
{
    /**
     * @param  array<string, mixed>  $rawData
     */
    public function __construct(
        public int|string|null $id,
        public int|string|null $dispute,
        public ?string $status,
        public ?string $by,
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
            status: Payload::nullableString($payload, 'status'),
            by: Payload::nullableString($payload, 'by'),
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

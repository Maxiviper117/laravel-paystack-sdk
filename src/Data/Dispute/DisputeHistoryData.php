<?php

namespace Maxiviper117\Paystack\Data\Dispute;

use Carbon\CarbonImmutable;
use Maxiviper117\Paystack\Support\Payload;
use Maxiviper117\Paystack\Support\PaystackDate;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

class DisputeHistoryData extends Data
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public int|string|null $id,
        public int|string|null $dispute,
        public ?DisputeStatus $status,
        public ?string $by,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $createdAt,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $updatedAt,
        public array $raw = [],
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            id: Payload::intOrStringOrNull($payload, 'id'),
            dispute: Payload::intOrStringOrNull($payload, 'dispute'),
            status: self::disputeStatus($payload),
            by: Payload::nullableString($payload, 'by'),
            createdAt: PaystackDate::nullable(
                Payload::nullableString($payload, 'createdAt') ?? Payload::nullableString($payload, 'created_at')
            ),
            updatedAt: PaystackDate::nullable(
                Payload::nullableString($payload, 'updatedAt') ?? Payload::nullableString($payload, 'updated_at')
            ),
            raw: $payload,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private static function disputeStatus(array $payload): ?DisputeStatus
    {
        $status = Payload::nullableString($payload, 'status');

        if ($status === null || trim($status) === '') {
            return null;
        }

        return DisputeStatus::tryFrom($status);
    }
}

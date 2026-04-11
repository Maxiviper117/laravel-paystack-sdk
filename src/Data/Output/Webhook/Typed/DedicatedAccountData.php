<?php

namespace Maxiviper117\Paystack\Data\Output\Webhook\Typed;

use Carbon\CarbonImmutable;
use Maxiviper117\Paystack\Support\Payload;
use Maxiviper117\Paystack\Support\PaystackDate;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

class DedicatedAccountData extends Data
{
    /**
     * @param  array<mixed>|null  $metadata
     * @param  array<mixed>|null  $bank
     * @param  array<mixed>|null  $assignment
     * @param  array<string, mixed>  $rawData
     */
    public function __construct(
        public int|string|null $id,
        public ?string $accountName,
        public ?string $accountNumber,
        public bool $assigned,
        public ?string $currency,
        public ?array $metadata,
        public bool $active,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $createdAt,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $updatedAt,
        public ?array $bank,
        public ?array $assignment,
        public array $rawData = [],
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            id: Payload::intOrStringOrNull($payload, 'id'),
            accountName: Payload::nullableString($payload, 'account_name') ?? Payload::nullableString($payload, 'accountName'),
            accountNumber: Payload::nullableString($payload, 'account_number') ?? Payload::nullableString($payload, 'accountNumber'),
            assigned: Payload::bool($payload, 'assigned'),
            currency: Payload::nullableString($payload, 'currency'),
            metadata: Payload::nullableArray($payload, 'metadata'),
            active: Payload::bool($payload, 'active'),
            createdAt: PaystackDate::nullable(
                Payload::nullableString($payload, 'created_at') ?? Payload::nullableString($payload, 'createdAt')
            ),
            updatedAt: PaystackDate::nullable(
                Payload::nullableString($payload, 'updated_at') ?? Payload::nullableString($payload, 'updatedAt')
            ),
            bank: Payload::nullableArray($payload, 'bank'),
            assignment: Payload::nullableArray($payload, 'assignment'),
            rawData: $payload,
        );
    }
}

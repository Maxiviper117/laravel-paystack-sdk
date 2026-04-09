<?php

namespace Maxiviper117\Paystack\Data\Dispute;

use Carbon\CarbonImmutable;
use Maxiviper117\Paystack\Support\Payload;
use Maxiviper117\Paystack\Support\PaystackDate;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

class DisputeEvidenceData extends Data
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public ?string $customerEmail,
        public ?string $customerName,
        public ?string $customerPhone,
        public ?string $serviceDetails,
        public ?string $deliveryAddress,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $deliveryDate,
        public int|string|null $dispute,
        public int|string|null $id,
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
            customerEmail: Payload::nullableString($payload, 'customer_email') ?? Payload::nullableString($payload, 'customerEmail'),
            customerName: Payload::nullableString($payload, 'customer_name') ?? Payload::nullableString($payload, 'customerName'),
            customerPhone: Payload::nullableString($payload, 'customer_phone') ?? Payload::nullableString($payload, 'customerPhone'),
            serviceDetails: Payload::nullableString($payload, 'service_details') ?? Payload::nullableString($payload, 'serviceDetails'),
            deliveryAddress: Payload::nullableString($payload, 'delivery_address') ?? Payload::nullableString($payload, 'deliveryAddress'),
            deliveryDate: PaystackDate::nullable(
                Payload::nullableString($payload, 'delivery_date') ?? Payload::nullableString($payload, 'deliveryDate')
            ),
            dispute: Payload::intOrStringOrNull($payload, 'dispute'),
            id: Payload::intOrStringOrNull($payload, 'id'),
            createdAt: PaystackDate::nullable(
                Payload::nullableString($payload, 'createdAt') ?? Payload::nullableString($payload, 'created_at')
            ),
            updatedAt: PaystackDate::nullable(
                Payload::nullableString($payload, 'updatedAt') ?? Payload::nullableString($payload, 'updated_at')
            ),
            raw: $payload,
        );
    }
}

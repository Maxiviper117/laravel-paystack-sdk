<?php

namespace Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested;

use Maxiviper117\Paystack\Support\Payload;
use Spatie\LaravelData\Data;

/**
 * Transfer integration fragment embedded inside transfer webhook payloads.
 */
class TransferIntegrationPayloadData extends Data
{
    /**
     * @param  array<string, mixed>  $rawData
     */
    public function __construct(
        public int|string|null $id,
        public ?bool $isLive,
        public ?string $businessName,
        public ?string $logoPath,
        public array $rawData = [],
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            id: Payload::intOrStringOrNull($payload, 'id'),
            isLive: array_key_exists('is_live', $payload) || array_key_exists('isLive', $payload)
                ? Payload::bool($payload, array_key_exists('is_live', $payload) ? 'is_live' : 'isLive')
                : null,
            businessName: Payload::nullableString($payload, 'business_name') ?? Payload::nullableString($payload, 'businessName'),
            logoPath: Payload::nullableString($payload, 'logo_path') ?? Payload::nullableString($payload, 'logoPath'),
            rawData: $payload,
        );
    }
}

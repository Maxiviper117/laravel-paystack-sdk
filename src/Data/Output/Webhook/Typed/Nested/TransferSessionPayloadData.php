<?php

namespace Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested;

use Maxiviper117\Paystack\Support\Payload;
use Spatie\LaravelData\Data;

/**
 * Transfer session fragment embedded inside transfer webhook payloads.
 */
class TransferSessionPayloadData extends Data
{
    /**
     * @param  array<string, mixed>  $rawData
     */
    public function __construct(
        public ?string $provider,
        public ?string $id,
        public array $rawData = [],
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            provider: Payload::nullableString($payload, 'provider'),
            id: Payload::nullableString($payload, 'id'),
            rawData: $payload,
        );
    }
}

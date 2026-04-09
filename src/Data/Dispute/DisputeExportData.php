<?php

namespace Maxiviper117\Paystack\Data\Dispute;

use Maxiviper117\Paystack\Support\Payload;
use Spatie\LaravelData\Data;

class DisputeExportData extends Data
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public ?string $path,
        public ?string $expiresAt,
        public array $raw = [],
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            path: Payload::nullableString($payload, 'path'),
            expiresAt: Payload::nullableString($payload, 'expiresAt') ?? Payload::nullableString($payload, 'expires_at'),
            raw: $payload,
        );
    }
}

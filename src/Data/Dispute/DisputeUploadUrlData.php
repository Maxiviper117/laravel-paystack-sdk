<?php

namespace Maxiviper117\Paystack\Data\Dispute;

use Maxiviper117\Paystack\Support\Payload;
use Spatie\LaravelData\Data;

class DisputeUploadUrlData extends Data
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public ?string $signedUrl,
        public ?string $fileName,
        public array $raw = [],
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            signedUrl: Payload::nullableString($payload, 'signedUrl') ?? Payload::nullableString($payload, 'signed_url'),
            fileName: Payload::nullableString($payload, 'fileName') ?? Payload::nullableString($payload, 'file_name'),
            raw: $payload,
        );
    }
}

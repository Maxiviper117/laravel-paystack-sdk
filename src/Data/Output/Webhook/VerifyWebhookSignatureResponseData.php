<?php

namespace Maxiviper117\Paystack\Data\Output\Webhook;

use Maxiviper117\Paystack\Support\Payload;
use Spatie\LaravelData\Data;

class VerifyWebhookSignatureResponseData extends Data
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public string $event,
        public string $resourceType,
        public array $data,
        public array $raw,
        public ?string $occurredAt = null,
        public ?string $domain = null,
        public int|string|null $id = null,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $data
     */
    public static function fromPayload(array $payload, array $data, string $resourceType): self
    {
        return new self(
            event: Payload::string($payload, 'event'),
            resourceType: $resourceType,
            data: $data,
            raw: $payload,
            occurredAt: Payload::nullableString($data, 'paid_at')
                ?? Payload::nullableString($data, 'created_at')
                ?? Payload::nullableString($data, 'createdAt'),
            domain: Payload::nullableString($data, 'domain'),
            id: Payload::intOrStringOrNull($data, 'id'),
        );
    }
}

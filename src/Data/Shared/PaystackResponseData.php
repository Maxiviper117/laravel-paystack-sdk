<?php

namespace Maxiviper117\Paystack\Data\Shared;

use Maxiviper117\Paystack\Support\Payload;
use Spatie\LaravelData\Data;

class PaystackResponseData extends Data
{
    public function __construct(
        public bool $status,
        public string $message,
    ) {}

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            status: Payload::bool($payload, 'status'),
            message: Payload::string($payload, 'message'),
        );
    }
}

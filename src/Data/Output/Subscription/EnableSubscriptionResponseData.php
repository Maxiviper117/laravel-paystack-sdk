<?php

namespace Maxiviper117\Paystack\Data\Output\Subscription;

use Maxiviper117\Paystack\Support\Payload;
use Spatie\LaravelData\Data;

class EnableSubscriptionResponseData extends Data
{
    public function __construct(
        public bool $successful,
        public string $message,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            successful: Payload::bool($payload, 'status'),
            message: Payload::string($payload, 'message'),
        );
    }
}

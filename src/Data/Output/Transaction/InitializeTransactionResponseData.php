<?php

namespace Maxiviper117\Paystack\Data\Output\Transaction;

use Maxiviper117\Paystack\Support\Payload;
use Spatie\LaravelData\Data;

class InitializeTransactionResponseData extends Data
{
    public function __construct(
        public string $authorizationUrl,
        public string $accessCode,
        public string $reference,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            authorizationUrl: Payload::string($payload, 'authorization_url'),
            accessCode: Payload::string($payload, 'access_code'),
            reference: Payload::string($payload, 'reference'),
        );
    }
}

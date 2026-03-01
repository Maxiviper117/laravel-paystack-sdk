<?php

namespace Maxiviper117\Paystack\Data\Customer;

use Maxiviper117\Paystack\Support\Payload;
use Spatie\LaravelData\Data;

class CustomerData extends Data
{
    /**
     * @param  array<mixed>|null  $metadata
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public string $email,
        public ?string $customerCode,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $phone,
        public ?array $metadata,
        public array $raw = [],
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        $metadata = Payload::nullableArray($payload, 'metadata');

        return new self(
            email: Payload::string($payload, 'email'),
            customerCode: Payload::nullableString($payload, 'customer_code'),
            firstName: Payload::nullableString($payload, 'first_name'),
            lastName: Payload::nullableString($payload, 'last_name'),
            phone: Payload::nullableString($payload, 'phone'),
            metadata: $metadata,
            raw: $payload,
        );
    }
}

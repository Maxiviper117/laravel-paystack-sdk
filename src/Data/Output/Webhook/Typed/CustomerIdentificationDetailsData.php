<?php

namespace Maxiviper117\Paystack\Data\Output\Webhook\Typed;

use Maxiviper117\Paystack\Support\Payload;
use Spatie\LaravelData\Data;

class CustomerIdentificationDetailsData extends Data
{
    /**
     * @param  array<string, mixed>  $rawData
     */
    public function __construct(
        public ?string $country,
        public ?string $type,
        public ?string $value,
        public ?string $bvn,
        public ?string $accountNumber,
        public ?string $bankCode,
        public array $rawData = [],
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            country: Payload::nullableString($payload, 'country'),
            type: Payload::nullableString($payload, 'type'),
            value: Payload::nullableString($payload, 'value'),
            bvn: Payload::nullableString($payload, 'bvn'),
            accountNumber: Payload::nullableString($payload, 'account_number') ?? Payload::nullableString($payload, 'accountNumber'),
            bankCode: Payload::nullableString($payload, 'bank_code') ?? Payload::nullableString($payload, 'bankCode'),
            rawData: $payload,
        );
    }
}

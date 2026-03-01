<?php

namespace Maxiviper117\Paystack\Data\Transaction;

use Maxiviper117\Paystack\Data\Customer\CustomerData;
use Maxiviper117\Paystack\Support\Payload;
use Spatie\LaravelData\Data;

class TransactionData extends Data
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public int|string|null $id,
        public string $status,
        public string $reference,
        public int $amount,
        public ?string $currency,
        public ?CustomerData $customer,
        public ?string $paidAt,
        public ?string $channel,
        public array $raw = [],
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        $customerPayload = Payload::nullableArray($payload, 'customer');
        $customer = null;

        if ($customerPayload !== null && ! array_is_list($customerPayload)) {
            /** @var array<string, mixed> $customerPayload */
            $customer = CustomerData::fromPayload($customerPayload);
        }

        return new self(
            id: Payload::intOrStringOrNull($payload, 'id'),
            status: Payload::string($payload, 'status'),
            reference: Payload::string($payload, 'reference'),
            amount: Payload::int($payload, 'amount'),
            currency: Payload::nullableString($payload, 'currency'),
            customer: $customer,
            paidAt: Payload::nullableString($payload, 'paid_at') ?? Payload::nullableString($payload, 'paidAt'),
            channel: Payload::nullableString($payload, 'channel'),
            raw: $payload,
        );
    }
}

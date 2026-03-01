<?php

namespace Maxiviper117\Paystack\Data\Output\Customer;

use Maxiviper117\Paystack\Data\Customer\CustomerData;
use Maxiviper117\Paystack\Data\Shared\MetaData;
use Spatie\LaravelData\Data;

class ListCustomersResponseData extends Data
{
    /**
     * @param  array<int, CustomerData>  $customers
     */
    public function __construct(
        public array $customers,
        public ?MetaData $meta = null,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $payload
     * @param  array<string, mixed>  $meta
     */
    public static function fromPayload(array $payload, array $meta = []): self
    {
        $customers = [];

        foreach ($payload as $item) {
            $customers[] = CustomerData::fromPayload($item);
        }

        return new self(
            customers: $customers,
            meta: $meta === [] ? null : MetaData::fromPayload($meta),
        );
    }
}

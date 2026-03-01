<?php

namespace Maxiviper117\Paystack\Data\Output\Customer;

use Maxiviper117\Paystack\Data\Customer\CustomerData;
use Spatie\LaravelData\Data;

class UpdateCustomerResponseData extends Data
{
    public function __construct(
        public CustomerData $customer,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            customer: CustomerData::fromPayload($payload),
        );
    }
}

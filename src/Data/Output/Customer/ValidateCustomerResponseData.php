<?php

namespace Maxiviper117\Paystack\Data\Output\Customer;

use Maxiviper117\Paystack\Data\Shared\PaystackResponseData;
use Maxiviper117\Paystack\Support\Payload;
use Override;

class ValidateCustomerResponseData extends PaystackResponseData
{
    /**
     * @param  array<string, mixed>  $payload
     */
    #[Override]
    public static function fromPayload(array $payload): self
    {
        return new self(
            status: Payload::bool($payload, 'status'),
            message: Payload::string($payload, 'message'),
        );
    }
}

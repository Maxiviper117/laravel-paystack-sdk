<?php

namespace Maxiviper117\Paystack\Data\Input\Refund;

use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Spatie\LaravelData\Data;

class RetryRefundInputData extends Data
{
    public function __construct(
        public int|string $id,
        public RefundAccountDetailsInputData $refundAccountDetails,
    ) {
        if (is_int($this->id) && $this->id < 1) {
            throw new InvalidPaystackInputException('The Paystack refund identifier must be greater than zero.');
        }

        if (\is_string($this->id) && trim($this->id) === '') {
            throw new InvalidPaystackInputException('The Paystack refund identifier cannot be empty.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toRequestBody(): array
    {
        return [
            'refund_account_details' => $this->refundAccountDetails->toRequestBody(),
        ];
    }
}

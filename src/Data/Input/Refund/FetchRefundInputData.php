<?php

namespace Maxiviper117\Paystack\Data\Input\Refund;

use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Spatie\LaravelData\Data;

class FetchRefundInputData extends Data
{
    public function __construct(
        public int|string $id,
    ) {
        if (is_int($this->id) && $this->id < 1) {
            throw new InvalidPaystackInputException('The Paystack refund identifier must be greater than zero.');
        }

        if (is_string($this->id) && trim($this->id) === '') {
            throw new InvalidPaystackInputException('The Paystack refund identifier cannot be empty.');
        }
    }
}

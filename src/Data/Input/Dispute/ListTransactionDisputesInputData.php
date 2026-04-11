<?php

namespace Maxiviper117\Paystack\Data\Input\Dispute;

use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Spatie\LaravelData\Data;

class ListTransactionDisputesInputData extends Data
{
    public function __construct(
        public int|string $id,
    ) {
        if (\is_int($this->id) && $this->id < 1) {
            throw new InvalidPaystackInputException('The Paystack transaction dispute identifier must be greater than zero.');
        }

        if (\is_string($this->id) && trim($this->id) === '') {
            throw new InvalidPaystackInputException('The Paystack transaction dispute identifier cannot be empty.');
        }
    }
}

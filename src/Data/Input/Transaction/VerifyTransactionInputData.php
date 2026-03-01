<?php

namespace Maxiviper117\Paystack\Data\Input\Transaction;

use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Spatie\LaravelData\Data;

class VerifyTransactionInputData extends Data
{
    public function __construct(
        public string $reference,
    ) {
        if (trim($this->reference) === '') {
            throw new InvalidPaystackInputException('The Paystack transaction reference cannot be empty.');
        }
    }
}

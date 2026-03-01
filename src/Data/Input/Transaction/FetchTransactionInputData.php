<?php

namespace Maxiviper117\Paystack\Data\Input\Transaction;

use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Spatie\LaravelData\Data;

class FetchTransactionInputData extends Data
{
    public function __construct(
        public int|string $idOrReference,
    ) {
        if (is_string($this->idOrReference) && trim($this->idOrReference) === '') {
            throw new InvalidPaystackInputException('The Paystack transaction identifier cannot be empty.');
        }
    }
}

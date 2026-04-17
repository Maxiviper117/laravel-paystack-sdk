<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Data\Input\Transaction;

use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Spatie\LaravelData\Data;

class FetchTransactionInputData extends Data
{
    public function __construct(
        public int $id,
    ) {
        if ($this->id < 1) {
            throw new InvalidPaystackInputException('The Paystack transaction identifier must be greater than zero.');
        }
    }
}

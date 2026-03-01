<?php

namespace Maxiviper117\Paystack\Data\Input\Plan;

use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Spatie\LaravelData\Data;

class FetchPlanInputData extends Data
{
    public function __construct(
        public int|string $idOrCode,
    ) {
        if (is_string($this->idOrCode) && trim($this->idOrCode) === '') {
            throw new InvalidPaystackInputException('The Paystack plan identifier cannot be empty.');
        }
    }
}

<?php

namespace Maxiviper117\Paystack\Data\Input\Customer;

use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Spatie\LaravelData\Data;

class FetchCustomerInputData extends Data
{
    public function __construct(
        public string $emailOrCode,
    ) {
        if (trim($this->emailOrCode) === '') {
            throw new InvalidPaystackInputException('The Paystack customer identifier cannot be empty.');
        }
    }
}

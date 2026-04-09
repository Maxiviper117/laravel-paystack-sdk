<?php

namespace Maxiviper117\Paystack\Data\Input\Subscription;

use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Spatie\LaravelData\Data;

class GenerateSubscriptionUpdateLinkInputData extends Data
{
    public function __construct(
        public string $code,
    ) {
        if (trim($this->code) === '') {
            throw new InvalidPaystackInputException('The Paystack subscription code cannot be empty.');
        }
    }
}

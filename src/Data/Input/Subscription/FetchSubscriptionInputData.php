<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Data\Input\Subscription;

use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Spatie\LaravelData\Data;

class FetchSubscriptionInputData extends Data
{
    public function __construct(
        public int|string $idOrCode,
    ) {
        if (\is_string($this->idOrCode) && trim($this->idOrCode) === '') {
            throw new InvalidPaystackInputException('The Paystack subscription identifier cannot be empty.');
        }
    }
}

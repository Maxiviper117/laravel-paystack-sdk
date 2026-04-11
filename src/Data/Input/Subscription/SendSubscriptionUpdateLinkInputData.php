<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Data\Input\Subscription;

use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Spatie\LaravelData\Data;

class SendSubscriptionUpdateLinkInputData extends Data
{
    public function __construct(
        public string $code,
    ) {
        if (trim($this->code) === '') {
            throw new InvalidPaystackInputException('The Paystack subscription code cannot be empty.');
        }
    }
}

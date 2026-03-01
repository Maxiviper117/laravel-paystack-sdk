<?php

namespace Maxiviper117\Paystack\Data\Input\Webhook;

use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Spatie\LaravelData\Data;

class VerifyWebhookSignatureInputData extends Data
{
    public function __construct(
        public string $payload,
        public string $signature,
    ) {
        if ($this->payload === '') {
            throw new InvalidPaystackInputException('The Paystack webhook payload cannot be empty.');
        }

        if (trim($this->signature) === '') {
            throw new InvalidPaystackInputException('The Paystack webhook signature cannot be empty.');
        }
    }
}

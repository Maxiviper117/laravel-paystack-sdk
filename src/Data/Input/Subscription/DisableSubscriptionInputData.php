<?php

namespace Maxiviper117\Paystack\Data\Input\Subscription;

use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Spatie\LaravelData\Data;

class DisableSubscriptionInputData extends Data
{
    public function __construct(
        public string $code,
        public string $token,
    ) {
        if (trim($this->code) === '') {
            throw new InvalidPaystackInputException('The Paystack subscription code cannot be empty.');
        }

        if (trim($this->token) === '') {
            throw new InvalidPaystackInputException('The Paystack subscription token cannot be empty.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toRequestBody(): array
    {
        return [
            'code' => $this->code,
            'token' => $this->token,
        ];
    }
}

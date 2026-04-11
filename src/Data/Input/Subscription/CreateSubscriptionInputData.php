<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Data\Input\Subscription;

use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Spatie\LaravelData\Data;

class CreateSubscriptionInputData extends Data
{
    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public string $customer,
        public string $plan,
        public ?string $authorization = null,
        public ?string $startDate = null,
        public array $extra = [],
    ) {
        if (trim($this->customer) === '') {
            throw new InvalidPaystackInputException('The Paystack subscription customer cannot be empty.');
        }

        if (trim($this->plan) === '') {
            throw new InvalidPaystackInputException('The Paystack subscription plan cannot be empty.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toRequestBody(): array
    {
        $payload = $this->extra;
        $payload['customer'] = $this->customer;
        $payload['plan'] = $this->plan;

        if ($this->authorization !== null) {
            $payload['authorization'] = $this->authorization;
        }

        if ($this->startDate !== null) {
            $payload['start_date'] = $this->startDate;
        }

        return $payload;
    }
}

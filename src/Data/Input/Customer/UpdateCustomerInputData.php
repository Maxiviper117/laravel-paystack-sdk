<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Data\Input\Customer;

use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Spatie\LaravelData\Data;

class UpdateCustomerInputData extends Data
{
    /**
     * @param  array<string, mixed>|null  $metadata
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public string $customerCode,
        public ?string $email = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $phone = null,
        public ?array $metadata = null,
        public array $extra = [],
    ) {
        if (trim($this->customerCode) === '') {
            throw new InvalidPaystackInputException('The Paystack customer code cannot be empty.');
        }

        if ($this->email !== null && ! filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidPaystackInputException('The Paystack customer email must be a valid email address.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toRequestBody(): array
    {
        $payload = $this->extra;

        if ($this->email !== null) {
            $payload['email'] = $this->email;
        }

        if ($this->firstName !== null) {
            $payload['first_name'] = $this->firstName;
        }

        if ($this->lastName !== null) {
            $payload['last_name'] = $this->lastName;
        }

        if ($this->phone !== null) {
            $payload['phone'] = $this->phone;
        }

        if ($this->metadata !== null) {
            $payload['metadata'] = $this->metadata;
        }

        return $payload;
    }
}

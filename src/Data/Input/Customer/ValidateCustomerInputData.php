<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Data\Input\Customer;

use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Spatie\LaravelData\Data;

class ValidateCustomerInputData extends Data
{
    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public string $customerCode,
        public string $country,
        public string $type,
        public ?string $value = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $middleName = null,
        public ?string $bvn = null,
        public ?string $bankCode = null,
        public ?string $accountNumber = null,
        public array $extra = [],
    ) {
        if (trim($this->customerCode) === '') {
            throw new InvalidPaystackInputException('The Paystack customer code cannot be empty.');
        }

        if (trim($this->country) === '') {
            throw new InvalidPaystackInputException('The Paystack customer validation country cannot be empty.');
        }

        if (trim($this->type) === '') {
            throw new InvalidPaystackInputException('The Paystack customer validation type cannot be empty.');
        }

        if (
            ($this->accountNumber !== null || $this->bvn !== null || $this->bankCode !== null) && ($this->accountNumber === null || $this->bvn === null || $this->bankCode === null)
        ) {
            throw new InvalidPaystackInputException('The Paystack bank account validation requires an account number, BVN, and bank code.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toRequestBody(): array
    {
        $payload = $this->extra;
        $payload['country'] = $this->country;
        $payload['type'] = $this->type;

        if ($this->value !== null) {
            $payload['value'] = $this->value;
        }

        if ($this->firstName !== null) {
            $payload['first_name'] = $this->firstName;
        }

        if ($this->lastName !== null) {
            $payload['last_name'] = $this->lastName;
        }

        if ($this->middleName !== null) {
            $payload['middle_name'] = $this->middleName;
        }

        if ($this->bvn !== null) {
            $payload['bvn'] = $this->bvn;
        }

        if ($this->bankCode !== null) {
            $payload['bank_code'] = $this->bankCode;
        }

        if ($this->accountNumber !== null) {
            $payload['account_number'] = $this->accountNumber;
        }

        return $payload;
    }
}

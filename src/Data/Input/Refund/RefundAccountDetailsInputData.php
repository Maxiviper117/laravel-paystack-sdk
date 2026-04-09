<?php

namespace Maxiviper117\Paystack\Data\Input\Refund;

use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Spatie\LaravelData\Data;

class RefundAccountDetailsInputData extends Data
{
    public function __construct(
        public string $currency,
        public string $accountNumber,
        public string $bankId,
    ) {
        if (trim($this->currency) === '') {
            throw new InvalidPaystackInputException('The Paystack refund account currency cannot be empty.');
        }

        if (trim($this->accountNumber) === '') {
            throw new InvalidPaystackInputException('The Paystack refund account number cannot be empty.');
        }

        if (trim($this->bankId) === '') {
            throw new InvalidPaystackInputException('The Paystack refund bank identifier cannot be empty.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toRequestBody(): array
    {
        return [
            'currency' => $this->currency,
            'account_number' => $this->accountNumber,
            'bank_id' => $this->bankId,
        ];
    }
}

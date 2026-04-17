<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Data\Input\Refund;

use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Spatie\LaravelData\Data;

class CreateRefundInputData extends Data
{
    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public int|string $transaction,
        public int|string|null $amount = null,
        public ?string $currency = null,
        public ?string $customerNote = null,
        public ?string $merchantNote = null,
        public array $extra = [],
    ) {
        if (\is_int($this->transaction) && $this->transaction < 1) {
            throw new InvalidPaystackInputException('The Paystack refund transaction identifier must be greater than zero.');
        }

        if (\is_string($this->transaction) && trim($this->transaction) === '') {
            throw new InvalidPaystackInputException('The Paystack refund transaction identifier cannot be empty.');
        }

        if ($this->amount !== null) {
            if (! is_numeric($this->amount)) {
                throw new InvalidPaystackInputException('The Paystack refund amount must be numeric.');
            }

            if ((int) $this->amount < 0) {
                throw new InvalidPaystackInputException('The Paystack refund amount cannot be negative.');
            }
        }

        if ($this->currency !== null && trim($this->currency) === '') {
            throw new InvalidPaystackInputException('The Paystack refund currency cannot be empty.');
        }

        if ($this->customerNote !== null && trim($this->customerNote) === '') {
            throw new InvalidPaystackInputException('The Paystack refund customer note cannot be empty.');
        }

        if ($this->merchantNote !== null && trim($this->merchantNote) === '') {
            throw new InvalidPaystackInputException('The Paystack refund merchant note cannot be empty.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toRequestBody(): array
    {
        $payload = $this->extra;

        $payload['transaction'] = $this->transaction;

        if ($this->amount !== null) {
            $payload['amount'] = \is_int($this->amount) ? $this->amount : (int) $this->amount;
        }

        if ($this->currency !== null) {
            $payload['currency'] = $this->currency;
        }

        if ($this->customerNote !== null) {
            $payload['customer_note'] = $this->customerNote;
        }

        if ($this->merchantNote !== null) {
            $payload['merchant_note'] = $this->merchantNote;
        }

        return $payload;
    }
}

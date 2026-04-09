<?php

namespace Maxiviper117\Paystack\Data\Input\Refund;

use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Spatie\LaravelData\Data;

class ListRefundsInputData extends Data
{
    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public int|string|null $transaction = null,
        public ?string $currency = null,
        public ?string $from = null,
        public ?string $to = null,
        public int $perPage = 50,
        public int $page = 1,
        public array $extra = [],
    ) {
        if ($this->transaction !== null && is_string($this->transaction) && trim($this->transaction) === '') {
            throw new InvalidPaystackInputException('The Paystack refund transaction filter cannot be empty.');
        }

        if ($this->transaction !== null && is_int($this->transaction) && $this->transaction < 1) {
            throw new InvalidPaystackInputException('The Paystack refund transaction filter must be greater than zero.');
        }

        if ($this->currency !== null && trim($this->currency) === '') {
            throw new InvalidPaystackInputException('The Paystack refund currency filter cannot be empty.');
        }

        if ($this->from !== null && trim($this->from) === '') {
            throw new InvalidPaystackInputException('The Paystack refund from filter cannot be empty.');
        }

        if ($this->to !== null && trim($this->to) === '') {
            throw new InvalidPaystackInputException('The Paystack refund to filter cannot be empty.');
        }

        if ($this->perPage < 1) {
            throw new InvalidPaystackInputException('The Paystack refund page size must be greater than zero.');
        }

        if ($this->page < 1) {
            throw new InvalidPaystackInputException('The Paystack refund page number must be greater than zero.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toRequestQuery(): array
    {
        $payload = $this->extra;

        if ($this->transaction !== null) {
            $payload['transaction'] = $this->transaction;
        }

        if ($this->currency !== null) {
            $payload['currency'] = $this->currency;
        }

        if ($this->from !== null) {
            $payload['from'] = $this->from;
        }

        if ($this->to !== null) {
            $payload['to'] = $this->to;
        }

        $payload['perPage'] = $this->perPage;
        $payload['page'] = $this->page;

        return $payload;
    }
}

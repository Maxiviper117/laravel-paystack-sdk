<?php

namespace Maxiviper117\Paystack\Data\Input\Transaction;

use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Spatie\LaravelData\Data;

class ListTransactionsInputData extends Data
{
    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public ?int $perPage = null,
        public ?int $page = null,
        public ?string $customer = null,
        public ?string $status = null,
        public ?string $from = null,
        public ?string $to = null,
        public int|string|null $amount = null,
        public ?string $reference = null,
        public array $extra = [],
    ) {
        if ($this->perPage !== null && $this->perPage < 1) {
            throw new InvalidPaystackInputException('The Paystack per-page filter must be greater than zero.');
        }

        if ($this->page !== null && $this->page < 1) {
            throw new InvalidPaystackInputException('The Paystack page filter must be greater than zero.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toRequestQuery(): array
    {
        $query = $this->extra;

        foreach ([
            'perPage' => $this->perPage,
            'page' => $this->page,
            'customer' => $this->customer,
            'status' => $this->status,
            'from' => $this->from,
            'to' => $this->to,
            'amount' => $this->amount,
            'reference' => $this->reference,
        ] as $key => $value) {
            if ($value !== null) {
                $query[$key] = $value;
            }
        }

        return $query;
    }
}

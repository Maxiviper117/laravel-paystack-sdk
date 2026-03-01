<?php

namespace Maxiviper117\Paystack\Data\Input\Plan;

use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Spatie\LaravelData\Data;

class ListPlansInputData extends Data
{
    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public ?int $perPage = null,
        public ?int $page = null,
        public ?string $status = null,
        public ?string $interval = null,
        public int|string|null $amount = null,
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
            'status' => $this->status,
            'interval' => $this->interval,
            'amount' => $this->amount,
        ] as $key => $value) {
            if ($value !== null) {
                $query[$key] = $value;
            }
        }

        return $query;
    }
}

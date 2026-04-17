<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Data\Input\Customer;

use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Spatie\LaravelData\Data;

class ListCustomersInputData extends Data
{
    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public ?int $perPage = null,
        public ?int $page = null,
        public ?string $email = null,
        public ?string $from = null,
        public ?string $to = null,
        public array $extra = [],
    ) {
        if ($this->perPage !== null && $this->perPage < 1) {
            throw new InvalidPaystackInputException('The Paystack per-page filter must be greater than zero.');
        }

        if ($this->page !== null && $this->page < 1) {
            throw new InvalidPaystackInputException('The Paystack page filter must be greater than zero.');
        }

        if ($this->email !== null && ! filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidPaystackInputException('The Paystack customer email filter must be a valid email address.');
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
            'email' => $this->email,
            'from' => $this->from,
            'to' => $this->to,
        ] as $key => $value) {
            if ($value !== null) {
                $query[$key] = $value;
            }
        }

        return $query;
    }
}

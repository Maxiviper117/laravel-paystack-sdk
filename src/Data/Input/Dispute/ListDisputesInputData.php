<?php

namespace Maxiviper117\Paystack\Data\Input\Dispute;

use Maxiviper117\Paystack\Data\Dispute\DisputeStatus;
use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Spatie\LaravelData\Data;

class ListDisputesInputData extends Data
{
    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public ?string $from = null,
        public ?string $to = null,
        public ?int $perPage = null,
        public ?int $page = null,
        public ?string $transaction = null,
        public DisputeStatus|string|null $status = null,
        public array $extra = [],
    ) {
        if ($this->from !== null && trim($this->from) === '') {
            throw new InvalidPaystackInputException('The Paystack dispute from filter cannot be empty.');
        }

        if ($this->to !== null && trim($this->to) === '') {
            throw new InvalidPaystackInputException('The Paystack dispute to filter cannot be empty.');
        }

        if ($this->perPage !== null && $this->perPage < 1) {
            throw new InvalidPaystackInputException('The Paystack dispute per-page filter must be greater than zero.');
        }

        if ($this->page !== null && $this->page < 1) {
            throw new InvalidPaystackInputException('The Paystack dispute page filter must be greater than zero.');
        }

        if ($this->transaction !== null && trim($this->transaction) === '') {
            throw new InvalidPaystackInputException('The Paystack dispute transaction filter cannot be empty.');
        }

        if ($this->status !== null && (! $this->status instanceof DisputeStatus && ! in_array($this->status, self::allowedStatuses(), true))) {
            throw new InvalidPaystackInputException('The Paystack dispute status filter is invalid.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toRequestQuery(): array
    {
        $query = $this->extra;

        foreach ([
            'from' => $this->from,
            'to' => $this->to,
            'perPage' => $this->perPage,
            'page' => $this->page,
            'transaction' => $this->transaction,
            'status' => $this->status instanceof DisputeStatus ? $this->status->value : $this->status,
        ] as $key => $value) {
            if ($value !== null) {
                $query[$key] = $value;
            }
        }

        return $query;
    }

    /**
     * @return array<int, string>
     */
    public static function allowedStatuses(): array
    {
        return DisputeStatus::values();
    }
}

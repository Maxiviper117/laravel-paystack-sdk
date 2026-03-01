<?php

namespace Maxiviper117\Paystack\Data\Output\Transaction;

use Maxiviper117\Paystack\Data\Shared\MetaData;
use Maxiviper117\Paystack\Data\Transaction\TransactionData;
use Spatie\LaravelData\Data;

class ListTransactionsResponseData extends Data
{
    /**
     * @param  array<int, TransactionData>  $transactions
     */
    public function __construct(
        public array $transactions,
        public ?MetaData $meta = null,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $payload
     * @param  array<string, mixed>  $meta
     */
    public static function fromPayload(array $payload, array $meta = []): self
    {
        $transactions = [];

        foreach ($payload as $item) {
            $transactions[] = TransactionData::fromPayload($item);
        }

        return new self(
            transactions: $transactions,
            meta: $meta === [] ? null : MetaData::fromPayload($meta),
        );
    }
}

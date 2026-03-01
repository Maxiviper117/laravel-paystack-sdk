<?php

namespace Maxiviper117\Paystack\Data\Output\Transaction;

use Maxiviper117\Paystack\Data\Transaction\TransactionData;
use Spatie\LaravelData\Data;

class FetchTransactionResponseData extends Data
{
    public function __construct(
        public TransactionData $transaction,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            transaction: TransactionData::fromPayload($payload),
        );
    }
}

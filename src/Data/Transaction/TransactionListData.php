<?php

namespace Maxiviper117\Paystack\Data\Transaction;

use Maxiviper117\Paystack\Data\Shared\MetaData;
use Spatie\LaravelData\Data;

class TransactionListData extends Data
{
    /**
     * @param array<int, TransactionData> $items
     */
    public function __construct(
        public array $items,
        public ?MetaData $meta = null,
    ) {}

    /**
     * @param array<int, array<string, mixed>> $payload
     * @param array<string, mixed> $meta
     */
    public static function fromPayload(array $payload, array $meta = []): self
    {
        $items = [];

        foreach ($payload as $item) {
            $items[] = TransactionData::fromPayload($item);
        }

        return new self(
            items: $items,
            meta: empty($meta) ? null : MetaData::fromPayload($meta),
        );
    }
}

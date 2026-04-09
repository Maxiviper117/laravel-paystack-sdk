<?php

namespace Maxiviper117\Paystack\Data\Output\Refund;

use Maxiviper117\Paystack\Data\Refund\RefundData;
use Maxiviper117\Paystack\Data\Shared\MetaData;
use Spatie\LaravelData\Data;

class ListRefundsResponseData extends Data
{
    /**
     * @param  array<int, RefundData>  $refunds
     */
    public function __construct(
        public array $refunds,
        public ?MetaData $meta = null,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $payload
     * @param  array<string, mixed>  $meta
     */
    public static function fromPayload(array $payload, array $meta = []): self
    {
        $refunds = [];

        foreach ($payload as $item) {
            $refunds[] = RefundData::fromPayload($item);
        }

        return new self(
            refunds: $refunds,
            meta: $meta === [] ? null : MetaData::fromPayload($meta),
        );
    }
}

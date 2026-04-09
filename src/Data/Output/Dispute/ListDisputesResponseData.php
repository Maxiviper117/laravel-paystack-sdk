<?php

namespace Maxiviper117\Paystack\Data\Output\Dispute;

use Maxiviper117\Paystack\Data\Dispute\DisputeData;
use Maxiviper117\Paystack\Data\Shared\MetaData;
use Spatie\LaravelData\Data;

class ListDisputesResponseData extends Data
{
    /**
     * @param  array<int, DisputeData>  $disputes
     */
    public function __construct(
        public array $disputes,
        public ?MetaData $meta = null,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $payload
     * @param  array<string, mixed>  $meta
     */
    public static function fromPayload(array $payload, array $meta = []): self
    {
        $disputes = [];

        foreach ($payload as $item) {
            $disputes[] = DisputeData::fromPayload($item);
        }

        return new self(
            disputes: $disputes,
            meta: $meta === [] ? null : MetaData::fromPayload($meta),
        );
    }
}

<?php

namespace Maxiviper117\Paystack\Data\Output\Dispute;

use Maxiviper117\Paystack\Data\Dispute\DisputeData;
use Spatie\LaravelData\Data;

class UpdateDisputeResponseData extends Data
{
    /**
     * @param  array<int, DisputeData>  $disputes
     */
    public function __construct(
        public array $disputes,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        $disputes = [];

        foreach ($payload as $item) {
            $disputes[] = DisputeData::fromPayload($item);
        }

        return new self(disputes: $disputes);
    }
}

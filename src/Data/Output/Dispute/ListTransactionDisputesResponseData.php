<?php

namespace Maxiviper117\Paystack\Data\Output\Dispute;

use Maxiviper117\Paystack\Data\Dispute\DisputeData;
use Spatie\LaravelData\Data;

class ListTransactionDisputesResponseData extends Data
{
    public function __construct(
        public DisputeData $dispute,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            dispute: DisputeData::fromPayload($payload),
        );
    }
}

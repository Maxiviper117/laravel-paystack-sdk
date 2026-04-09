<?php

namespace Maxiviper117\Paystack\Data\Output\Dispute;

use Maxiviper117\Paystack\Data\Dispute\DisputeEvidenceData;
use Spatie\LaravelData\Data;

class AddDisputeEvidenceResponseData extends Data
{
    public function __construct(
        public DisputeEvidenceData $evidence,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            evidence: DisputeEvidenceData::fromPayload($payload),
        );
    }
}

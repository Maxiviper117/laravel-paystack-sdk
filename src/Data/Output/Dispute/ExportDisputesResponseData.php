<?php

namespace Maxiviper117\Paystack\Data\Output\Dispute;

use Maxiviper117\Paystack\Data\Dispute\DisputeExportData;
use Spatie\LaravelData\Data;

class ExportDisputesResponseData extends Data
{
    public function __construct(
        public DisputeExportData $export,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            export: DisputeExportData::fromPayload($payload),
        );
    }
}

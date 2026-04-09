<?php

namespace Maxiviper117\Paystack\Data\Output\Dispute;

use Maxiviper117\Paystack\Data\Dispute\DisputeUploadUrlData;
use Spatie\LaravelData\Data;

class GetDisputeUploadUrlResponseData extends Data
{
    public function __construct(
        public DisputeUploadUrlData $uploadUrl,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            uploadUrl: DisputeUploadUrlData::fromPayload($payload),
        );
    }
}

<?php

namespace Maxiviper117\Paystack\Data\Output\Refund;

use Maxiviper117\Paystack\Data\Refund\RefundData;
use Spatie\LaravelData\Data;

class CreateRefundResponseData extends Data
{
    public function __construct(
        public RefundData $refund,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(refund: RefundData::fromPayload($payload));
    }
}

<?php

namespace Maxiviper117\Paystack\Data\Output\Plan;

use Maxiviper117\Paystack\Data\Plan\PlanData;
use Spatie\LaravelData\Data;

class FetchPlanResponseData extends Data
{
    public function __construct(
        public PlanData $plan,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(plan: PlanData::fromPayload($payload));
    }
}

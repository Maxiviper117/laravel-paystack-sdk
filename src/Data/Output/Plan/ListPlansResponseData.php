<?php

namespace Maxiviper117\Paystack\Data\Output\Plan;

use Maxiviper117\Paystack\Data\Plan\PlanData;
use Maxiviper117\Paystack\Data\Shared\MetaData;
use Spatie\LaravelData\Data;

class ListPlansResponseData extends Data
{
    /**
     * @param  array<int, PlanData>  $plans
     */
    public function __construct(
        public array $plans,
        public ?MetaData $meta = null,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $payload
     * @param  array<string, mixed>  $meta
     */
    public static function fromPayload(array $payload, array $meta = []): self
    {
        $plans = [];

        foreach ($payload as $item) {
            $plans[] = PlanData::fromPayload($item);
        }

        return new self(
            plans: $plans,
            meta: $meta === [] ? null : MetaData::fromPayload($meta),
        );
    }
}

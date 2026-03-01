<?php

namespace Maxiviper117\Paystack\Data\Output\Subscription;

use Maxiviper117\Paystack\Data\Shared\MetaData;
use Maxiviper117\Paystack\Data\Subscription\SubscriptionData;
use Spatie\LaravelData\Data;

class ListSubscriptionsResponseData extends Data
{
    /**
     * @param  array<int, SubscriptionData>  $subscriptions
     */
    public function __construct(
        public array $subscriptions,
        public ?MetaData $meta = null,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $payload
     * @param  array<string, mixed>  $meta
     */
    public static function fromPayload(array $payload, array $meta = []): self
    {
        $subscriptions = [];

        foreach ($payload as $item) {
            $subscriptions[] = SubscriptionData::fromPayload($item);
        }

        return new self(
            subscriptions: $subscriptions,
            meta: $meta === [] ? null : MetaData::fromPayload($meta),
        );
    }
}

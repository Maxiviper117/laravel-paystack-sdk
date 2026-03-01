<?php

namespace Maxiviper117\Paystack\Data\Output\Subscription;

use Maxiviper117\Paystack\Data\Subscription\SubscriptionData;
use Spatie\LaravelData\Data;

class FetchSubscriptionResponseData extends Data
{
    public function __construct(
        public SubscriptionData $subscription,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(subscription: SubscriptionData::fromPayload($payload));
    }
}

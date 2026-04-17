<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Data\Output\Webhook\Typed;

use Spatie\LaravelData\Data;

class SubscriptionExpiringCardsWebhookData extends Data implements PaystackTypedWebhookData
{
    /**
     * @param  array<int, SubscriptionExpiringCardData>  $cards
     * @param  array<mixed>  $rawData
     */
    public function __construct(
        public string $event,
        public array $cards,
        public array $rawData = [],
    ) {}
}

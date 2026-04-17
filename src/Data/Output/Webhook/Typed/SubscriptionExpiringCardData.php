<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Data\Output\Webhook\Typed;

use Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested\WebhookCustomerPayloadData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested\WebhookSubscriptionPayloadData;
use Spatie\LaravelData\Data;

class SubscriptionExpiringCardData extends Data
{
    /**
     * @param  array<string, mixed>  $rawData
     */
    public function __construct(
        public ?string $expiryDate,
        public ?string $description,
        public ?string $brand,
        public WebhookSubscriptionPayloadData $subscription,
        public WebhookCustomerPayloadData $customer,
        public array $rawData = [],
    ) {}
}

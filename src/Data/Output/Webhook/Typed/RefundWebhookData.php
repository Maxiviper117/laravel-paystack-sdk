<?php

namespace Maxiviper117\Paystack\Data\Output\Webhook\Typed;

use Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested\WebhookCustomerPayloadData;
use Spatie\LaravelData\Data;

class RefundWebhookData extends Data implements PaystackTypedWebhookData
{
    /**
     * @param  array<string, mixed>  $rawData
     */
    public function __construct(
        public string $event,
        public ?string $refundReference,
        public string $transactionReference,
        public string $status,
        public int $amount,
        public ?string $currency,
        public ?string $domain,
        public ?string $processor,
        public int|string|null $integration,
        public ?WebhookCustomerPayloadData $customer,
        public array $rawData = [],
    ) {}
}

<?php

namespace Maxiviper117\Paystack\Data\Output\Webhook\Typed;

use Spatie\LaravelData\Data;

class CustomerIdentificationWebhookData extends Data implements PaystackTypedWebhookData
{
    /**
     * @param  array<string, mixed>  $rawData
     */
    public function __construct(
        public string $event,
        public ?string $customerId,
        public string $customerCode,
        public string $email,
        public CustomerIdentificationDetailsData $identification,
        public ?string $reason,
        public array $rawData = [],
    ) {}
}

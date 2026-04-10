<?php

namespace Maxiviper117\Paystack\Data\Output\Webhook\Typed;

use Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested\WebhookCustomerPayloadData;
use Spatie\LaravelData\Data;

class DedicatedAccountAssignWebhookData extends Data implements PaystackTypedWebhookData
{
    /**
     * @param  array<string, mixed>  $rawData
     */
    public function __construct(
        public string $event,
        public string $status,
        public WebhookCustomerPayloadData $customer,
        public ?DedicatedAccountData $dedicatedAccount,
        public array $rawData = [],
    ) {}
}

<?php

namespace Maxiviper117\Paystack\Data\Output\Webhook\Typed;

use Maxiviper117\Paystack\Data\Customer\CustomerData;
use Maxiviper117\Paystack\Data\Plan\PlanData;
use Maxiviper117\Paystack\Data\Subscription\SubscriptionData;
use Spatie\LaravelData\Data;

class SubscriptionWebhookData extends Data implements PaystackTypedWebhookData
{
    /**
     * @param  array<string, mixed>  $rawData
     */
    public function __construct(
        public string $event,
        public string $subscriptionCode,
        public string $status,
        public ?string $domain,
        public ?string $emailToken,
        public ?int $amount,
        public ?string $nextPaymentDate,
        public int|string|null $openInvoice,
        public ?CustomerData $customer,
        public ?PlanData $plan,
        public SubscriptionData $subscription,
        public array $rawData = [],
    ) {}
}

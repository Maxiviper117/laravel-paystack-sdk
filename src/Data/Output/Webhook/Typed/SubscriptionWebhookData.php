<?php

namespace Maxiviper117\Paystack\Data\Output\Webhook\Typed;

use Carbon\CarbonImmutable;
use Maxiviper117\Paystack\Data\Customer\CustomerData;
use Maxiviper117\Paystack\Data\Plan\PlanData;
use Maxiviper117\Paystack\Data\Subscription\SubscriptionData;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

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
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $nextPaymentDate,
        public int|string|null $openInvoice,
        public ?CustomerData $customer,
        public ?PlanData $plan,
        public SubscriptionData $subscription,
        public array $rawData = [],
    ) {}
}

<?php

namespace Maxiviper117\Paystack\Data\Output\Webhook\Typed;

use Carbon\CarbonImmutable;
use Maxiviper117\Paystack\Data\Customer\CustomerData;
use Maxiviper117\Paystack\Data\Subscription\SubscriptionData;
use Maxiviper117\Paystack\Data\Transaction\TransactionData;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

class InvoiceWebhookData extends Data implements PaystackTypedWebhookData
{
    /**
     * @param  array<string, mixed>  $rawData
     */
    public function __construct(
        public string $event,
        public string $invoiceCode,
        public string $status,
        public bool $paid,
        public int $amount,
        public ?string $domain,
        public ?string $periodStart,
        public ?string $periodEnd,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $paidAt,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $nextPaymentDate,
        public ?string $description,
        public ?string $subscriptionCode,
        public ?string $customerCode,
        public ?string $authorizationCode,
        public ?CustomerData $customer,
        public ?SubscriptionData $subscription,
        public ?TransactionData $transaction,
        public array $rawData = [],
    ) {}
}

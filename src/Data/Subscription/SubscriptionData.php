<?php

namespace Maxiviper117\Paystack\Data\Subscription;

use Carbon\CarbonImmutable;
use Maxiviper117\Paystack\Data\Customer\CustomerData;
use Maxiviper117\Paystack\Data\Plan\PlanData;
use Maxiviper117\Paystack\Support\Payload;
use Maxiviper117\Paystack\Support\PaystackDate;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

class SubscriptionData extends Data
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public int|string|null $id,
        public string $subscriptionCode,
        public ?string $status,
        public ?string $emailToken,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $nextPaymentDate,
        public int|string|null $openInvoice,
        public ?PlanData $plan,
        public ?CustomerData $customer,
        public array $raw = [],
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        $planPayload = Payload::nullableArray($payload, 'plan');
        $customerPayload = Payload::nullableArray($payload, 'customer');
        $plan = null;
        $customer = null;

        if ($planPayload !== null && ! array_is_list($planPayload)) {
            /** @var array<string, mixed> $planPayload */
            $plan = PlanData::fromPayload($planPayload);
        }

        if ($customerPayload !== null && ! array_is_list($customerPayload)) {
            /** @var array<string, mixed> $customerPayload */
            $customer = CustomerData::fromPayload($customerPayload);
        }

        return new self(
            id: Payload::intOrStringOrNull($payload, 'id'),
            subscriptionCode: Payload::nullableString($payload, 'subscription_code') ?? Payload::string($payload, 'subscriptionCode'),
            status: Payload::nullableString($payload, 'status'),
            emailToken: Payload::nullableString($payload, 'email_token') ?? Payload::nullableString($payload, 'emailToken'),
            nextPaymentDate: PaystackDate::nullable(
                Payload::nullableString($payload, 'next_payment_date') ?? Payload::nullableString($payload, 'nextPaymentDate')
            ),
            openInvoice: Payload::intOrStringOrNull($payload, 'open_invoice') ?? Payload::intOrStringOrNull($payload, 'openInvoice'),
            plan: $plan,
            customer: $customer,
            raw: $payload,
        );
    }
}

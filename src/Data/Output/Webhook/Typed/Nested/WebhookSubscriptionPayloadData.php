<?php

namespace Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested;

use Carbon\CarbonImmutable;
use Maxiviper117\Paystack\Support\Payload;
use Maxiviper117\Paystack\Support\PaystackDate;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

/**
 * Subscription fragment embedded inside webhook payloads.
 *
 * Typed webhook event DTOs use this as a nested payload fragment instead of
 * depending on the standalone subscription endpoint response shape.
 */
class WebhookSubscriptionPayloadData extends Data
{
    /**
     * @param  array<string, mixed>  $rawData
     */
    public function __construct(
        public int|string|null $id,
        public string $subscriptionCode,
        public ?string $status,
        public ?string $emailToken,
        public ?int $amount,
        public ?string $cronExpression,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $nextPaymentDate,
        public int|string|null $openInvoice,
        public ?WebhookPlanPayloadData $plan,
        public ?WebhookCustomerPayloadData $customer,
        public array $rawData = [],
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
            $plan = WebhookPlanPayloadData::fromPayload($planPayload);
        }

        if ($customerPayload !== null && ! array_is_list($customerPayload)) {
            /** @var array<string, mixed> $customerPayload */
            $customer = WebhookCustomerPayloadData::fromPayload($customerPayload);
        }

        return new self(
            id: Payload::intOrStringOrNull($payload, 'id'),
            subscriptionCode: Payload::nullableString($payload, 'subscription_code') ?? Payload::string($payload, 'subscriptionCode'),
            status: Payload::nullableString($payload, 'status'),
            emailToken: Payload::nullableString($payload, 'email_token') ?? Payload::nullableString($payload, 'emailToken'),
            amount: array_key_exists('amount', $payload) ? Payload::int($payload, 'amount') : null,
            cronExpression: Payload::nullableString($payload, 'cron_expression') ?? Payload::nullableString($payload, 'cronExpression'),
            nextPaymentDate: PaystackDate::nullable(
                Payload::nullableString($payload, 'next_payment_date') ?? Payload::nullableString($payload, 'nextPaymentDate')
            ),
            openInvoice: Payload::intOrStringOrNull($payload, 'open_invoice') ?? Payload::intOrStringOrNull($payload, 'openInvoice'),
            plan: $plan,
            customer: $customer,
            rawData: $payload,
        );
    }
}

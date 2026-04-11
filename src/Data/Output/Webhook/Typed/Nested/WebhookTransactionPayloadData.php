<?php

namespace Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested;

use Carbon\CarbonImmutable;
use Maxiviper117\Paystack\Support\Payload;
use Maxiviper117\Paystack\Support\PaystackDate;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

/**
 * Transaction fragment embedded inside webhook payloads.
 *
 * This is webhook-specific composition data, not the transaction endpoint
 * response DTO, and only captures the transaction fields needed by webhooks.
 */
class WebhookTransactionPayloadData extends Data
{
    /**
     * @param  array<mixed>|null  $metadata
     * @param  array<mixed>|null  $log
     * @param  array<mixed>|null  $feesSplit
     * @param  array<mixed>|null  $authorization
     * @param  array<mixed>|null  $plan
     * @param  array<mixed>|null  $subaccount
     * @param  array<mixed>|null  $split
     * @param  array<string, mixed>  $rawData
     */
    public function __construct(
        public int|string|null $id,
        public ?string $domain,
        public ?string $status,
        public ?string $reference,
        public ?int $amount,
        public ?string $message,
        public ?string $gatewayResponse,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $paidAt,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $createdAt,
        public ?string $channel,
        public ?string $currency,
        public ?string $ipAddress,
        public ?array $metadata,
        public ?array $log,
        public ?int $fees,
        public ?array $feesSplit,
        public ?array $authorization,
        public ?WebhookCustomerPayloadData $customer,
        public ?array $plan,
        public ?array $subaccount,
        public ?array $split,
        public ?string $orderId,
        public ?int $requestedAmount,
        public array $rawData = [],
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        $customerPayload = Payload::nullableArray($payload, 'customer');
        $customer = null;

        if ($customerPayload !== null && ! array_is_list($customerPayload)) {
            /** @var array<string, mixed> $customerPayload */
            $customer = WebhookCustomerPayloadData::fromPayload($customerPayload);
        }

        return new self(
            id: Payload::intOrStringOrNull($payload, 'id'),
            domain: Payload::nullableString($payload, 'domain'),
            status: Payload::nullableString($payload, 'status'),
            reference: Payload::nullableString($payload, 'reference'),
            amount: array_key_exists('amount', $payload) ? Payload::int($payload, 'amount') : null,
            message: Payload::nullableString($payload, 'message'),
            gatewayResponse: Payload::nullableString($payload, 'gateway_response') ?? Payload::nullableString($payload, 'gatewayResponse'),
            paidAt: PaystackDate::nullable(
                Payload::nullableString($payload, 'paid_at') ?? Payload::nullableString($payload, 'paidAt')
            ),
            createdAt: PaystackDate::nullable(
                Payload::nullableString($payload, 'created_at') ?? Payload::nullableString($payload, 'createdAt')
            ),
            channel: Payload::nullableString($payload, 'channel'),
            currency: Payload::nullableString($payload, 'currency'),
            ipAddress: Payload::nullableString($payload, 'ip_address') ?? Payload::nullableString($payload, 'ipAddress'),
            metadata: Payload::nullableArray($payload, 'metadata'),
            log: Payload::nullableArray($payload, 'log'),
            fees: array_key_exists('fees', $payload) ? Payload::int($payload, 'fees') : null,
            feesSplit: Payload::nullableArray($payload, 'fees_split') ?? Payload::nullableArray($payload, 'feesSplit'),
            authorization: Payload::nullableArray($payload, 'authorization'),
            customer: $customer,
            plan: Payload::nullableArray($payload, 'plan'),
            subaccount: Payload::nullableArray($payload, 'subaccount'),
            split: Payload::nullableArray($payload, 'split'),
            orderId: Payload::nullableString($payload, 'order_id') ?? Payload::nullableString($payload, 'orderId'),
            requestedAmount: array_key_exists('requested_amount', $payload) || array_key_exists('requestedAmount', $payload)
                ? Payload::int($payload, array_key_exists('requested_amount', $payload) ? 'requested_amount' : 'requestedAmount')
                : null,
            rawData: $payload,
        );
    }
}

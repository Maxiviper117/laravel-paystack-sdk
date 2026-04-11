<?php

namespace Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested;

use Carbon\CarbonImmutable;
use Maxiviper117\Paystack\Support\Payload;
use Maxiviper117\Paystack\Support\PaystackDate;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

/**
 * Charge dispute payload fragment embedded in dispute webhook events.
 *
 * This composes the nested customer, transaction, history, and message
 * fragments used by dispute-related webhook DTOs.
 */
class ChargeDisputePayloadData extends Data
{
    /**
     * @param  array<mixed>|null  $evidence
     * @param  array<int, ChargeDisputeHistoryPayloadData>  $history
     * @param  array<int, ChargeDisputeMessagePayloadData>  $messages
     * @param  array<string, mixed>  $rawData
     */
    public function __construct(
        public int|string|null $id,
        public ?int $refundAmount,
        public ?string $currency,
        public ?string $status,
        public ?string $resolution,
        public ?string $domain,
        public ?string $category,
        public ?string $note,
        public ?string $attachments,
        public ?string $last4,
        public ?string $bin,
        public ?string $transactionReference,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $resolvedAt,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $dueAt,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $createdAt,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $updatedAt,
        public ?array $evidence,
        public ?WebhookTransactionPayloadData $transaction,
        public ?WebhookCustomerPayloadData $customer,
        public array $history = [],
        public array $messages = [],
        public array $rawData = [],
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        $transactionPayload = Payload::nullableArray($payload, 'transaction');
        $customerPayload = Payload::nullableArray($payload, 'customer');
        $historyPayload = Payload::nullableArray($payload, 'history') ?? [];
        $messagesPayload = Payload::nullableArray($payload, 'messages') ?? [];
        $history = [];
        $messages = [];
        $transaction = null;
        $customer = null;

        foreach ($historyPayload as $item) {
            if (\is_array($item) && ! array_is_list($item)) {
                /** @var array<string, mixed> $item */
                $history[] = ChargeDisputeHistoryPayloadData::fromPayload($item);
            }
        }

        foreach ($messagesPayload as $item) {
            if (\is_array($item) && ! array_is_list($item)) {
                /** @var array<string, mixed> $item */
                $messages[] = ChargeDisputeMessagePayloadData::fromPayload($item);
            }
        }

        if ($transactionPayload !== null && ! array_is_list($transactionPayload)) {
            /** @var array<string, mixed> $transactionPayload */
            $transaction = WebhookTransactionPayloadData::fromPayload($transactionPayload);
        }

        if ($customerPayload !== null && ! array_is_list($customerPayload)) {
            /** @var array<string, mixed> $customerPayload */
            $customer = WebhookCustomerPayloadData::fromPayload($customerPayload);
        }

        return new self(
            id: Payload::intOrStringOrNull($payload, 'id'),
            refundAmount: array_key_exists('refund_amount', $payload) || array_key_exists('refundAmount', $payload)
                ? Payload::int($payload, array_key_exists('refund_amount', $payload) ? 'refund_amount' : 'refundAmount')
                : null,
            currency: Payload::nullableString($payload, 'currency'),
            status: Payload::nullableString($payload, 'status'),
            resolution: Payload::nullableString($payload, 'resolution'),
            domain: Payload::nullableString($payload, 'domain'),
            category: Payload::nullableString($payload, 'category'),
            note: Payload::nullableString($payload, 'note'),
            attachments: Payload::nullableString($payload, 'attachments'),
            last4: Payload::nullableString($payload, 'last4'),
            bin: Payload::nullableString($payload, 'bin'),
            transactionReference: Payload::nullableString($payload, 'transaction_reference') ?? Payload::nullableString($payload, 'transactionReference'),
            resolvedAt: PaystackDate::nullable(
                Payload::nullableString($payload, 'resolvedAt') ?? Payload::nullableString($payload, 'resolved_at')
            ),
            dueAt: PaystackDate::nullable(
                Payload::nullableString($payload, 'dueAt') ?? Payload::nullableString($payload, 'due_at')
            ),
            createdAt: PaystackDate::nullable(
                Payload::nullableString($payload, 'created_at') ?? Payload::nullableString($payload, 'createdAt')
            ),
            updatedAt: PaystackDate::nullable(
                Payload::nullableString($payload, 'updated_at') ?? Payload::nullableString($payload, 'updatedAt')
            ),
            evidence: Payload::nullableArray($payload, 'evidence'),
            transaction: $transaction,
            customer: $customer,
            history: $history,
            messages: $messages,
            rawData: $payload,
        );
    }
}

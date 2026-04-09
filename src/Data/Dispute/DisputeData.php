<?php

namespace Maxiviper117\Paystack\Data\Dispute;

use Carbon\CarbonImmutable;
use Maxiviper117\Paystack\Support\Payload;
use Maxiviper117\Paystack\Support\PaystackDate;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

class DisputeData extends Data
{
    /**
     * @param  array<int, DisputeHistoryData>  $history
     * @param  array<int, DisputeMessageData>  $messages
     * @param  array<string, mixed>|null  $evidence
     * @param  array<string, mixed>  $raw
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
        public ?string $merchantTransactionReference,
        public ?string $source,
        public int|string|null $createdBy,
        public int|string|null $organization,
        public int|string|null $integration,
        public ?array $evidence,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $resolvedAt,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $dueAt,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $createdAt,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $updatedAt,
        public ?DisputeTransactionData $transaction,
        public ?DisputeCustomerData $customer,
        public array $history = [],
        public array $messages = [],
        public array $raw = [],
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        $transactionPayload = Payload::nullableArray($payload, 'transaction');
        $transaction = null;

        if ($transactionPayload !== null && ! array_is_list($transactionPayload)) {
            /** @var array<string, mixed> $transactionPayload */
            $transaction = DisputeTransactionData::fromPayload($transactionPayload);
        }

        $customerPayload = Payload::nullableArray($payload, 'customer');
        $customer = null;

        if ($customerPayload !== null && ! array_is_list($customerPayload)) {
            /** @var array<string, mixed> $customerPayload */
            $customer = DisputeCustomerData::fromPayload($customerPayload);
        }

        $history = [];
        $historyPayload = Payload::nullableArray($payload, 'history');

        if ($historyPayload !== null) {
            /** @var array<int, array<string, mixed>> $historyPayload */
            foreach ($historyPayload as $item) {
                $history[] = DisputeHistoryData::fromPayload($item);
            }
        }

        $messages = [];
        $messagesPayload = Payload::nullableArray($payload, 'messages');

        if ($messagesPayload !== null) {
            /** @var array<int, array<string, mixed>> $messagesPayload */
            foreach ($messagesPayload as $item) {
                $messages[] = DisputeMessageData::fromPayload($item);
            }
        }

        /** @var array<string, mixed>|null $evidence */
        $evidence = Payload::nullableArray($payload, 'evidence');

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
            merchantTransactionReference: Payload::nullableString($payload, 'merchant_transaction_reference') ?? Payload::nullableString($payload, 'merchantTransactionReference'),
            source: Payload::nullableString($payload, 'source'),
            createdBy: Payload::intOrStringOrNull($payload, 'created_by') ?? Payload::intOrStringOrNull($payload, 'createdBy'),
            organization: Payload::intOrStringOrNull($payload, 'organization'),
            integration: Payload::intOrStringOrNull($payload, 'integration'),
            evidence: $evidence,
            resolvedAt: PaystackDate::nullable(
                Payload::nullableString($payload, 'resolvedAt') ?? Payload::nullableString($payload, 'resolved_at')
            ),
            dueAt: PaystackDate::nullable(
                Payload::nullableString($payload, 'dueAt') ?? Payload::nullableString($payload, 'due_at')
            ),
            createdAt: PaystackDate::nullable(
                Payload::nullableString($payload, 'createdAt') ?? Payload::nullableString($payload, 'created_at')
            ),
            updatedAt: PaystackDate::nullable(
                Payload::nullableString($payload, 'updatedAt') ?? Payload::nullableString($payload, 'updated_at')
            ),
            transaction: $transaction,
            customer: $customer,
            history: $history,
            messages: $messages,
            raw: $payload,
        );
    }
}

<?php

namespace Maxiviper117\Paystack\Data\Refund;

use Carbon\CarbonImmutable;
use Maxiviper117\Paystack\Data\Customer\CustomerData;
use Maxiviper117\Paystack\Data\Transaction\TransactionData;
use Maxiviper117\Paystack\Support\Payload;
use Maxiviper117\Paystack\Support\PaystackDate;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

class RefundData extends Data
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public int|string|null $id,
        public int|string|null $integration,
        public TransactionData|int|string|null $transaction,
        public int|string|null $dispute,
        public int|string|null $settlement,
        public ?string $domain,
        public ?int $amount,
        public ?int $deductedAmount,
        public ?string $currency,
        public ?string $channel,
        public ?bool $fullyDeducted,
        public ?string $refundedBy,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $refundedAt,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $expectedAt,
        public ?string $customerNote,
        public ?string $merchantNote,
        public ?string $status,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $createdAt,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $updatedAt,
        public ?string $bankReference = null,
        public ?string $reason = null,
        public ?CustomerData $customer = null,
        public ?string $initiatedBy = null,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $reversedAt = null,
        public ?string $sessionId = null,
        public array $raw = [],
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        $transactionPayload = Payload::nullableArray($payload, 'transaction');
        $customerPayload = Payload::nullableArray($payload, 'customer');
        $transaction = null;
        $customer = null;

        if ($transactionPayload !== null && ! array_is_list($transactionPayload)) {
            /** @var array<string, mixed> $transactionPayload */
            $transaction = TransactionData::fromPayload($transactionPayload);
        } else {
            $transaction = Payload::intOrStringOrNull($payload, 'transaction');
        }

        if ($customerPayload !== null && ! array_is_list($customerPayload)) {
            /** @var array<string, mixed> $customerPayload */
            $customer = CustomerData::fromPayload($customerPayload);
        }

        $status = Payload::nullableString($payload, 'status');

        return new self(
            id: Payload::intOrStringOrNull($payload, 'id'),
            integration: Payload::intOrStringOrNull($payload, 'integration'),
            transaction: $transaction,
            dispute: Payload::intOrStringOrNull($payload, 'dispute'),
            settlement: Payload::intOrStringOrNull($payload, 'settlement'),
            domain: Payload::nullableString($payload, 'domain'),
            amount: array_key_exists('amount', $payload) ? Payload::int($payload, 'amount') : null,
            deductedAmount: array_key_exists('deducted_amount', $payload) || array_key_exists('deductedAmount', $payload)
                ? Payload::int($payload, array_key_exists('deducted_amount', $payload) ? 'deducted_amount' : 'deductedAmount')
                : null,
            currency: Payload::nullableString($payload, 'currency'),
            channel: Payload::nullableString($payload, 'channel'),
            fullyDeducted: array_key_exists('fully_deducted', $payload) || array_key_exists('fullyDeducted', $payload)
                ? Payload::bool($payload, array_key_exists('fully_deducted', $payload) ? 'fully_deducted' : 'fullyDeducted')
                : null,
            refundedBy: Payload::nullableString($payload, 'refunded_by') ?? Payload::nullableString($payload, 'refundedBy'),
            refundedAt: PaystackDate::nullable(
                Payload::nullableString($payload, 'refunded_at') ?? Payload::nullableString($payload, 'refundedAt')
            ),
            expectedAt: PaystackDate::nullable(
                Payload::nullableString($payload, 'expected_at') ?? Payload::nullableString($payload, 'expectedAt')
            ),
            customerNote: Payload::nullableString($payload, 'customer_note') ?? Payload::nullableString($payload, 'customerNote'),
            merchantNote: Payload::nullableString($payload, 'merchant_note') ?? Payload::nullableString($payload, 'merchantNote'),
            status: $status,
            createdAt: PaystackDate::nullable(
                Payload::nullableString($payload, 'created_at')
                ?? Payload::nullableString($payload, 'createdAt')
            ),
            updatedAt: PaystackDate::nullable(
                Payload::nullableString($payload, 'updated_at')
                ?? Payload::nullableString($payload, 'updatedAt')
            ),
            bankReference: Payload::nullableString($payload, 'bank_reference') ?? Payload::nullableString($payload, 'bankReference'),
            reason: Payload::nullableString($payload, 'reason'),
            customer: $customer,
            initiatedBy: Payload::nullableString($payload, 'initiated_by') ?? Payload::nullableString($payload, 'initiatedBy'),
            reversedAt: PaystackDate::nullable(
                Payload::nullableString($payload, 'reversed_at') ?? Payload::nullableString($payload, 'reversedAt')
            ),
            sessionId: Payload::nullableString($payload, 'session_id') ?? Payload::nullableString($payload, 'sessionId'),
            raw: $payload,
        );
    }
}

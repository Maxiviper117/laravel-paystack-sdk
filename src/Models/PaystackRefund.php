<?php

namespace Maxiviper117\Paystack\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Maxiviper117\Paystack\Data\Customer\CustomerData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested\WebhookCustomerPayloadData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\RefundWebhookData;
use Maxiviper117\Paystack\Data\Refund\RefundData;
use Maxiviper117\Paystack\Data\Transaction\TransactionData;

/**
 * @property int $id
 * @property string|null $paystack_id
 * @property string|null $refund_reference
 * @property string|null $transaction_reference
 * @property string|null $status
 * @property int|null $amount
 * @property int|null $deducted_amount
 * @property string|null $currency
 * @property string|null $channel
 * @property string|null $billable_type
 * @property int|string|null $billable_id
 * @property int|string|null $paystack_transaction_id
 * @property int|string|null $paystack_customer_id
 * @property bool|null $fully_deducted
 * @property CarbonImmutable|null $refunded_at
 * @property CarbonImmutable|null $expected_at
 * @property string|null $processor
 * @property string|null $integration
 * @property string|null $domain
 * @property array<string, mixed>|null $raw_payload
 */
class PaystackRefund extends Model
{
    protected $table = 'paystack_refunds';

    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'fully_deducted' => 'bool',
        'refunded_at' => 'immutable_datetime',
        'expected_at' => 'immutable_datetime',
        'reversed_at' => 'immutable_datetime',
        'raw_payload' => 'array',
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function billable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<PaystackTransaction, $this>
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaystackTransaction::class, 'paystack_transaction_id');
    }

    /**
     * @return BelongsTo<PaystackCustomer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(PaystackCustomer::class, 'paystack_customer_id');
    }

    public static function syncFromRefundData(RefundData $refund): self
    {
        $transactionReference = null;
        if ($refund->transaction instanceof TransactionData) {
            $transactionReference = $refund->transaction->reference;
        } elseif (is_int($refund->transaction) || is_string($refund->transaction)) {
            $transactionReference = (string) $refund->transaction;
        }

        $transactionId = $refund->transaction instanceof TransactionData
            ? PaystackTransaction::syncFromTransactionData($refund->transaction)->getKey()
            : ($transactionReference !== null
                ? PaystackTransaction::query()->where('reference', $transactionReference)->value('id')
                : null);
        $customerId = $refund->customer instanceof CustomerData ? PaystackCustomer::syncFromCustomerData($refund->customer)->getKey() : null;

        /** @var self $record */
        $record = $refund->id !== null
            ? static::query()->firstOrNew(['paystack_id' => (string) $refund->id])
            : static::query()->firstOrNew([
                'refund_reference' => null,
                'transaction_reference' => $transactionReference,
            ]);

        $record->fill([
            'paystack_id' => $refund->id !== null ? (string) $refund->id : null,
            'refund_reference' => $refund->id !== null ? null : $refund->bankReference,
            'transaction_reference' => $transactionReference,
            'paystack_transaction_id' => $transactionId,
            'status' => $refund->status?->value,
            'amount' => $refund->amount,
            'deducted_amount' => $refund->deductedAmount,
            'currency' => $refund->currency,
            'channel' => $refund->channel,
            'fully_deducted' => $refund->fullyDeducted,
            'refunded_by' => $refund->refundedBy,
            'refunded_at' => $refund->refundedAt,
            'expected_at' => $refund->expectedAt,
            'customer_note' => $refund->customerNote,
            'merchant_note' => $refund->merchantNote,
            'bank_reference' => $refund->bankReference,
            'reason' => $refund->reason,
            'initiated_by' => $refund->initiatedBy,
            'reversed_at' => $refund->reversedAt,
            'session_id' => $refund->sessionId,
            'domain' => $refund->domain,
            'processor' => null,
            'integration' => $refund->integration !== null ? (string) $refund->integration : null,
            'paystack_customer_id' => $customerId,
            'raw_payload' => $refund->raw,
        ]);

        $record->save();

        return $record;
    }

    public static function syncFromWebhookRefundData(RefundWebhookData $refund): self
    {
        /** @var self $record */
        $record = $refund->refundReference !== null && trim($refund->refundReference) !== ''
            ? static::query()->firstOrNew([
                'refund_reference' => $refund->refundReference,
            ])
            : static::query()->firstOrNew([
                'transaction_reference' => $refund->transactionReference,
            ]);

        $customerId = $refund->customer instanceof WebhookCustomerPayloadData ? PaystackCustomer::syncFromCustomerData($refund->customer)->getKey() : null;

        $record->fill([
            'refund_reference' => $refund->refundReference,
            'transaction_reference' => $refund->transactionReference,
            'paystack_transaction_id' => PaystackTransaction::query()->where('reference', $refund->transactionReference)->value('id'),
            'status' => $refund->status,
            'amount' => $refund->amount,
            'currency' => $refund->currency,
            'domain' => $refund->domain,
            'processor' => $refund->processor,
            'integration' => $refund->integration !== null ? (string) $refund->integration : null,
            'paystack_customer_id' => $customerId,
            'raw_payload' => $refund->rawData,
        ]);

        $record->save();

        return $record;
    }
}

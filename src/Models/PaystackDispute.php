<?php

namespace Maxiviper117\Paystack\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Maxiviper117\Paystack\Data\Dispute\DisputeCustomerData;
use Maxiviper117\Paystack\Data\Dispute\DisputeData;
use Maxiviper117\Paystack\Data\Dispute\DisputeTransactionData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested\ChargeDisputePayloadData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested\WebhookCustomerPayloadData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested\WebhookTransactionPayloadData;

/**
 * @property int $id
 * @property string|null $paystack_id
 * @property string|null $transaction_reference
 * @property string|null $status
 * @property int|null $refund_amount
 * @property string|null $currency
 * @property string|null $resolution
 * @property string|null $domain
 * @property string|null $category
 * @property string|null $note
 * @property string|null $attachments
 * @property string|null $last4
 * @property string|null $bin
 * @property string|null $billable_type
 * @property int|string|null $billable_id
 * @property int|string|null $paystack_transaction_id
 * @property int|string|null $paystack_customer_id
 * @property string|null $source
 * @property string|null $created_by
 * @property string|null $organization
 * @property string|null $integration
 * @property CarbonImmutable|null $resolved_at
 * @property CarbonImmutable|null $due_at
 * @property array<string, mixed>|null $raw_payload
 */
class PaystackDispute extends Model
{
    protected $table = 'paystack_disputes';

    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'resolved_at' => 'immutable_datetime',
        'due_at' => 'immutable_datetime',
        'created_at_source' => 'immutable_datetime',
        'updated_at_source' => 'immutable_datetime',
        'evidence' => 'array',
        'history' => 'array',
        'messages' => 'array',
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

    public static function syncFromDisputeData(DisputeData $dispute): self
    {
        /** @var self $record */
        $record = static::query()->firstOrNew([
            'paystack_id' => $dispute->id !== null ? (string) $dispute->id : null,
        ]);

        $transactionReference = $dispute->transaction instanceof DisputeTransactionData ? $dispute->transaction->reference : $dispute->transactionReference;
        $transactionId = $dispute->transaction instanceof DisputeTransactionData
            ? PaystackTransaction::syncFromTransactionData($dispute->transaction)->getKey()
            : ($transactionReference !== null
                ? PaystackTransaction::query()->where('reference', $transactionReference)->value('id')
                : null);
        $customerId = $dispute->customer instanceof DisputeCustomerData ? PaystackCustomer::syncFromCustomerData($dispute->customer)->getKey() : null;

        $record->fill([
            'paystack_id' => $dispute->id !== null ? (string) $dispute->id : null,
            'paystack_transaction_id' => $transactionId,
            'paystack_customer_id' => $customerId,
            'refund_amount' => $dispute->refundAmount,
            'currency' => $dispute->currency,
            'status' => $dispute->status?->value,
            'resolution' => $dispute->resolution,
            'domain' => $dispute->domain,
            'category' => $dispute->category,
            'note' => $dispute->note,
            'attachments' => $dispute->attachments,
            'last4' => $dispute->last4,
            'bin' => $dispute->bin,
            'transaction_reference' => $dispute->transactionReference,
            'merchant_transaction_reference' => $dispute->merchantTransactionReference,
            'source' => $dispute->source,
            'created_by' => $dispute->createdBy !== null ? (string) $dispute->createdBy : null,
            'organization' => $dispute->organization !== null ? (string) $dispute->organization : null,
            'integration' => $dispute->integration !== null ? (string) $dispute->integration : null,
            'evidence' => $dispute->evidence,
            'resolved_at' => $dispute->resolvedAt,
            'due_at' => $dispute->dueAt,
            'created_at_source' => $dispute->createdAt,
            'updated_at_source' => $dispute->updatedAt,
            'history' => $dispute->history,
            'messages' => $dispute->messages,
            'raw_payload' => $dispute->raw,
        ]);

        $record->save();

        return $record;
    }

    public static function syncFromWebhookDisputeData(ChargeDisputePayloadData $dispute): self
    {
        /** @var self $record */
        $record = static::query()->firstOrNew([
            'paystack_id' => $dispute->id !== null ? (string) $dispute->id : null,
        ]);

        $transactionReference = $dispute->transaction instanceof WebhookTransactionPayloadData ? $dispute->transaction->reference : $dispute->transactionReference;
        $transactionId = $dispute->transaction instanceof WebhookTransactionPayloadData
            ? PaystackTransaction::syncFromWebhookTransactionData($dispute->transaction)->getKey()
            : ($transactionReference !== null
                ? PaystackTransaction::query()->where('reference', $transactionReference)->value('id')
                : null);
        $customerId = $dispute->customer instanceof WebhookCustomerPayloadData ? PaystackCustomer::syncFromCustomerData($dispute->customer)->getKey() : null;

        $record->fill([
            'paystack_id' => $dispute->id !== null ? (string) $dispute->id : null,
            'paystack_transaction_id' => $transactionId,
            'paystack_customer_id' => $customerId,
            'refund_amount' => $dispute->refundAmount,
            'currency' => $dispute->currency,
            'status' => $dispute->status,
            'resolution' => $dispute->resolution,
            'domain' => $dispute->domain,
            'category' => $dispute->category,
            'note' => $dispute->note,
            'attachments' => $dispute->attachments,
            'last4' => $dispute->last4,
            'bin' => $dispute->bin,
            'transaction_reference' => $dispute->transactionReference,
            'resolved_at' => $dispute->resolvedAt,
            'due_at' => $dispute->dueAt,
            'created_at_source' => $dispute->createdAt,
            'updated_at_source' => $dispute->updatedAt,
            'evidence' => $dispute->evidence,
            'history' => array_map(static fn ($history): array => $history->toArray(), $dispute->history),
            'messages' => array_map(static fn ($message): array => $message->toArray(), $dispute->messages),
            'raw_payload' => $dispute->rawData,
        ]);

        $record->save();

        return $record;
    }
}

<?php

namespace Maxiviper117\Paystack\Models;

use BackedEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Maxiviper117\Paystack\Data\Dispute\DisputeTransactionData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested\WebhookPlanPayloadData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested\WebhookTransactionPayloadData;
use Maxiviper117\Paystack\Data\Plan\PlanData;
use Maxiviper117\Paystack\Data\Transaction\TransactionData;

/**
 * @property int $id
 * @property string|null $paystack_id
 * @property string|null $reference
 * @property string|null $status
 * @property int|null $amount
 * @property string|null $currency
 * @property string|null $channel
 * @property CarbonImmutable|null $paid_at
 * @property string|null $message
 * @property string|null $gateway_response
 * @property string|null $ip_address
 * @property string|null $customer_code
 * @property string|null $billable_type
 * @property int|string|null $billable_id
 * @property int|string|null $paystack_customer_id
 * @property int|string|null $paystack_plan_id
 * @property int|string|null $paystack_subscription_id
 * @property int|null $fees
 * @property string|null $order_id
 * @property int|null $requested_amount
 * @property array<string, mixed>|null $metadata
 * @property array<string, mixed>|null $authorization
 * @property array<string, mixed>|null $log
 * @property array<string, mixed>|null $fees_split
 * @property array<string, mixed>|null $subaccount
 * @property array<string, mixed>|null $split
 * @property array<string, mixed>|null $raw_payload
 */
class PaystackTransaction extends Model
{
    protected $table = 'paystack_transactions';

    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'paid_at' => 'immutable_datetime',
        'metadata' => 'array',
        'authorization' => 'array',
        'log' => 'array',
        'fees' => 'integer',
        'fees_split' => 'array',
        'subaccount' => 'array',
        'split' => 'array',
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
     * @return BelongsTo<PaystackCustomer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(PaystackCustomer::class, 'paystack_customer_id');
    }

    /**
     * @return BelongsTo<PaystackPlan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(PaystackPlan::class, 'paystack_plan_id');
    }

    /**
     * @return BelongsTo<PaystackSubscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(PaystackSubscription::class, 'paystack_subscription_id');
    }

    public static function syncFromTransactionData(
        TransactionData|DisputeTransactionData|WebhookTransactionPayloadData $transaction
    ): self {
        /** @var self $record */
        $record = static::query()->firstOrNew([
            'reference' => $transaction->reference,
        ]);

        $status = $transaction->status instanceof BackedEnum
            ? $transaction->status->value
            : $transaction->status;
        $customer = $transaction->customer;
        $plan = $transaction instanceof TransactionData ? null : $transaction->plan;
        $customerModel = $customer !== null ? PaystackCustomer::syncFromCustomerData($customer) : null;
        $planModel = null;
        if (is_array($plan) && ! array_is_list($plan)) {
            /** @var array<string, mixed> $planPayload */
            $planPayload = $plan;
            $planModel = $transaction instanceof WebhookTransactionPayloadData
                ? PaystackPlan::syncFromWebhookPlanData(WebhookPlanPayloadData::fromPayload($planPayload))
                : PaystackPlan::syncFromPlanData(PlanData::fromPayload($planPayload));
        }

        $message = $transaction instanceof TransactionData ? null : $transaction->message;
        $gatewayResponse = $transaction instanceof TransactionData ? null : $transaction->gatewayResponse;
        $ipAddress = $transaction instanceof TransactionData ? null : $transaction->ipAddress;
        $metadata = $transaction instanceof TransactionData ? null : $transaction->metadata;
        $authorization = $transaction instanceof TransactionData ? null : $transaction->authorization;
        $log = $transaction instanceof TransactionData ? null : $transaction->log;
        $fees = $transaction instanceof TransactionData ? null : $transaction->fees;
        $feesSplit = $transaction instanceof TransactionData ? null : $transaction->feesSplit;
        $subaccount = $transaction instanceof TransactionData ? null : $transaction->subaccount;
        $split = $transaction instanceof TransactionData ? null : $transaction->split;
        $orderId = $transaction instanceof TransactionData ? null : $transaction->orderId;
        $requestedAmount = $transaction instanceof TransactionData ? null : $transaction->requestedAmount;
        $rawPayload = $transaction instanceof WebhookTransactionPayloadData ? $transaction->rawData : $transaction->raw;

        $record->fill([
            'paystack_id' => $transaction->id !== null ? (string) $transaction->id : null,
            'reference' => $transaction->reference,
            'status' => $status,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'channel' => $transaction->channel,
            'paid_at' => $transaction->paidAt,
            'customer_code' => $customer?->customerCode,
            'paystack_customer_id' => $customerModel?->getKey(),
            'paystack_plan_id' => $planModel?->getKey(),
            'message' => $message,
            'gateway_response' => $gatewayResponse,
            'ip_address' => $ipAddress,
            'metadata' => $metadata,
            'authorization' => $authorization,
            'log' => $log,
            'fees' => $fees,
            'fees_split' => $feesSplit,
            'subaccount' => $subaccount,
            'split' => $split,
            'order_id' => $orderId,
            'requested_amount' => $requestedAmount,
            'raw_payload' => $rawPayload,
        ]);

        $record->save();

        return $record;
    }

    public static function syncFromWebhookTransactionData(WebhookTransactionPayloadData $transaction): self
    {
        return static::syncFromTransactionData($transaction);
    }
}

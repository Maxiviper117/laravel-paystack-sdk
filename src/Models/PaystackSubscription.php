<?php

namespace Maxiviper117\Paystack\Models;

use BackedEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested\WebhookPlanPayloadData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested\WebhookSubscriptionPayloadData;
use Maxiviper117\Paystack\Data\Plan\PlanData;
use Maxiviper117\Paystack\Data\Subscription\SubscriptionData;

/**
 * @property int $id
 * @property string|null $paystack_id
 * @property string $name
 * @property string $subscription_code
 * @property string|null $status
 * @property string|null $email_token
 * @property int|null $amount
 * @property string|null $cron_expression
 * @property string|null $plan_code
 * @property string|null $open_invoice
 * @property string|null $billable_type
 * @property int|string|null $billable_id
 * @property int|string|null $paystack_customer_id
 * @property int|string|null $paystack_plan_id
 * @property CarbonImmutable|null $next_payment_date
 * @property array<string, mixed>|null $raw_payload
 */
class PaystackSubscription extends Model
{
    protected $table = 'paystack_subscriptions';

    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'next_payment_date' => 'immutable_datetime',
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

    public static function syncForBillable(
        Model $billable,
        SubscriptionData $subscription,
        string $name = 'default',
        ?PaystackCustomer $customer = null,
    ): self {
        return static::syncFromSubscriptionData($subscription, $name, $billable, $customer);
    }

    public static function syncFromSubscriptionData(
        SubscriptionData|WebhookSubscriptionPayloadData $subscription,
        string $name = 'default',
        ?Model $billable = null,
        ?PaystackCustomer $customer = null,
        ?PaystackPlan $plan = null,
    ): self {
        /** @var self $record */
        $record = static::query()->firstOrNew([
            'subscription_code' => $subscription->subscriptionCode,
        ]);

        if ($billable instanceof Model) {
            $record->billable()->associate($billable);
        }

        $planCode = $subscription->plan?->planCode;
        $planModel = $plan;
        if (! $planModel instanceof PaystackPlan && $subscription->plan !== null) {
            if ($subscription instanceof WebhookSubscriptionPayloadData) {
                /** @var WebhookPlanPayloadData $webhookPlan */
                $webhookPlan = $subscription->plan;
                $planModel = PaystackPlan::syncFromWebhookPlanData($webhookPlan);
            } else {
                /** @var PlanData $planData */
                $planData = $subscription->plan;
                $planModel = PaystackPlan::syncFromPlanData($planData);
            }
        }

        if (! $planModel instanceof PaystackPlan && $planCode !== null) {
            $planModel = PaystackPlan::query()->where('plan_code', $planCode)->first();
        }

        $customerModel = $customer ?? ($subscription->customer !== null ? PaystackCustomer::syncFromCustomerData($subscription->customer) : null);
        $amount = $subscription instanceof WebhookSubscriptionPayloadData ? $subscription->amount : null;
        $cronExpression = $subscription instanceof WebhookSubscriptionPayloadData ? $subscription->cronExpression : null;
        $rawPayload = $subscription instanceof WebhookSubscriptionPayloadData ? $subscription->rawData : $subscription->raw;

        $record->fill([
            'paystack_id' => $subscription->id !== null ? (string) $subscription->id : null,
            'paystack_customer_id' => $customerModel?->getKey(),
            'paystack_plan_id' => $planModel?->getKey(),
            'name' => $name,
            'subscription_code' => $subscription->subscriptionCode,
            'status' => $subscription->status instanceof BackedEnum
                ? $subscription->status->value
                : $subscription->status,
            'email_token' => $subscription->emailToken,
            'amount' => $amount,
            'cron_expression' => $cronExpression,
            'plan_code' => $planCode,
            'open_invoice' => $subscription->openInvoice !== null ? (string) $subscription->openInvoice : null,
            'next_payment_date' => $subscription->nextPaymentDate,
            'raw_payload' => $rawPayload,
        ]);

        $record->save();

        return $record;
    }
}

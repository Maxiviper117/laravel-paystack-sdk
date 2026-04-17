<?php

namespace Maxiviper117\Paystack\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested\WebhookPlanPayloadData;
use Maxiviper117\Paystack\Data\Plan\PlanData;

/**
 * @property int $id
 * @property string|null $paystack_id
 * @property string|null $plan_code
 * @property string|null $name
 * @property int|null $amount
 * @property string|null $interval
 * @property string|null $description
 * @property string|null $currency
 * @property int|null $invoice_limit
 * @property bool|null $send_invoices
 * @property bool|null $send_sms
 * @property string|null $billable_type
 * @property int|string|null $billable_id
 * @property array<string, mixed>|null $payload_snapshot
 */
class PaystackPlan extends Model
{
    protected $table = 'paystack_plans';

    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'send_invoices' => 'bool',
        'send_sms' => 'bool',
        'payload_snapshot' => 'array',
    ];

    /**
     * @return HasMany<PaystackSubscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(PaystackSubscription::class, 'paystack_plan_id');
    }

    /**
     * @return HasMany<PaystackTransaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(PaystackTransaction::class, 'paystack_plan_id');
    }

    public static function syncFromPlanData(PlanData|WebhookPlanPayloadData $plan): self
    {
        /** @var self $record */
        $record = static::query()->firstOrNew([
            'plan_code' => $plan->planCode,
        ]);

        $record->fill([
            'paystack_id' => $plan->id !== null ? (string) $plan->id : null,
            'plan_code' => $plan->planCode,
            'name' => $plan->name,
            'amount' => $plan->amount,
            'interval' => $plan->interval,
            'description' => $plan->description,
            'currency' => $plan->currency,
            'invoice_limit' => $plan->invoiceLimit,
            'send_invoices' => $plan->sendInvoices,
            'send_sms' => $plan->sendSms,
            'payload_snapshot' => self::payloadSnapshot($plan instanceof WebhookPlanPayloadData ? $plan->rawData : $plan->raw),
        ]);

        $record->save();

        return $record;
    }

    public static function syncFromWebhookPlanData(WebhookPlanPayloadData $plan): self
    {
        /** @var self $record */
        $record = static::query()->firstOrNew([
            'plan_code' => $plan->planCode,
        ]);

        $record->fill([
            'paystack_id' => $plan->id !== null ? (string) $plan->id : null,
            'plan_code' => $plan->planCode,
            'name' => $plan->name,
            'amount' => $plan->amount,
            'interval' => $plan->interval,
            'description' => $plan->description,
            'currency' => $plan->currency,
            'invoice_limit' => $plan->invoiceLimit,
            'send_invoices' => $plan->sendInvoices,
            'send_sms' => $plan->sendSms,
            'payload_snapshot' => self::payloadSnapshot($plan->rawData),
        ]);

        $record->save();

        return $record;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private static function payloadSnapshot(array $payload): array
    {
        $snapshot = $payload;

        if (isset($snapshot['subscriptions']) && is_array($snapshot['subscriptions'])) {
            $snapshot['subscriptions'] = array_slice(array_values($snapshot['subscriptions']), 0, 1);
        }

        return $snapshot;
    }
}

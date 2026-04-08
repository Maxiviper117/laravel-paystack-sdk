<?php

namespace Maxiviper117\Paystack\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Maxiviper117\Paystack\Data\Subscription\SubscriptionData;

/**
 * @property int $id
 * @property string $name
 * @property string $subscription_code
 * @property string|null $status
 * @property string|null $email_token
 * @property string|null $plan_code
 * @property string|null $open_invoice
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

    public static function syncForBillable(
        Model $billable,
        SubscriptionData $subscription,
        string $name = 'default',
        ?PaystackCustomer $customer = null,
    ): self {
        /** @var self $record */
        $record = static::query()->firstOrNew([
            'billable_type' => $billable->getMorphClass(),
            'billable_id' => $billable->getKey(),
            'name' => $name,
        ]);

        $record->fill([
            'paystack_customer_id' => $customer?->getKey(),
            'name' => $name,
            'subscription_code' => $subscription->subscriptionCode,
            'status' => $subscription->status,
            'email_token' => $subscription->emailToken,
            'plan_code' => $subscription->plan?->planCode,
            'open_invoice' => $subscription->openInvoice !== null ? (string) $subscription->openInvoice : null,
            'next_payment_date' => $subscription->nextPaymentDate,
            'raw_payload' => $subscription->raw,
        ]);

        $record->save();

        return $record;
    }
}

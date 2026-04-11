<?php

namespace Maxiviper117\Paystack\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Maxiviper117\Paystack\Data\Customer\CustomerData;
use Maxiviper117\Paystack\Data\Dispute\DisputeCustomerData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\Nested\WebhookCustomerPayloadData;

/**
 * @property int $id
 * @property string|null $customer_code
 * @property string|null $billable_type
 * @property int|string|null $billable_id
 * @property string $email
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $phone
 * @property string|null $risk_action
 * @property string|null $international_format_phone
 * @property array<mixed>|null $metadata
 * @property array<string, mixed>|null $raw_payload
 */
class PaystackCustomer extends Model
{
    protected $table = 'paystack_customers';

    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
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
     * @return HasMany<PaystackSubscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(PaystackSubscription::class);
    }

    /**
     * @return HasMany<PaystackTransaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(PaystackTransaction::class);
    }

    /**
     * @return HasMany<PaystackRefund, $this>
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(PaystackRefund::class);
    }

    /**
     * @return HasMany<PaystackDispute, $this>
     */
    public function disputes(): HasMany
    {
        return $this->hasMany(PaystackDispute::class);
    }

    public static function syncForBillable(Model $billable, CustomerData $customer): self
    {
        return static::syncFromCustomerData($customer, $billable);
    }

    public static function syncFromCustomerData(CustomerData|DisputeCustomerData|WebhookCustomerPayloadData $customer, ?Model $billable = null): self
    {
        /** @var self $record */
        $record = $customer->customerCode !== null && trim($customer->customerCode) !== ''
            ? static::query()->firstOrNew([
                'customer_code' => $customer->customerCode,
            ])
            : ($billable instanceof Model
                ? static::query()->firstOrNew([
                    'billable_type' => $billable->getMorphClass(),
                    'billable_id' => $billable->getKey(),
                ])
                : static::query()->firstOrNew([
                    'email' => $customer->email,
                ]));

        if ($billable instanceof Model) {
            $record->billable()->associate($billable);
        }

        $record->fill([
            'customer_code' => $customer->customerCode,
            'email' => $customer->email,
            'first_name' => $customer->firstName,
            'last_name' => $customer->lastName,
            'phone' => $customer->phone,
            'metadata' => $customer->metadata,
            'risk_action' => property_exists($customer, 'riskAction') ? $customer->riskAction : null,
            'international_format_phone' => property_exists($customer, 'internationalFormatPhone') ? $customer->internationalFormatPhone : null,
            'raw_payload' => $customer instanceof WebhookCustomerPayloadData ? $customer->rawData : $customer->raw,
        ]);

        $record->save();

        return $record;
    }
}

<?php

namespace Maxiviper117\Paystack\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Maxiviper117\Paystack\Data\Customer\CustomerData;

/**
 * @property int $id
 * @property string|null $customer_code
 * @property string $email
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $phone
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

    public static function syncForBillable(Model $billable, CustomerData $customer): self
    {
        /** @var self $record */
        $record = static::query()->firstOrNew([
            'billable_type' => $billable->getMorphClass(),
            'billable_id' => $billable->getKey(),
        ]);

        $record->fill([
            'customer_code' => $customer->customerCode,
            'email' => $customer->email,
            'first_name' => $customer->firstName,
            'last_name' => $customer->lastName,
            'phone' => $customer->phone,
            'metadata' => $customer->metadata,
            'raw_payload' => $customer->raw,
        ]);

        $record->save();

        return $record;
    }
}

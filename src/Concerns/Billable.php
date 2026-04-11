<?php

namespace Maxiviper117\Paystack\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Maxiviper117\Paystack\Billing\BillableCustomerLifecycleService;
use Maxiviper117\Paystack\Data\Dispute\DisputeData;
use Maxiviper117\Paystack\Data\Plan\PlanData;
use Maxiviper117\Paystack\Data\Refund\RefundData;
use Maxiviper117\Paystack\Data\Subscription\SubscriptionData;
use Maxiviper117\Paystack\Data\Transaction\TransactionData;
use Maxiviper117\Paystack\Models\PaystackCustomer;
use Maxiviper117\Paystack\Models\PaystackDispute;
use Maxiviper117\Paystack\Models\PaystackPlan;
use Maxiviper117\Paystack\Models\PaystackRefund;
use Maxiviper117\Paystack\Models\PaystackSubscription;
use Maxiviper117\Paystack\Models\PaystackTransaction;

/**
 * @mixin Model
 */
trait Billable
{
    /**
     * @return MorphOne<PaystackCustomer, $this>
     */
    public function paystackCustomer(): MorphOne
    {
        return $this->morphOne(PaystackCustomer::class, 'billable');
    }

    /**
     * @return MorphMany<PaystackSubscription, $this>
     */
    public function paystackSubscriptions(): MorphMany
    {
        return $this->morphMany(PaystackSubscription::class, 'billable');
    }

    /**
     * @return MorphMany<PaystackTransaction, $this>
     */
    public function paystackTransactions(): MorphMany
    {
        return $this->morphMany(PaystackTransaction::class, 'billable');
    }

    /**
     * @return MorphMany<PaystackRefund, $this>
     */
    public function paystackRefunds(): MorphMany
    {
        return $this->morphMany(PaystackRefund::class, 'billable');
    }

    /**
     * @return MorphMany<PaystackDispute, $this>
     */
    public function paystackDisputes(): MorphMany
    {
        return $this->morphMany(PaystackDispute::class, 'billable');
    }

    public function hasPaystackCustomer(): bool
    {
        return $this->paystackCustomer()->exists();
    }

    public function hasPaystackSubscription(string $name = 'default'): bool
    {
        return $this->paystackSubscriptions()->where('name', $name)->exists();
    }

    public function paystackCustomerCode(): ?string
    {
        return $this->storedPaystackCustomer()?->customer_code;
    }

    public function paystackSubscription(string $name = 'default'): ?PaystackSubscription
    {
        /** @var PaystackSubscription|null $subscription */
        $subscription = $this->paystackSubscriptions()
            ->where('name', $name)
            ->first();

        return $subscription;
    }

    public function paystackPlan(string $planCode): ?PaystackPlan
    {
        return PaystackPlan::query()->where('plan_code', $planCode)->first();
    }

    public function paystackTransaction(string|int $referenceOrId): ?PaystackTransaction
    {
        /** @var PaystackTransaction|null $transaction */
        $transaction = $this->paystackTransactions()
            ->where(function ($query) use ($referenceOrId): void {
                $query->where('reference', (string) $referenceOrId)
                    ->orWhere('paystack_id', (string) $referenceOrId);
            })
            ->first();

        return $transaction;
    }

    public function paystackRefund(string|int $referenceOrId): ?PaystackRefund
    {
        /** @var PaystackRefund|null $refund */
        $refund = $this->paystackRefunds()
            ->where(function ($query) use ($referenceOrId): void {
                $query->where('refund_reference', (string) $referenceOrId)
                    ->orWhere('paystack_id', (string) $referenceOrId);
            })
            ->first();

        return $refund;
    }

    public function paystackDispute(string|int $referenceOrId): ?PaystackDispute
    {
        /** @var PaystackDispute|null $dispute */
        $dispute = $this->paystackDisputes()
            ->where(function ($query) use ($referenceOrId): void {
                $query->where('paystack_id', (string) $referenceOrId)
                    ->orWhere('transaction_reference', (string) $referenceOrId);
            })
            ->first();

        return $dispute;
    }

    public function syncPaystackCustomer(): PaystackCustomer
    {
        return app(BillableCustomerLifecycleService::class)->sync($this);
    }

    public function syncPaystackPlan(PlanData $plan): PaystackPlan
    {
        return PaystackPlan::syncFromPlanData($plan);
    }

    public function syncPaystackSubscription(SubscriptionData $subscription, string $name = 'default'): PaystackSubscription
    {
        return PaystackSubscription::syncFromSubscriptionData($subscription, $name, $this);
    }

    public function syncPaystackTransaction(TransactionData $transaction): PaystackTransaction
    {
        $record = PaystackTransaction::syncFromTransactionData($transaction);
        $record->billable()->associate($this);
        $record->save();

        return $record;
    }

    public function syncPaystackRefund(RefundData $refund): PaystackRefund
    {
        $record = PaystackRefund::syncFromRefundData($refund);
        $record->billable()->associate($this);
        $record->save();

        return $record;
    }

    public function syncPaystackDispute(DisputeData $dispute): PaystackDispute
    {
        $record = PaystackDispute::syncFromDisputeData($dispute);
        $record->billable()->associate($this);
        $record->save();

        return $record;
    }

    protected function storedPaystackCustomer(): ?PaystackCustomer
    {
        /** @var PaystackCustomer|null $customer */
        $customer = $this->paystackCustomer()->first();

        return $customer;
    }
}

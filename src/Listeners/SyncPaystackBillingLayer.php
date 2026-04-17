<?php

namespace Maxiviper117\Paystack\Listeners;

use Illuminate\Support\Facades\Schema;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\ChargeDisputeWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\ChargeSuccessWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\PaystackTypedWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\RefundWebhookData;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\SubscriptionWebhookData;
use Maxiviper117\Paystack\Events\PaystackWebhookReceived;
use Maxiviper117\Paystack\Models\PaystackDispute;
use Maxiviper117\Paystack\Models\PaystackRefund;
use Maxiviper117\Paystack\Models\PaystackSubscription;
use Maxiviper117\Paystack\Models\PaystackTransaction;

class SyncPaystackBillingLayer
{
    public function handle(PaystackWebhookReceived $event): void
    {
        $typed = $event->event->typedData();

        if (! $typed instanceof PaystackTypedWebhookData) {
            return;
        }

        if ($typed instanceof ChargeSuccessWebhookData) {
            if (! $this->hasTables(['paystack_customers', 'paystack_transactions'])) {
                return;
            }

            PaystackTransaction::syncFromTransactionData($typed->transaction);

            return;
        }

        if ($typed instanceof SubscriptionWebhookData) {
            if (! $this->hasTables(['paystack_customers', 'paystack_plans', 'paystack_subscriptions'])) {
                return;
            }

            PaystackSubscription::syncFromSubscriptionData($typed->subscription);

            return;
        }

        if ($typed instanceof RefundWebhookData) {
            if (! $this->hasTables(['paystack_customers', 'paystack_transactions', 'paystack_refunds'])) {
                return;
            }

            PaystackRefund::syncFromWebhookRefundData($typed);

            return;
        }

        if ($typed instanceof ChargeDisputeWebhookData) {
            if (! $this->hasTables(['paystack_customers', 'paystack_transactions', 'paystack_disputes'])) {
                return;
            }

            PaystackDispute::syncFromWebhookDisputeData($typed->dispute);
        }
    }

    /**
     * @param  array<int, string>  $tables
     */
    private function hasTables(array $tables): bool
    {
        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }
}

<?php

namespace Maxiviper117\Paystack\Data\Output\Webhook\Typed;

use Maxiviper117\Paystack\Data\Customer\CustomerData;
use Maxiviper117\Paystack\Data\Transaction\TransactionData;
use Spatie\LaravelData\Data;

class ChargeSuccessWebhookData extends Data implements PaystackTypedWebhookData
{
    /**
     * @param  array<string, mixed>  $rawData
     */
    public function __construct(
        public string $event,
        public string $reference,
        public string $status,
        public int $amount,
        public ?string $domain,
        public ?string $currency,
        public ?string $paidAt,
        public ?string $channel,
        public ?string $gatewayResponse,
        public ?CustomerData $customer,
        public TransactionData $transaction,
        public array $rawData = [],
    ) {}
}

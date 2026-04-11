<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Maxiviper117\Paystack\Data\Output\Webhook\PaystackWebhookEventData;
use Maxiviper117\Paystack\Models\PaystackWebhookCall;

class PaystackWebhookReceived
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public PaystackWebhookCall $webhookCall,
        public PaystackWebhookEventData $event,
    ) {}
}

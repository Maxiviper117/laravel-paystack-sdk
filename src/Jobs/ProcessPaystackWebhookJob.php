<?php

namespace Maxiviper117\Paystack\Jobs;

use Maxiviper117\Paystack\Events\PaystackWebhookReceived;
use Maxiviper117\Paystack\Models\PaystackWebhookCall;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;

class ProcessPaystackWebhookJob extends ProcessWebhookJob
{
    public function __construct(PaystackWebhookCall $webhookCall)
    {
        parent::__construct($webhookCall);

        $connection = config('paystack.webhooks.connection');
        $queue = config('paystack.webhooks.queue');

        if (is_string($connection) && $connection !== '') {
            $this->onConnection($connection);
        }

        if (is_string($queue) && $queue !== '') {
            $this->onQueue($queue);
        }
    }

    public function handle(): void
    {
        /** @var PaystackWebhookCall $webhookCall */
        $webhookCall = $this->webhookCall;

        event(new PaystackWebhookReceived(
            webhookCall: $webhookCall,
            event: $webhookCall->eventData(),
        ));
    }
}

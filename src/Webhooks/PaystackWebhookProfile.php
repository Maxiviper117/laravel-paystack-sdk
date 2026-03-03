<?php

namespace Maxiviper117\Paystack\Webhooks;

use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookProfile\WebhookProfile;

class PaystackWebhookProfile implements WebhookProfile
{
    public function shouldProcess(Request $request): bool
    {
        return true;
    }
}

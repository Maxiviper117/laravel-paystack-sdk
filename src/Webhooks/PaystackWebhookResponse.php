<?php

namespace Maxiviper117\Paystack\Webhooks;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookResponse\RespondsToWebhook;
use Symfony\Component\HttpFoundation\Response;

class PaystackWebhookResponse implements RespondsToWebhook
{
    public function respondToValidWebhook(Request $request, WebhookConfig $config): Response
    {
        return new JsonResponse([
            'received' => true,
        ]);
    }
}

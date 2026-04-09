<?php

namespace Maxiviper117\Paystack\Models;

use Illuminate\Http\Request;
use Maxiviper117\Paystack\Data\Output\Webhook\PaystackWebhookEventData;
use Maxiviper117\Paystack\Support\Webhooks\PaystackWebhook;
use Override;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\WebhookConfig;

class PaystackWebhookCall extends WebhookCall
{
    protected $table = 'webhook_calls';

    #[Override]
    public static function storeWebhook(WebhookConfig $config, Request $request): WebhookCall
    {
        $headers = self::headersToStore($config, $request);

        return self::create([
            'name' => $config->name,
            'url' => $request->fullUrl(),
            'headers' => $headers,
            'payload' => [
                'raw_body' => $request->getContent(),
                'input' => self::buildPayloadFromRequest($request),
            ],
            'exception' => null,
        ]);
    }

    public function rawBody(): string
    {
        $payload = $this->payload ?? [];
        $rawBody = $payload['raw_body'] ?? '';

        return is_string($rawBody) ? $rawBody : '';
    }

    /**
     * @return array<string, mixed>
     */
    public function inputPayload(): array
    {
        $payload = $this->payload ?? [];
        $inputPayload = $payload['input'] ?? [];

        if (! is_array($inputPayload) || array_is_list($inputPayload)) {
            return [];
        }

        /** @var array<string, mixed> $inputPayload */
        return $inputPayload;
    }

    public function eventData(): PaystackWebhookEventData
    {
        return PaystackWebhookEventData::fromPayload(
            PaystackWebhook::decodePayload($this->rawBody())
        );
    }
}

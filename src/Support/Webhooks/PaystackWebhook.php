<?php

namespace Maxiviper117\Paystack\Support\Webhooks;

use JsonException;
use Maxiviper117\Paystack\Exceptions\MalformedWebhookPayloadException;

class PaystackWebhook
{
    /**
     * @return array<string, mixed>
     */
    public static function decodePayload(string $payload): array
    {
        try {
            $decoded = json_decode($payload, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            throw new MalformedWebhookPayloadException('The Paystack webhook payload is not valid JSON.', 0, $jsonException);
        }

        if (! is_array($decoded) || array_is_list($decoded)) {
            throw new MalformedWebhookPayloadException('The Paystack webhook payload must decode to an object.');
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    public static function inferResourceType(string $event): string
    {
        $segments = explode('.', $event, 2);

        return $segments[0] !== '' ? $segments[0] : $event;
    }
}

<?php

namespace Maxiviper117\Paystack\Support\Webhooks;

use JsonException;
use Maxiviper117\Paystack\Exceptions\InvalidWebhookSignatureException;
use Maxiviper117\Paystack\Exceptions\MalformedWebhookPayloadException;

class PaystackWebhook
{
    public static function verifySignature(string $payload, string $secretKey, string $signature): void
    {
        $expectedSignature = hash_hmac('sha512', $payload, $secretKey);

        if (! hash_equals($expectedSignature, $signature)) {
            throw new InvalidWebhookSignatureException('The Paystack webhook signature is invalid.');
        }
    }

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

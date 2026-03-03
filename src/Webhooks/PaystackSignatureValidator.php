<?php

namespace Maxiviper117\Paystack\Webhooks;

use Illuminate\Http\Request;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

class PaystackSignatureValidator implements SignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        $secret = config('paystack.webhooks.signing_secret', '');
        if ($secret === '') {
            $secret = config('paystack.secret_key', $config->signingSecret);
        }

        if (! is_string($secret) || $secret === '') {
            return false;
        }

        $signature = $request->headers->get($config->signatureHeaderName);

        if (! is_string($signature) || trim($signature) === '') {
            $serverKey = 'HTTP_'.strtoupper(str_replace('-', '_', $config->signatureHeaderName));
            $signature = $request->server($serverKey);
        }

        if (! is_string($signature) || trim($signature) === '') {
            return false;
        }

        $expectedSignature = hash_hmac('sha512', $request->getContent(), $secret);

        return hash_equals($expectedSignature, $signature);
    }
}

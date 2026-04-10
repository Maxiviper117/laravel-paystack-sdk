<?php

namespace Maxiviper117\Paystack\Webhooks;

use Illuminate\Http\Request;
use Maxiviper117\Paystack\Support\Webhooks\PaystackWebhookIpAllowlist;
use Spatie\WebhookClient\WebhookProfile\WebhookProfile;
use Symfony\Component\HttpFoundation\IpUtils;

class PaystackWebhookProfile implements WebhookProfile
{
    public function shouldProcess(Request $request): bool
    {
        $allowedIps = PaystackWebhookIpAllowlist::fromConfig(
            config('paystack.webhooks.allowed_ips')
        );

        if ($allowedIps === []) {
            return true;
        }

        $requestIp = $request->ip();

        if (! is_string($requestIp) || trim($requestIp) === '') {
            return false;
        }

        return IpUtils::checkIp($requestIp, $allowedIps);
    }
}

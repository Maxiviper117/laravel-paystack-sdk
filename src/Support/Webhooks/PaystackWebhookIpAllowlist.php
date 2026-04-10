<?php

namespace Maxiviper117\Paystack\Support\Webhooks;

/**
 * This class provides functionality to normalize and manage the allowlist of IP addresses for Paystack webhooks.
 *
 * It allows for flexible configuration of the IP allowlist, including using the default Paystack IPs, disabling the allowlist,
 * or providing a custom list of IP addresses. The normalization process ensures that the resulting list of IPs is clean and unique.
 *
 * @method static list<string> fromConfig(mixed $configuredIps) Normalize the configured webhook IP allowlist.
 */
final class PaystackWebhookIpAllowlist
{
    /**
     * @var list<string>
     */
    private const array DEFAULT_IPS = [
        '52.31.139.75',
        '52.49.173.169',
        '52.214.14.220',
    ];

    /**
     * Normalize the configured webhook IP allowlist.
     *
     * Passing `null` uses the documented Paystack webhook IP addresses.
     * Passing an empty string or `false` disables the allowlist check.
     *
     * @return list<string>
     */
    public static function fromConfig(mixed $configuredIps): array
    {
        if ($configuredIps === null) {
            return self::DEFAULT_IPS;
        }

        if (\is_bool($configuredIps)) {
            return $configuredIps ? self::DEFAULT_IPS : [];
        }

        if (! \is_array($configuredIps) && ! \is_string($configuredIps)) {
            return self::DEFAULT_IPS;
        }

        if (\is_string($configuredIps)) {
            $configuredIps = trim($configuredIps);

            if ($configuredIps === '') {
                return [];
            }

            $configuredIps = explode(',', $configuredIps);
        }

        $ips = [];

        foreach ($configuredIps as $configuredIp) {
            if (! \is_string($configuredIp)) {
                continue;
            }

            $configuredIp = trim($configuredIp);

            if ($configuredIp !== '') {
                $ips[] = $configuredIp;
            }
        }

        return array_values(array_unique($ips));
    }
}

<?php

namespace Maxiviper117\Paystack\Support\Webhooks;

/**
 * Normalise and manage the configured Paystack webhook IP allowlist.
 *
 * Paystack sends webhook requests from a small set of documented IPv4 addresses. This
 * helper accepts a variety of configuration shapes (null, bool, string, array) and
 * returns a clean, unique list of IP address strings appropriate for use in an
 * allowlist check.
 *
 * Behaviour summary:
 * - `null` will return the documented Paystack default IPs.
 * - `false` will disable the allowlist (returns an empty array); `true` returns the defaults.
 * - An empty string disables the allowlist. A non-empty CSV string will be split on commas.
 * - An array will be trimmed, filtered for non-empty strings, and de-duplicated.
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

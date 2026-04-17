<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Support;

/**
 * Helper utilities for safely extracting and normalising typed values from array payloads.
 *
 * This class is used throughout the package to read values from API responses, webhook
 * payloads and configuration arrays in a concise and consistent way. Each method accepts
 * an array payload and a key name and returns a typed value with sensible defaults and
 * lightweight conversions (for example numeric strings cast to ints, floats converted to
 * ints for ID fields, and numeric strings interpreted for booleans).
 *
 * Common call sites include:
 * - service provider configuration loading (e.g. `PaystackServiceProvider`)
 * - webhook mappers under `Support\\Webhooks\\Mappers` (normalising webhook bodies)
 * - data/DTO constructors such as `Data/*` classes that shape API responses
 *
 * Methods:
 * - string(array $payload, string $key, string $default = ''): string
 * - nullableString(array $payload, string $key): ?string
 * - int(array $payload, string $key, int $default = 0): int
 * - bool(array $payload, string $key, bool $default = false): bool
 * - nullableArray(array $payload, string $key): ?array
 * - intOrStringOrNull(array $payload, string $key): int|string|null
 */
class Payload
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public static function string(array $payload, string $key, string $default = ''): string
    {
        // reminder what are scalars: string, int, float, bool - we allow numeric strings to be cast to strings, but reject arrays and objects

        $value = $payload[$key] ?? $default;

        return \is_scalar($value) ? (string) $value : $default;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function nullableString(array $payload, string $key): ?string
    {
        $value = $payload[$key] ?? null;

        return \is_scalar($value) ? (string) $value : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function int(array $payload, string $key, int $default = 0): int
    {
        $value = $payload[$key] ?? $default;

        return \is_int($value) ? $value : (\is_numeric($value) ? (int) $value : $default);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function bool(array $payload, string $key, bool $default = false): bool
    {
        $value = $payload[$key] ?? $default;

        if (\is_bool($value)) {
            return $value;
        }

        if (\is_int($value)) {
            return $value !== 0;
        }

        if (\is_string($value) && \is_numeric($value)) {
            return (int) $value !== 0;
        }

        return $default;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<mixed>|null
     */
    public static function nullableArray(array $payload, string $key): ?array
    {
        $value = $payload[$key] ?? null;

        return \is_array($value) ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function intOrStringOrNull(array $payload, string $key): int|string|null
    {
        $value = $payload[$key] ?? null;

        if (\is_int($value) || \is_string($value)) {
            return $value;
        }

        if (\is_float($value)) {
            return (int) $value;
        }

        return null;
    }
}

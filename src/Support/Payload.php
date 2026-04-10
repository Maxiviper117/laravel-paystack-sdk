<?php

namespace Maxiviper117\Paystack\Support;

class Payload
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public static function string(array $payload, string $key, string $default = ''): string
    {
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

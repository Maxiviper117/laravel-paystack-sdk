<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Support;

use Carbon\CarbonImmutable;
use InvalidArgumentException;
use Throwable;

/**
 * Helper for parsing Paystack date/time strings into CarbonImmutable instances.
 *
 * Paystack returns many date/time fields as strings in API responses and webhook
 * payloads. This helper centralises parsing logic and provides a single nullable
 * entrypoint that returns a typed CarbonImmutable instance or null when the input
 * is null. If parsing fails the method throws an InvalidArgumentException with
 * the original exception set as the previous exception to preserve the underlying
 * parsing error context.
 *
 * Typical usage:
 * - `PaystackDate::nullable($payload['created_at'] ?? null)` in mappers and DTOs.
 *
 * Exceptions:
 * - Throws `InvalidArgumentException` when the provided non-null string is not a
 *   valid date-time representation.
 *
 * @internal Keep parsing behaviour consistent across the package.
 */
class PaystackDate
{
    public static function nullable(?string $value): ?CarbonImmutable
    {
        if ($value === null) {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (Throwable $throwable) {
            throw new InvalidArgumentException(
                sprintf('The Paystack date value [%s] is not a valid date-time string.', $value),
                0,
                $throwable,
            );
        }
    }
}

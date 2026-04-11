<?php

namespace Maxiviper117\Paystack\Support;

use InvalidArgumentException;

/**
 * Utility for converting monetary amounts to subunits.
 *
 * This class provides helpers to convert amounts expressed in major currency
 * units (e.g. naira, naira decimals) into their smallest integer subunit
 * representation (e.g. kobo). Conversions assume two decimal places.
 */
class Amount
{
    /**
     * Convert an amount in major units to subunits (e.g. 10.50 => 1050).
     *
     * Accepts integers, floats or numeric strings and returns the equivalent
     * integer number of subunits. The method validates that the input is
     * numeric and non-negative.
     *
     * Examples:
     *   Amount::toSubunit(10);      // 1000
     *   Amount::toSubunit(10.50);   // 1050
     *   Amount::toSubunit('10.50'); // 1050
     *
     * @param  int|float|string  $amount  The amount in major currency units.
     * @return int The amount in subunits.
     *
     * @throws InvalidArgumentException If the amount is non-numeric or negative.
     */
    public static function toSubunit(int|float|string $amount): int
    {
        if (! is_numeric($amount)) {
            throw new InvalidArgumentException('The amount must be numeric.');
        }

        $value = (float) $amount;

        if ($value < 0) {
            throw new InvalidArgumentException('The amount must be zero or greater.');
        }

        return (int) round($value * 100);
    }
}

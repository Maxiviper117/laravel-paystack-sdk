<?php

namespace Maxiviper117\Paystack\Support;

use InvalidArgumentException;

class Amount
{
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

<?php

namespace Maxiviper117\Paystack\Support;

use Carbon\CarbonImmutable;
use InvalidArgumentException;
use Throwable;

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

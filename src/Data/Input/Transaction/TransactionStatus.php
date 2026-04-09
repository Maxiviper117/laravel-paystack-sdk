<?php

namespace Maxiviper117\Paystack\Data\Input\Transaction;

enum TransactionStatus: string
{
    case Failed = 'failed';
    case Success = 'success';
    case Abandoned = 'abandoned';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $status): string => $status->value, self::cases());
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $status) {
            $options[$status->value] = ucfirst($status->value);
        }

        return $options;
    }
}

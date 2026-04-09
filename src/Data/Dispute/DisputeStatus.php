<?php

namespace Maxiviper117\Paystack\Data\Dispute;

enum DisputeStatus: string
{
    case AwaitingMerchantFeedback = 'awaiting-merchant-feedback';
    case AwaitingBankFeedback = 'awaiting-bank-feedback';
    case Pending = 'pending';
    case Resolved = 'resolved';

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
        return [
            self::AwaitingMerchantFeedback->value => 'awaiting-merchant-feedback',
            self::AwaitingBankFeedback->value => 'awaiting-bank-feedback',
            self::Pending->value => 'pending',
            self::Resolved->value => 'resolved',
        ];
    }
}

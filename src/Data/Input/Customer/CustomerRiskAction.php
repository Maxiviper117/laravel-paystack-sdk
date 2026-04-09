<?php

namespace Maxiviper117\Paystack\Data\Input\Customer;

enum CustomerRiskAction: string
{
    case Default = 'default';
    case Allow = 'allow';
    case Deny = 'deny';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $action): string => $action->value, self::cases());
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::Default->value => 'default',
            self::Allow->value => 'allow',
            self::Deny->value => 'deny',
        ];
    }
}

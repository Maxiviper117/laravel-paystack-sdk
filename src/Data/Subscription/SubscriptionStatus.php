<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Data\Subscription;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case NonRenewing = 'non-renewing';
    case Attention = 'attention';
    case Complete = 'complete';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}

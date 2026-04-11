<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Data\Refund;

enum RefundStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case NeedsAttention = 'needs-attention';
    case Failed = 'failed';
    case Processed = 'processed';
}

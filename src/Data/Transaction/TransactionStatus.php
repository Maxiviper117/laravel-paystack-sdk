<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Data\Transaction;

enum TransactionStatus: string
{
    case Abandoned = 'abandoned';
    case Failed = 'failed';
    case Ongoing = 'ongoing';
    case Pending = 'pending';
    case Processing = 'processing';
    case Queued = 'queued';
    case Reversed = 'reversed';
    case Success = 'success';
}

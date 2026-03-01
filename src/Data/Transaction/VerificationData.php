<?php

namespace Maxiviper117\Paystack\Data\Transaction;

use Override;

class VerificationData extends TransactionData
{
    /**
     * @param  array<string, mixed>  $payload
     */
    #[Override]
    public static function fromPayload(array $payload): self
    {
        $transaction = parent::fromPayload($payload);

        return new self(
            id: $transaction->id,
            status: $transaction->status,
            reference: $transaction->reference,
            amount: $transaction->amount,
            currency: $transaction->currency,
            customer: $transaction->customer,
            paidAt: $transaction->paidAt,
            channel: $transaction->channel,
            raw: $transaction->raw,
        );
    }
}

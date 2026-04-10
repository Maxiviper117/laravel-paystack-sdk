<?php

namespace Maxiviper117\Paystack\Data\Input\Dispute;

use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Spatie\LaravelData\Data;

class UpdateDisputeInputData extends Data
{
    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public int|string $id,
        public int|string|null $refundAmount = null,
        public ?string $uploadedFilename = null,
        public array $extra = [],
    ) {
        if (is_int($this->id) && $this->id < 1) {
            throw new InvalidPaystackInputException('The Paystack dispute identifier must be greater than zero.');
        }

        if (\is_string($this->id) && trim($this->id) === '') {
            throw new InvalidPaystackInputException('The Paystack dispute identifier cannot be empty.');
        }

        if ($this->refundAmount !== null) {
            if (! is_numeric($this->refundAmount)) {
                throw new InvalidPaystackInputException('The Paystack dispute refund amount must be numeric.');
            }

            if ((int) $this->refundAmount < 0) {
                throw new InvalidPaystackInputException('The Paystack dispute refund amount cannot be negative.');
            }
        }

        if ($this->uploadedFilename !== null && trim($this->uploadedFilename) === '') {
            throw new InvalidPaystackInputException('The Paystack dispute uploaded filename cannot be empty.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toRequestBody(): array
    {
        $payload = $this->extra;

        if ($this->refundAmount !== null) {
            $payload['refund_amount'] = is_int($this->refundAmount) ? $this->refundAmount : (int) $this->refundAmount;
        }

        if ($this->uploadedFilename !== null) {
            $payload['uploaded_filename'] = $this->uploadedFilename;
        }

        return $payload;
    }
}

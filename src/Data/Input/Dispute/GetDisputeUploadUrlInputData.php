<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Data\Input\Dispute;

use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Spatie\LaravelData\Data;

class GetDisputeUploadUrlInputData extends Data
{
    public function __construct(
        public int|string $id,
        public string $uploadFilename,
    ) {
        if (\is_int($this->id) && $this->id < 1) {
            throw new InvalidPaystackInputException('The Paystack dispute identifier must be greater than zero.');
        }

        if (\is_string($this->id) && trim($this->id) === '') {
            throw new InvalidPaystackInputException('The Paystack dispute identifier cannot be empty.');
        }

        if (trim($this->uploadFilename) === '') {
            throw new InvalidPaystackInputException('The Paystack dispute upload filename cannot be empty.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toRequestQuery(): array
    {
        return [
            'upload_filename' => $this->uploadFilename,
        ];
    }
}

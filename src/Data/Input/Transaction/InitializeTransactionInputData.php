<?php

namespace Maxiviper117\Paystack\Data\Input\Transaction;

use InvalidArgumentException;
use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Maxiviper117\Paystack\Support\Amount;
use Spatie\LaravelData\Data;

class InitializeTransactionInputData extends Data
{
    /**
     * @param  array<string, mixed>|null  $metadata
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public string $email,
        public int|float|string $amount,
        public ?string $callbackUrl = null,
        public ?array $metadata = null,
        public ?string $currency = null,
        public array $extra = [],
    ) {
        if (! filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidPaystackInputException('The Paystack transaction email must be a valid email address.');
        }

        try {
            Amount::toSubunit($this->amount);
        } catch (InvalidArgumentException $invalidArgumentException) {
            throw new InvalidPaystackInputException($invalidArgumentException->getMessage(), 0, $invalidArgumentException);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toRequestBody(): array
    {
        $payload = $this->extra;

        if ($this->callbackUrl !== null) {
            $payload['callback_url'] = $this->callbackUrl;
        }

        if ($this->currency !== null) {
            $payload['currency'] = $this->currency;
        }

        if ($this->metadata !== null) {
            $payload['metadata'] = json_encode($this->metadata, JSON_THROW_ON_ERROR);
        }

        $payload['email'] = $this->email;
        $payload['amount'] = (string) Amount::toSubunit($this->amount);

        return $payload;
    }
}

<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Data\Input\Transaction;

use InvalidArgumentException;
use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Maxiviper117\Paystack\Support\Amount;
use Spatie\LaravelData\Data;

class InitializeTransactionInputData extends Data
{
    /**
     * @param  list<string>|null  $channels
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
        public ?array $channels = null,
        public ?string $reference = null,
        public ?string $plan = null,
        public ?int $invoiceLimit = null,
        public ?string $splitCode = null,
        public ?string $subaccount = null,
        public ?int $transactionCharge = null,
        public ?string $bearer = null,
    ) {
        if (! filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidPaystackInputException('The Paystack transaction email must be a valid email address.');
        }

        if ($this->reference !== null && trim($this->reference) === '') {
            throw new InvalidPaystackInputException('The Paystack transaction reference cannot be empty.');
        }

        if ($this->invoiceLimit !== null && $this->invoiceLimit < 0) {
            throw new InvalidPaystackInputException('The Paystack transaction invoice limit cannot be negative.');
        }

        if ($this->transactionCharge !== null && $this->transactionCharge < 0) {
            throw new InvalidPaystackInputException('The Paystack transaction charge cannot be negative.');
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

        if ($this->channels !== null) {
            $payload['channels'] = $this->channels;
        }

        if ($this->callbackUrl !== null) {
            $payload['callback_url'] = $this->callbackUrl;
        }

        if ($this->reference !== null) {
            $payload['reference'] = $this->reference;
        }

        if ($this->plan !== null) {
            $payload['plan'] = $this->plan;
        }

        if ($this->invoiceLimit !== null) {
            $payload['invoice_limit'] = $this->invoiceLimit;
        }

        if ($this->currency !== null) {
            $payload['currency'] = $this->currency;
        }

        if ($this->metadata !== null) {
            $payload['metadata'] = json_encode($this->metadata, JSON_THROW_ON_ERROR);
        }

        if ($this->splitCode !== null) {
            $payload['split_code'] = $this->splitCode;
        }

        if ($this->subaccount !== null) {
            $payload['subaccount'] = $this->subaccount;
        }

        if ($this->transactionCharge !== null) {
            $payload['transaction_charge'] = $this->transactionCharge;
        }

        if ($this->bearer !== null) {
            $payload['bearer'] = $this->bearer;
        }

        $payload['email'] = $this->email;
        $payload['amount'] = (string) Amount::toSubunit($this->amount);

        return $payload;
    }
}
